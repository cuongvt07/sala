<?php

namespace App\Livewire\Admin\Bookings;

use Livewire\Component;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;

class Create extends Component
{
    public $customer_id;
    public $room_id;
    public $check_in;
    public $check_out;
    public $price;
    public $deposit = 0;
    public $deposit_2 = 0;
    public $deposit_3 = 0;
    public $status = 'pending';

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'room_id' => 'required|exists:rooms,id',
        'check_in' => 'required|date',
        'check_out' => 'required|date|after:check_in',
        'price' => 'required|numeric|min:0',
        'deposit' => 'nullable|numeric|min:0',
        'deposit_2' => 'nullable|numeric|min:0',
        'deposit_3' => 'nullable|numeric|min:0',
        'status' => 'required|in:pending,checked_in,checked_out,cancelled',
    ];

    public function save()
    {
        $this->validate();

        Booking::create([
            'customer_id' => $this->customer_id,
            'room_id' => $this->room_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'price' => $this->price,
            'deposit' => $this->deposit,
            'deposit_2' => $this->deposit_2,
            'deposit_3' => $this->deposit_3,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Tạo booking mới thành công.');

        return redirect()->route('admin.bookings.index');
    }

    public function render()
    {
        return view('livewire.admin.bookings.create', [
            'customers' => Customer::orderBy('name')->get(),
            'rooms' => Room::with('area')->orderBy('code')->get(),
        ])->layout('components.layouts.admin');
    }
}
