<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Room;
use App\Models\Area;
use Carbon\Carbon;

class BookingCalendar extends Component
{
    public $month;
    public $year;
    public $selectedArea = '';

    protected $listeners = ['area-selected' => '$refresh'];

    public function mount()
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->addMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function prevMonth()
    {
        $date = Carbon::createFromDate($this->year, $this->month, 1)->subMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function goToToday()
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function getDaysInMonthProperty()
    {
        $start = Carbon::createFromDate($this->year, $this->month, 1);
        $daysInMonth = $start->daysInMonth;
        
        $days = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $days[] = $start->copy()->day($i);
        }
        
        return $days;
    }

    public function getRoomsProperty()
    {
        $query = Room::query()
            ->with(['area', 'bookings' => function ($q) {
                // Eager load bookings for the displayed month
                $q->whereMonth('check_in', $this->month)->whereYear('check_in', $this->year)
                  ->orWhere(function ($sq) {
                      $sq->whereMonth('check_out', $this->month)->whereYear('check_out', $this->year);
                  })
                  ->orWhere(function ($sq) {
                      // Covers bookings spanning the entire month
                      $sq->where('check_in', '<', Carbon::createFromDate($this->year, $this->month, 1))
                         ->where('check_out', '>', Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth());
                  });
            }]);

        if (session('admin_selected_area_id')) {
            $query->where('area_id', session('admin_selected_area_id'));
        }

        if ($this->selectedArea) {
            $query->where('area_id', $this->selectedArea);
        }

        return $query->get()->groupBy('area.name');
    }

    public function createBooking($roomId, $date)
    {
        // Placeholder for create action
        // Could dispatch an event to open a modal
        // $this->dispatch('openBookingModal', roomId: $roomId, date: $date);
        return redirect()->route('admin.dashboard'); // Temporary
    }

    public function render()
    {
        return view('livewire.admin.booking-calendar', [
            'areas' => Area::all(),
        ])->layout('components.layouts.admin');
    }
}
