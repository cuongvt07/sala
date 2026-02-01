<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;

class BookingCalendar extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $title = 'Booking Calendar';
    protected static ?string $slug = 'booking-calendar';
    protected string $view = 'filament.pages.booking-calendar';

    public $month;
    public $year;
    public $selectedArea = null;

    public function mount()
    {
        $this->month = now()->month;
        $this->year = now()->year;
        $this->selectedArea = session()->get('admin.selected_area');
    }

    public function updatedSelectedArea($value)
    {
        session()->put('admin.selected_area', $value);
    }

    public function getDaysInMonthProperty()
    {
        $start = \Carbon\Carbon::createFromDate($this->year, $this->month, 1);
        $days = [];
        $daysInMonth = $start->daysInMonth;

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $days[] = $start->copy()->day($i);
        }

        return $days;
    }

    public function getRoomsProperty()
    {
        $query = \App\Models\Room::query()->with([
            'area',
            'bookings' => function ($q) {
                $q->whereMonth('check_in', $this->month)->whereYear('check_in', $this->year)
                    ->orWhere(function ($sq) {
                        $sq->whereMonth('check_out', $this->month)->whereYear('check_out', $this->year);
                    });
            }
        ]);

        if ($this->selectedArea) {
            $query->where('area_id', $this->selectedArea);
        }

        return $query->get()->groupBy('area.name');
    }

    public function createBooking($roomId, $date)
    {
        return redirect()->route('filament.admin.resources.bookings.create', [
            'room_id' => $roomId,
            'check_in' => $date,
        ]);
    }

    public function editBooking($bookingId)
    {
        return redirect()->route('filament.admin.resources.bookings.edit', ['record' => $bookingId]);
    }

    public function nextMonth()
    {
        $date = \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->addMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }

    public function prevMonth()
    {
        $date = \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->subMonth();
        $this->month = $date->month;
        $this->year = $date->year;
    }
}
