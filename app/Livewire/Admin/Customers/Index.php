<?php

namespace App\Livewire\Admin\Customers;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Customer;
use App\Models\Booking;

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
    public $gender;

    public $nationality = 'Vietnam';
    public $visa_number;
    public $visa_expiry;
    public $countries = [];

    use \App\Traits\HasCountryData;

    public function updatedNationality($value)
    {
        $this->nationality = $this->handleNationalityUpdate($value);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('customers', 'phone')->ignore($this->editingCustomerId)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($this->editingCustomerId)],
            'identity_id' => ['nullable', 'string', 'max:20', Rule::unique('customers', 'identity_id')->ignore($this->editingCustomerId)],
            'birthday' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',

            'nationality' => 'nullable|string',
            'visa_number' => 'nullable|string|max:50',
            'visa_expiry' => 'nullable|date',
        ];
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'phone', 'email', 'identity_id', 'birthday', 'gender', 'nationality', 'visa_number', 'visa_expiry', 'editingCustomerId']);
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
        $this->gender = $customer->gender;

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
                'gender' => $this->gender,

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
                'gender' => $this->gender,

                'nationality' => $this->nationality ?: 'Vietnam',
                'visa_number' => $this->visa_number,
                'visa_expiry' => $this->visa_expiry,
            ]);
            $message = 'Thêm khách hàng mới thành công.';
        }

        $this->showModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
        $this->reset(['name', 'phone', 'email', 'identity_id', 'birthday', 'gender', 'nationality', 'visa_number', 'visa_expiry', 'editingCustomerId']);
    }

    public function delete($id)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            $this->dispatch('toast', message: 'Không tìm thấy khách hàng.', type: 'error');
            return;
        }

        // Chặn xóa khi khách còn booking để tránh cascade xóa luôn lịch sử đặt phòng/thu tiền
        if ($customer->bookings()->exists()) {
            $this->dispatch('toast', message: 'Không thể xóa: khách hàng còn lịch đặt phòng. Vui lòng xử lý các booking trước.', type: 'error');
            return;
        }

        $customer->delete();
        $this->dispatch('toast', message: 'Xóa khách hàng thành công.', type: 'success');
    }

    public $filterNationality = '';

    public function render()
    {
        $customers = Customer::query()
            ->with(['bookings' => function($q) {
                $q->latest()->whereIn('status', ['checked_in', 'pending'])->with('room');
            }])
            // Cột phụ để sắp xếp: số phòng khách đang ở & ngày nhận phòng sắp tới gần nhất
            ->select('customers.*')
            ->addSelect(['staying_room_code' => Booking::query()
                ->select('rooms.code')
                ->join('rooms', 'rooms.id', '=', 'bookings.room_id')
                ->whereColumn('bookings.customer_id', 'customers.id')
                ->where('bookings.status', 'checked_in')
                ->orderBy('rooms.code')
                ->limit(1)])
            ->addSelect(['next_checkin' => Booking::query()
                ->select('check_in')
                ->whereColumn('bookings.customer_id', 'customers.id')
                ->where('bookings.status', 'pending')
                ->where('check_in', '>=', now()->startOfDay())
                ->orderBy('check_in')
                ->limit(1)])
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('identity_id', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterNationality, function ($query) {
                $query->where('nationality', $this->filterNationality);
            })
            // Khách đang ở lên đầu (theo số phòng tăng dần), rồi khách sắp tới theo ngày gần nhất
            ->orderByRaw('staying_room_code IS NULL')
            ->orderBy('staying_room_code')
            ->orderByRaw('next_checkin IS NULL')
            ->orderBy('next_checkin')
            ->latest()
            ->paginate(10);

        return view('livewire.admin.customers.index', [
            'customers' => $customers,
        ])->layout('components.layouts.admin');
    }
}
