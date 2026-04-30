<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Area;
use App\Models\Room;
use App\Models\Customer;
use App\Models\Booking;

class Dashboard extends Component
{
    protected $listeners = ['area-selected' => '$refresh'];

    public $filterMonth;
    public $filterYear;
    public $activeTab = 'general'; // general, finance

    public function mount()
    {
        $this->filterMonth = now()->month;
        $this->filterYear = now()->year;
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function updateVisaStatus($customerId, $status)
    {
        $customer = Customer::find($customerId);
        if ($customer) {
            $customer->update(['visa_status' => $status]);
            session()->flash('message', 'Cập nhật trạng thái Visa thành công.');
        }
    }

    public function render()
    {
        $areaId = session('admin_selected_area_id');

        $queryRoom = Room::query();
        $queryBooking = Booking::query();

        if ($areaId) {
            $queryRoom->where('area_id', $areaId);
            $queryBooking->whereHas('room', function ($q) use ($areaId) {
                $q->where('area_id', $areaId);
            });
        }

        // Finance Data (Filtered by Month/Year)
        $queryMonthBookings = clone $queryBooking;
        if ($this->filterMonth && $this->filterYear) {
            $queryMonthBookings->whereYear('check_in', $this->filterYear)
                               ->whereMonth('check_in', $this->filterMonth);
        }

        $revenue = $queryMonthBookings->sum('price');
        $depositSum1 = $queryMonthBookings->sum('deposit');
        $depositSum2 = $queryMonthBookings->sum('deposit_2');
        $depositSum3 = $queryMonthBookings->sum('deposit_3');
        $totalCollected = $depositSum1 + $depositSum2 + $depositSum3;

        // Upcoming Lists (Next 3 Days)
        $today = now()->startOfDay();
        $threeDaysLater = now()->addDays(3)->endOfDay();

        $upcomingCheckins = (clone $queryBooking)
            ->whereBetween('check_in', [$today, $threeDaysLater])
            ->whereIn('status', ['pending'])
            ->with(['customer', 'room'])
            ->orderBy('check_in')
            ->get();

        $upcomingCheckouts = (clone $queryBooking)
            ->whereBetween('check_out', [$today, $threeDaysLater])
            ->whereIn('status', ['checked_in'])
            ->with(['customer', 'room'])
            ->orderBy('check_out')
            ->get();

        $visaExpiries = Customer::whereBetween('visa_expiry', [$today, $threeDaysLater])
            ->where('visa_status', '!=', 3) // 3 is "Đã gia hạn"
            ->where('visa_expiry', '>=', $today)
            ->orderBy('visa_expiry')
            ->get();

        return view('livewire.admin.dashboard', [
            'totalAreas' => Area::count(),
            'totalRooms' => $queryRoom->count(),
            'totalCustomers' => Customer::count(),
            'totalBookings' => $queryMonthBookings->count(),
            'activeBookings' => (clone $queryMonthBookings)->where('status', 'checked_in')->count(),
            'pendingBookings' => (clone $queryMonthBookings)->where('status', 'pending')->count(),
            'birthdayCustomers' => Customer::whereMonth('birthday', date('m'))->whereDay('birthday', date('d'))->get(),
            'revenue' => $revenue,
            'totalCollected' => $totalCollected,
            'upcomingCheckins' => $upcomingCheckins,
            'upcomingCheckouts' => $upcomingCheckouts,
            'visaExpiries' => $visaExpiries,
        ])->layout('components.layouts.admin');
    }
}
