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
                // Determine the start and end of the current month view
                $startOfMonth = \Carbon\Carbon::create($this->year, $this->month, 1)->startOfDay();
                $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

                $q->where(function ($query) use ($startOfMonth, $endOfMonth) {
                    // 1. Bookings that start before (or in) this month AND (end after start of month OR stay forever)
                    $query->where('check_in', '<=', $endOfMonth)
                        ->where(function ($sub) use ($startOfMonth) {
                        $sub->where('check_out', '>=', $startOfMonth)
                            ->orWhereNull('check_out'); // Handle long-term
                    });
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
