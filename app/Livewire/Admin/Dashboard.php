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

    public function mount()
    {
        $this->filterMonth = now()->month;
        $this->filterYear = now()->year;
    }

    public function updatedFilterMonth()
    {
        // Trigger re-render
    }

    public function updatedFilterYear()
    {
        // Trigger re-render
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

        return view('livewire.admin.dashboard', [
            'totalAreas' => Area::count(), // Areas count remains global usually, or filter if needed
            'totalRooms' => $queryRoom->count(),
            'totalCustomers' => Customer::count(), // Customers are usually global
            'totalBookings' => $queryMonthBookings->count(),
            'activeBookings' => (clone $queryMonthBookings)->where('status', 'checked_in')->count(),
            'pendingBookings' => (clone $queryMonthBookings)->where('status', 'pending')->count(),
            'birthdayCustomers' => Customer::whereMonth('birthday', date('m'))->whereDay('birthday', date('d'))->get(),
            'revenue' => $revenue,
            'totalCollected' => $totalCollected,
        ])->layout('components.layouts.admin');
    }
}
