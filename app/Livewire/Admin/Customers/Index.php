<?php

namespace App\Livewire\Admin\Customers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;

use Illuminate\Validation\Rule;

class Index extends Component
{
    use WithPagination;

    public $search = '';

    public $showModal = false;
    public $editingCustomerId = null;

    // Form inputs
    public $name;
    public $phone;
    public $email;
    public $identity_id;
    public $birthday;
    public $nationality = 'Vietnam';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', Rule::unique('customers', 'phone')->ignore($this->editingCustomerId)],
            'email' => 'nullable|email|max:255',
            'identity_id' => ['required', 'string', 'max:20', Rule::unique('customers', 'identity_id')->ignore($this->editingCustomerId)],
            'birthday' => 'nullable|date',
            'nationality' => 'nullable|string',
        ];
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'phone', 'email', 'identity_id', 'birthday', 'nationality', 'editingCustomerId']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $customer = Customer::findOrFail($id);
        $this->editingCustomerId = $id;
        
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->email = $customer->email;
        $this->identity_id = $customer->identity_id;
        $this->birthday = $customer->birthday;
        $this->nationality = $customer->nationality;
        
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingCustomerId) {
            $customer = Customer::find($this->editingCustomerId);
            $customer->update([
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'identity_id' => $this->identity_id,
                'birthday' => $this->birthday,
                'nationality' => $this->nationality,
            ]);
            $message = 'Cập nhật khách hàng thành công.';
        } else {
            Customer::create([
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'identity_id' => $this->identity_id,
                'birthday' => $this->birthday,
                'nationality' => $this->nationality,
            ]);
            $message = 'Thêm khách hàng mới thành công.';
        }

        $this->showModal = false;
        session()->flash('success', $message);
        $this->reset(['name', 'phone', 'email', 'identity_id', 'birthday', 'nationality', 'editingCustomerId']);
    }

    public function delete($id)
    {
        Customer::find($id)->delete();
        session()->flash('success', 'Xóa khách hàng thành công.');
    }

    public function render()
    {
        $customers = Customer::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                      ->orWhere('identity_id', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.customers.index', [
            'customers' => $customers,
        ])->layout('components.layouts.admin');
    }
}
