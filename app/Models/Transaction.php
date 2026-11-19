<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Một khoản thu/chi ĐÃ XÁC NHẬN. Đây là nguồn số liệu duy nhất cho báo cáo tài chính.
 */
class Transaction extends Model
{
    public const INCOME = 'income';
    public const EXPENSE = 'expense';

    public const METHODS = [
        'cash' => 'Tiền mặt',
        'transfer' => 'Chuyển khoản',
    ];

    public const INCOME_CATEGORIES = ['Tiền phòng', 'Tiền cọc', 'Dịch vụ', 'Khác'];
    public const EXPENSE_CATEGORIES = ['Hoàn cọc', 'Bảo trì', 'Điện nước', 'Lương', 'Vật tư', 'Khác'];

    protected $fillable = [
        'type', 'category', 'amount', 'method', 'txn_date', 'description',
        'area_id', 'room_code', 'customer_name', 'booking_id', 'period_due', 'user_id',
    ];

    protected $casts = [
        'txn_date' => 'date',
        'period_due' => 'date',
        'amount' => 'float',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }

    /**
     * Ghi 1 khoản thu/chi gắn với booking, tự chụp lại phòng/toà/tên khách.
     */
    public static function recordForBooking(Booking $booking, array $data): self
    {
        $booking->loadMissing(['room', 'customer']);

        return self::create(array_merge([
            'type' => self::INCOME,
            'method' => 'cash',
            'txn_date' => now()->toDateString(),
            'booking_id' => $booking->id,
            'area_id' => $booking->room->area_id ?? null,
            'room_code' => $booking->room->code ?? null,
            'customer_name' => $booking->customer->name ?? null,
            'user_id' => auth()->id(),
        ], $data));
    }
}
