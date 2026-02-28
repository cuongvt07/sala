<?php

namespace App\Livewire\Admin\Customers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

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
    public $visa_number;
    public $visa_expiry;
    public $countries = [];

    public function mount()
    {
        $this->countries = Cache::remember('countries_list', 86400, function () {
            try {
                $response = Http::get('https://open.oapi.vn/location/countries');
                if ($response->successful()) {
                    return collect($response->json()['data'])->pluck('niceName')->toArray();
                }
            } catch (\Exception $e) {
                // Fallback or log error
            }
            return [];
        });
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => ['required', 'string', 'max:20', Rule::unique('customers', 'phone')->ignore($this->editingCustomerId)],
            'email' => 'nullable|email|max:255',
            'identity_id' => ['required', 'string', 'max:20', Rule::unique('customers', 'identity_id')->ignore($this->editingCustomerId)],
            'birthday' => 'nullable|date',

            'nationality' => 'nullable|string',
            'visa_number' => 'nullable|string|max:50',
            'visa_expiry' => 'nullable|date',
        ];
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'phone', 'email', 'identity_id', 'birthday', 'nationality', 'visa_number', 'visa_expiry', 'editingCustomerId']);
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
        $this->birthday = $customer->birthday ? \Carbon\Carbon::parse($customer->birthday)->format('Y-m-d') : null;

        $this->nationality = $customer->nationality;
        $this->visa_number = $customer->visa_number;
        $this->visa_expiry = $customer->visa_expiry ? \Carbon\Carbon::parse($customer->visa_expiry)->format('Y-m-d') : null;

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
                'visa_number' => $this->visa_number,
                'visa_expiry' => $this->visa_expiry,
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
                'visa_number' => $this->visa_number,
                'visa_expiry' => $this->visa_expiry,
            ]);
            $message = 'Thêm khách hàng mới thành công.';
        }

        $this->showModal = false;
        session()->flash('success', $message);
        $this->reset(['name', 'phone', 'email', 'identity_id', 'birthday', 'nationality', 'visa_number', 'visa_expiry', 'editingCustomerId']);
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
