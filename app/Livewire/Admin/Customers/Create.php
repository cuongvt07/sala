<?php

namespace App\Livewire\Admin\Customers;

use Livewire\Component;
use App\Models\Customer;

class Create extends Component
{
    public $name;
    public $phone;
    public $email;
    public $identity_id;
    public $birthday;
    public $nationality = 'Vietnam';
    public $visa_number;
    public $visa_expiry;

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20|unique:customers,phone',
        'email' => 'nullable|email|max:255',
        'identity_id' => 'required|string|max:20|unique:customers,identity_id',
        'birthday' => 'nullable|date',
        'nationality' => 'nullable|string',
        'visa_number' => 'nullable|string|max:50',
        'visa_expiry' => 'nullable|date',
    ];

    public function save()
    {
        $this->validate();

        Customer::create([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'identity_id' => $this->identity_id,
            'birthday' => $this->birthday,

            'nationality' => $this->nationality,
            'visa_number' => $this->visa_number,
            'visa_expiry' => $this->visa_expiry,
        ]);

        session()->flash('success', 'Thêm khách hàng mới thành công.');

        return redirect()->route('admin.customers.index');
    }

    public function render()
    {
        return view('livewire.admin.customers.create')->layout('components.layouts.admin');
    }
}
