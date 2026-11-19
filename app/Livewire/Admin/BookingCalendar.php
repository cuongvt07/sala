<?php

namespace App\Livewire\Admin;

use App\Models\Area;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Lịch đặt phòng (30 ngày). Thêm/sửa đặt phòng dùng chung component BookingForm.
 */
class BookingCalendar extends Component
{
    public $startDate;

    // Filters
    public $filter_areas = [];
    public $filter_start_date;
    public $filter_end_date;

    protected $listeners = ['area-selected' => '$refresh', 'booking-saved' => '$refresh'];

    public function mount()
    {
        $this->startDate = now()->format('Y-m-d');
    }

    public function updatedFilterStartDate($value)
    {
        // Ô tìm kiếm nhập dd/mm/yyyy -> dịch lịch tới ngày đó
        $normalized = $this->parseDate($value);
        if ($normalized) {
            $this->startDate = $normalized;
        }
    }

    public function nextMonth()
    {
        $this->startDate = Carbon::parse($this->startDate)->addDays(30)->format('Y-m-d');
    }

    public function prevMonth()
    {
        $this->startDate = Carbon::parse($this->startDate)->subDays(30)->format('Y-m-d');
    }

    public function goToToday()
    {
        $this->startDate = now()->format('Y-m-d');
    }

    private function parseDate($dateStr): ?string
    {
        if (empty($dateStr)) {
            return null;
        }
        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
                return $dateStr;
            }
            $clean = str_replace([' ', '-', '.'], ['', '/', '/'], $dateStr);
            return Carbon::createFromFormat('d/m/Y', $clean)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /** Phạm vi phòng theo toà (session toàn cục + bộ lọc trên lịch + phân quyền nhân viên). */
    protected function roomScope()
    {
        $query = Room::query();
        $user = auth()->user();

        if ($user->isAreaRestricted()) {
            $query->where('area_id', $user->area_id);
        } elseif (session('admin_selected_area_id')) {
            $query->where('area_id', session('admin_selected_area_id'));
        }
        if (!empty($this->filter_areas)) {
            $query->whereIn('area_id', $this->filter_areas);
        }

        return $query;
    }

    /**
     * Gợi ý NỐI PHÒNG khi không có phòng trống suốt khoảng tìm kiếm.
     */
    #[Computed]
    public function roomChain()
    {
        $startStr = $this->parseDate($this->filter_start_date);
        $endStr = $this->parseDate($this->filter_end_date);
        if (!$startStr || !$endStr) {
            return null;
        }

        $reqStart = Carbon::parse($startStr)->startOfDay();
        $reqEnd = Carbon::parse($endStr)->startOfDay();
        if ($reqEnd->lte($reqStart)) {
            return null;
        }

        $rooms = $this->roomScope()->with(['area:id,name', 'bookings' => function ($q) use ($reqStart, $reqEnd) {
            $q->select('id', 'room_id', 'check_in', 'check_out')
              ->whereIn('status', Booking::ACTIVE_STATUSES)
              ->where('check_in', '<', $reqEnd)
              ->where(fn ($s) => $s->where('check_out', '>', $reqStart)->orWhereNull('check_out'));
        }])->get();

        $freeInfo = function ($room, $d) use ($reqEnd) {
            foreach ($room->bookings as $b) {
                $ci = $b->check_in->copy()->startOfDay();
                $co = $b->check_out ? $b->check_out->copy()->startOfDay() : null;
                if ($ci->lte($d) && ($co === null || $co->gt($d))) {
                    return [false, null];
                }
            }
            $reach = $reqEnd->copy();
            foreach ($room->bookings as $b) {
                $ci = $b->check_in->copy()->startOfDay();
                if ($ci->gt($d) && $ci->lt($reach)) {
                    $reach = $ci->copy();
                }
            }
            return [true, $reach];
        };

        $segments = [];
        $current = $reqStart->copy();
        $guard = 0;
        while ($current->lt($reqEnd) && $guard++ < 400) {
            $best = null;
            $bestReach = null;
            foreach ($rooms as $room) {
                [$free, $reach] = $freeInfo($room, $current);
                if ($free && $reach->gt($current) && ($bestReach === null || $reach->gt($bestReach))) {
                    $bestReach = $reach->copy();
                    $best = $room;
                }
            }
            if (!$best) {
                return ['possible' => false, 'segments' => $segments, 'single' => false];
            }
            $segments[] = [
                'room_id' => $best->id,
                'room_code' => $best->code,
                'area' => $best->area->name ?? '',
                'from' => $current->format('Y-m-d'),
                'from_label' => $current->format('d/m'),
                'to_label' => $bestReach->format('d/m'),
                'nights' => $current->diffInDays($bestReach),
            ];
            $current = $bestReach->copy();
        }

        return [
            'possible' => $current->gte($reqEnd),
            'segments' => $segments,
            'single' => count($segments) === 1,
        ];
    }

    #[Computed]
    public function daysInMonth()
    {
        $start = Carbon::parse($this->startDate);
        $days = [];
        for ($i = 0; $i < 30; $i++) {
            $days[] = $start->copy()->addDays($i);
        }
        return $days;
    }

    #[Computed]
    public function rooms()
    {
        $windowStart = Carbon::parse($this->startDate)->startOfDay();
        $windowEnd = $windowStart->copy()->addDays(29)->endOfDay();

        $query = $this->roomScope()
            ->select('id', 'area_id', 'code', 'type', 'status', 'price_day')
            ->with([
                'area:id,name',
                'bookings' => function ($q) use ($windowStart, $windowEnd) {
                    $q->select('id', 'room_id', 'customer_id', 'check_in', 'check_out', 'price_type', 'status', 'source', 'additional_guests')
                      ->whereIn('status', Booking::ACTIVE_STATUSES)
                      ->where('check_in', '<=', $windowEnd)
                      ->where(fn ($s) => $s->where('check_out', '>=', $windowStart)->orWhereNull('check_out'))
                      ->with('customer:id,name'); // tránh N+1 khi hiển thị tên khách trên thanh booking
                },
            ])
            ->orderBy('area_id')
            ->orderBy('code');

        // Chỉ hiện phòng trống trong khoảng ngày tìm kiếm
        $filterStart = $this->parseDate($this->filter_start_date);
        $filterEnd = $this->parseDate($this->filter_end_date);
        if ($filterStart && $filterEnd) {
            $start = Carbon::parse($filterStart)->startOfDay();
            $end = Carbon::parse($filterEnd)->startOfDay();
            $query->whereDoesntHave('bookings', function ($q) use ($start, $end) {
                $q->whereIn('status', Booking::ACTIVE_STATUSES)
                  ->where('check_in', '<', $end)
                  ->where(fn ($s) => $s->where('check_out', '>', $start)->orWhereNull('check_out'));
            });
        }

        return $query->get()
            ->each(fn ($room) => $this->calculateStacking($room, $windowStart))
            ->groupBy(fn ($room) => $room->area->name ?? 'Khác');
    }

    protected function calculateStacking($room, Carbon $windowStart): void
    {
        foreach ($room->bookings as $booking) {
            $checkIn = $booking->check_in->copy()->startOfDay();
            $checkOut = $booking->check_out
                ? $booking->check_out->copy()->startOfDay()
                : $windowStart->copy()->addDays(31); // hợp đồng không thời hạn -> kéo hết lịch

            $startDays = $windowStart->diffInDays($checkIn, false);
            $endDays = $windowStart->diffInDays($checkOut, false);

            if ($booking->price_type === 'month') {
                $visualStart = (float) $startDays;
                $visualDays = $endDays - $startDays;
            } else {
                // Thuê ngày: bắt đầu & kết thúc ở giữa ô ngày (nhận chiều, trả trưa)
                $visualStart = $startDays + 0.5;
                $visualDays = $endDays - $startDays;
            }

            if ($visualStart < 0) {
                $visualDays = max(0, $visualDays + $visualStart);
                $visualStart = 0;
            }

            $booking->visual_start = $visualStart;
            $booking->visual_days = max(0.5, $visualDays);
        }
    }

    public function render()
    {
        return view('livewire.admin.booking-calendar', [
            'areas' => Area::select('id', 'name')->orderBy('name')->get(),
            'roomsData' => $this->rooms,
            'days' => $this->daysInMonth,
            'roomChain' => $this->roomChain,
        ])->layout('components.layouts.admin');
    }
}
