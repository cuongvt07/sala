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

        return view('livewire.admin.dashboard', [
            'totalAreas' => Area::count(), // Areas count remains global usually, or filter if needed
            'totalRooms' => $queryRoom->count(),
            'totalCustomers' => Customer::count(), // Customers are usually global
            'totalBookings' => $queryBooking->count(),
            'activeBookings' => (clone $queryBooking)->where('status', 'checked_in')->count(),
            'pendingBookings' => (clone $queryBooking)->where('status', 'pending')->count(),
        ])->layout('components.layouts.admin');
    }
}
