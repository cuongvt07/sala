<?php

namespace App\Livewire\Admin;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use App\Models\Transaction;
use App\Services\BookingPurger;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Dashboard extends Component
{
    protected $listeners = ['area-selected' => '$refresh', 'booking-saved' => '$refresh'];

    public function mount()
    {
        // Lưới an toàn (không cần cron): mỗi ngày dọn 1 lần booking đã trả/huỷ còn sót & khách không còn đặt phòng
        if (Cache::add('purge-sweep-' . now()->toDateString(), 1, now()->endOfDay())) {
            try {
                BookingPurger::sweep();
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    public function updateVisaStatus($customerId, $status)
    {
        Customer::whereKey($customerId)->update(['visa_status' => (int) $status]);
        $this->dispatch('toast', message: 'Đã cập nhật trạng thái visa.', type: 'success');
    }

    protected function areaId()
    {
        $user = auth()->user();
        return $user->isAreaRestricted() ? $user->area_id : session('admin_selected_area_id');
    }

    /**
     * Phòng trống từng đêm: hôm nay và 3 ngày tới.
     * Phòng trống đêm D = không có booking (sắp tới/đang ở) nào có check_in <= D < check_out.
     */
    protected function vacancy($areaId): array
    {
        $today = now()->startOfDay();
        $days = collect(range(0, 3))->map(fn ($i) => $today->copy()->addDays($i));
        $windowEnd = $today->copy()->addDays(4);

        $rooms = Room::query()
            ->select('id', 'area_id', 'code', 'type', 'status')
            ->with(['area:id,name', 'bookings' => function ($q) use ($today, $windowEnd) {
                $q->select('id', 'room_id', 'check_in', 'check_out')
                  ->whereIn('status', Booking::ACTIVE_STATUSES)
                  ->where('check_in', '<', $windowEnd)
                  ->where(fn ($s) => $s->where('check_out', '>', $today)->orWhereNull('check_out'));
            }])
            ->when($areaId, fn ($q) => $q->where('area_id', $areaId))
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'maintenance'))
            ->orderBy('area_id')->orderBy('code')
            ->get();

        $result = [];
        foreach ($days as $day) {
            $free = $rooms->filter(function ($room) use ($day) {
                foreach ($room->bookings as $b) {
                    $in = $b->check_in->copy()->startOfDay();
                    $out = $b->check_out?->copy()->startOfDay();
                    if ($in->lte($day) && ($out === null || $out->gt($day))) {
                        return false;
                    }
                }
                return true;
            });

            $result[] = [
                'date' => $day,
                'label' => $day->isToday() ? 'Hôm nay' : ($day->isTomorrow() ? 'Ngày mai' : $day->locale('vi')->isoFormat('dddd')),
                'count' => $free->count(),
                'byArea' => $free->groupBy(fn ($r) => $r->area->name ?? 'Khác'),
            ];
        }

        return ['days' => $result, 'total' => $rooms->count()];
    }

    public function render()
    {
        $areaId = $this->areaId();
        $today = now()->startOfDay();
        $in3Days = now()->addDays(3)->endOfDay();

        $inArea = fn ($q) => $q->where('area_id', $areaId);
        $bookingBase = fn () => Booking::query()
            ->when($areaId, fn ($q) => $q->whereHas('room', $inArea))
            ->with(['customer:id,name,phone', 'room:id,code']);

        $upcomingCheckins = $bookingBase()
            ->where('status', 'pending')
            ->whereBetween('check_in', [$today, $in3Days])
            ->orderBy('check_in')->get();

        $upcomingCheckouts = $bookingBase()
            ->where('status', 'checked_in')
            ->whereNotNull('check_out')
            ->where('check_out', '<=', $in3Days)
            ->orderBy('check_out')->get();

        // Hợp đồng đến hạn đóng tiền trong 3 ngày tới (hoặc đã quá hạn mà chưa thu)
        $contractDues = [];
        $bookingBase()
            ->where('status', 'checked_in')
            ->where(fn ($q) => $q->where('price_type', 'month')->orWhere('is_contract', true))
            ->with(['transactions' => fn ($q) => $q->select('id', 'booking_id', 'type', 'period_due')->whereNotNull('period_due')])
            ->get()
            ->each(function (Booking $b) use (&$contractDues, $in3Days, $today) {
                foreach ($b->unpaidContractDues($in3Days) as $due) {
                    $contractDues[] = [
                        'booking' => $b,
                        'due' => $due,
                        'days' => (int) $today->diffInDays($due, false),
                        'amount' => (float) $b->unit_price,
                    ];
                }
            });
        usort($contractDues, fn ($a, $b) => $a['due'] <=> $b['due']);

        $stayingFilter = function ($q) use ($areaId) {
            $q->where('status', 'checked_in');
            if ($areaId) {
                $q->whereHas('room', fn ($r) => $r->where('area_id', $areaId));
            }
        };

        $visaExpiries = Customer::select('id', 'name', 'visa_expiry', 'visa_status')
            ->whereBetween('visa_expiry', [$today, $in3Days])
            ->where('visa_status', '!=', 3)
            ->whereHas('bookings', $stayingFilter)
            ->orderBy('visa_expiry')->get();

        $birthdayCustomers = Customer::select('id', 'name', 'phone', 'birthday')
            ->whereMonth('birthday', $today->month)
            ->whereDay('birthday', $today->day)
            ->whereHas('bookings', $stayingFilter)
            ->get();

        // Thu/chi tháng này (từ sổ thu chi) — chỉ người có quyền xem tài chính
        $finance = null;
        if (auth()->user()->hasPermission('admin.finance.index')) {
            $month = Transaction::query()
                ->whereBetween('txn_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->when($areaId, fn ($q) => $q->where('area_id', $areaId))
                ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income, SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense")
                ->first();
            $finance = ['income' => (float) $month->income, 'expense' => (float) $month->expense];
        }

        $stayingCount = Booking::where('status', 'checked_in')
            ->when($areaId, fn ($q) => $q->whereHas('room', $inArea))->count();

        return view('livewire.admin.dashboard', [
            'vacancy' => $this->vacancy($areaId),
            'stayingCount' => $stayingCount,
            'upcomingCheckins' => $upcomingCheckins,
            'upcomingCheckouts' => $upcomingCheckouts,
            'contractDues' => $contractDues,
            'visaExpiries' => $visaExpiries,
            'birthdayCustomers' => $birthdayCustomers,
            'finance' => $finance,
        ])->layout('components.layouts.admin');
    }
}
