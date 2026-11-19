<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Hệ thống chỉ giữ thông tin khách SẮP TỚI và ĐANG Ở.
 * Khi khách trả phòng / huỷ: xoá booking + dịch vụ + khách (nếu khách không còn booking nào khác).
 * Sổ thu chi (transactions) vẫn giữ nguyên vì đã lưu sẵn số phòng & tên khách dạng text.
 */
class BookingPurger
{
    public static function purge(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $customerIds = collect([$booking->customer_id]);
            foreach ((array) ($booking->additional_guests ?? []) as $guest) {
                if (!empty($guest['customer_id'])) {
                    $customerIds->push($guest['customer_id']);
                }
            }
            $customerIds = $customerIds->filter()->unique()->values();

            $bookingId = $booking->id;

            // withoutEvents: không ghi thêm nhật ký chứa thông tin khách vừa bị xoá
            Booking::withoutEvents(function () use ($booking) {
                $booking->services()->detach();
                $booking->usageLogs()->delete();
                $booking->delete(); // transactions.booking_id tự về NULL (nullOnDelete)
            });

            $deletedCustomerIds = [];
            foreach ($customerIds as $customerId) {
                $customer = Customer::find($customerId);
                if (!$customer) {
                    continue;
                }
                if ($customer->bookings()->exists()) {
                    continue; // khách còn booking khác (sắp tới/đang ở) -> giữ lại
                }
                self::deleteCustomer($customer);
                $deletedCustomerIds[] = $customerId;
            }

            // Dọn nhật ký hoạt động có chứa thông tin cá nhân của booking/khách đã xoá
            ActivityLog::where('model_type', Booking::class)->where('model_id', $bookingId)->delete();
            if ($deletedCustomerIds) {
                ActivityLog::where('model_type', Customer::class)->whereIn('model_id', $deletedCustomerIds)->delete();
            }
        });
    }

    public static function deleteCustomer(Customer $customer): void
    {
        if (!empty($customer->images)) {
            try {
                Storage::disk('public')->delete($customer->images);
            } catch (\Throwable $e) {
                // ảnh không tồn tại -> bỏ qua
            }
        }

        Customer::withoutEvents(fn () => $customer->delete());
    }

    /**
     * Dọn định kỳ: booking đã trả/huỷ còn sót + khách không còn booking nào
     * (khách tạo tay quá $graceDays ngày mà không đặt phòng).
     *
     * @return array{bookings:int, customers:int}
     */
    public static function sweep(int $graceDays = 7): array
    {
        $bookings = 0;
        Booking::whereNotIn('status', Booking::ACTIVE_STATUSES)
            ->orderBy('id')
            ->each(function (Booking $b) use (&$bookings) {
                self::purge($b);
                $bookings++;
            });

        $customers = 0;
        Customer::whereDoesntHave('bookings')
            ->where('created_at', '<', now()->subDays($graceDays))
            ->orderBy('id')
            ->each(function (Customer $c) use (&$customers) {
                ActivityLog::where('model_type', Customer::class)->where('model_id', $c->id)->delete();
                self::deleteCustomer($c);
                $customers++;
            });

        return ['bookings' => $bookings, 'customers' => $customers];
    }
}
