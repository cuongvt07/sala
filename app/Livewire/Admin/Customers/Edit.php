<?php

namespace App\Livewire\Admin\Customers;

use Livewire\Component;
use App\Models\Customer;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    public $customerId;
    public $name;
    public $phone;
    public $email;
    public $identity_id;
    public $birthday;
    public $nationality;
    public $visa_number;
    public $visa_expiry;

    public function mount($customer)
    {
        $customerModel = Customer::find($customer);
        if (!$customerModel) {
            return redirect()->route('admin.customers.index');
        }

        $this->customerId = $customerModel->id;
        $this->name = $customerModel->name;
        $this->phone = $customerModel->phone;
        $this->email = $customerModel->email;
        $this->identity_id = $customerModel->identity_id;
        $this->birthday = $customerModel->birthday;
        $this->nationality = $customerModel->nationality;
        $this->visa_number = $customerModel->visa_number;
        $this->visa_expiry = $customerModel->visa_expiry;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', Rule::unique('customers', 'phone')->ignore($this->customerId)],
            'email' => 'nullable|email|max:255',
            'identity_id' => ['required', 'string', 'max:20', Rule::unique('customers', 'identity_id')->ignore($this->customerId)],
            'birthday' => 'nullable|date',
            'nationality' => 'nullable|string',
            'visa_number' => 'nullable|string|max:50',
            'visa_expiry' => 'nullable|date',
        ];
    }

    public function save()
    {
        $this->validate();

        $customer = Customer::find($this->customerId);
        $customer->update([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'identity_id' => $this->identity_id,
            'birthday' => $this->birthday,
            'nationality' => $this->nationality,
            'visa_number' => $this->visa_number,
            'visa_expiry' => $this->visa_expiry,
        ]);

        session()->flash('success', 'Cập nhật khách hàng thành công.');

        return redirect()->route('admin.customers.index');
    }

    public function render()
    {
        return view('livewire.admin.customers.edit')->layout('components.layouts.admin');
    }
}
