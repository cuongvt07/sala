<?php

namespace App\Livewire\Admin\Bookings;

use Livewire\Component;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;

class Edit extends Component
{
    public $bookingId;
    public $customer_id;
    public $room_id;
    public $check_in;
    public $check_out;
    public $price;
    public $deposit;
    public $status;

    public function mount($booking)
    {
        $bookingModel = Booking::find($booking);
        if (!$bookingModel) {
            return redirect()->route('admin.bookings.index');
        }

        $this->bookingId = $bookingModel->id;
        $this->customer_id = $bookingModel->customer_id;
        $this->room_id = $bookingModel->room_id;
        $this->check_in = $bookingModel->check_in;
        $this->check_out = $bookingModel->check_out;
        $this->price = $bookingModel->price;
        $this->deposit = $bookingModel->deposit;
        $this->status = $bookingModel->status;
    }

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'room_id' => 'required|exists:rooms,id',
        'check_in' => 'required|date',
        'check_out' => 'required|date|after:check_in',
        'price' => 'required|numeric|min:0',
        'deposit' => 'nullable|numeric|min:0',
        'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
    ];

    public function save()
    {
        $this->validate();

        $booking = Booking::find($this->bookingId);
        $booking->update([
            'customer_id' => $this->customer_id,
            'room_id' => $this->room_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'price' => $this->price,
            'deposit' => $this->deposit,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Cập nhật booking thành công.');

        return redirect()->route('admin.bookings.index');
    }

    public function render()
    {
        return view('livewire.admin.bookings.edit', [
            'customers' => Customer::orderBy('name')->get(),
            'rooms' => Room::with('area')->orderBy('code')->get(),
        ])->layout('components.layouts.admin');
    }
}
