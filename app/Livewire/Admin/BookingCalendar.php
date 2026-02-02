<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Room;
use App\Models\Area;
use Carbon\Carbon;

class BookingCalendar extends Component
{
    use \Livewire\WithFileUploads;

    public $month;
    public $year;
    public $selectedArea = '';

    // Modal & Form State
    public $showModal = false;
    public $editingBookingId = null;
    public $activeModalTab = 'overview';
    public $activeTab = 'existing';
    public $selected_services = [];
    public $service_inputs = [];
    public $usage_logs = [];
    public $new_log = [
        'service_id' => '',
        'type' => 'fixed',
        'billing_unit' => 'quantity',
        'start_index' => '',
        'end_index' => '',
        'quantity' => 1,
        'unit_price' => 0,
        'billing_date' => '',
        'notes' => '',
    ];

    public $customer_id;
    public $new_customer_name;
    public $new_customer_phone;
    public $new_customer_email;
    public $new_customer_identity;
    public $new_customer_nationality;
    public $new_customer_visa_number;
    public $new_customer_visa_expiry;
    public $new_customer_notes;
    public $new_customer_image;

    public $room_id;
    public $price_type = 'day';
    public $unit_price = 0;
    public $check_in;
    public $check_out;
    public $price;
    public $deposit = 0;
    public $status = 'pending';
    public $notes;

    public $manual_fee_amount;
    public $manual_fee_notes;
    public $manual_fee_date;

    protected $listeners = ['area-selected' => '$refresh', 'refreshView' => '$refresh'];

    public function mount()
    {
        $this->month = now()->month;
        $this->year = now()->year;
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

    public function rules()
    {
        return [
            'room_id' => 'required|exists:rooms,id',
            'price_type' => 'required|in:day,month',
            'unit_price' => 'required',
            'check_in' => 'required',
            'check_out' => [
                $this->price_type === 'month' ? 'nullable' : 'required',
                $this->price_type !== 'month' ? 'after:check_in' : '',
            ],
            'price' => 'required',
            'deposit' => 'nullable',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
            'notes' => 'nullable|string',
            'customer_id' => $this->activeTab === 'existing' ? 'required|exists:customers,id' : 'nullable',
            'new_customer_name' => $this->activeTab === 'new' ? 'required|string|max:255' : 'nullable',
        ];
    }

    public function updatedRoomId()
    {
        $this->updatePricing();
    }

    public function updatedPriceType()
    {
        $this->updatePricing();
    }

    protected function updatePricing()
    {
        if (!$this->room_id)
            return;
        $room = \App\Models\Room::find($this->room_id);
        if (!$room)
            return;

        $priceVal = ($this->price_type === 'month') ? ($room->price_day ?? 0) : ($room->price_day ?? 0);
        $this->unit_price = number_format($priceVal, 0, ',', '.');
    }

    public function setTab($tab)
    {
        $this->activeModalTab = $tab;
    }

    public function createBooking($roomId, $date)
    {
        $this->resetValidation();
        $this->reset(['customer_id', 'new_customer_name', 'new_customer_phone', 'new_customer_email', 'new_customer_identity', 'new_customer_nationality', 'new_customer_visa_number', 'new_customer_visa_expiry', 'new_customer_notes', 'new_customer_image', 'room_id', 'price_type', 'unit_price', 'check_in', 'check_out', 'price', 'deposit', 'status', 'notes', 'editingBookingId', 'selected_services', 'usage_logs']);

        $this->room_id = $roomId;
        $this->check_in = $date . 'T14:00';
        $this->check_out = \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d\T12:00');
        $this->price_type = 'day';
        $this->activeTab = 'existing';
        $this->manual_fee_date = date('Y-m-d');
        $this->activeModalTab = 'overview';
        $this->showModal = true;

        $this->updatePricing();
    }

    public function editBooking($id)
    {
        $this->resetValidation();
        $booking = \App\Models\Booking::with(['services', 'usageLogs.service', 'room', 'customer'])->findOrFail($id);
        $this->editingBookingId = $id;

        $this->customer_id = $booking->customer_id;
        $this->activeTab = 'existing';
        $this->room_id = $booking->room_id;
        $this->price_type = ($booking->price_type === 'month') ? 'month' : 'day';
        $this->unit_price = number_format($booking->unit_price ?? 0, 0, ',', '.');
        $this->check_in = $booking->check_in ? $booking->check_in->format('Y-m-d\TH:i') : null;
        $this->check_out = $booking->check_out ? $booking->check_out->format('Y-m-d\TH:i') : null;
        $this->price = number_format($booking->price, 0, ',', '.');
        $this->deposit = $booking->deposit ? number_format($booking->deposit, 0, ',', '.') : 0;
        $this->status = $booking->status;
        $this->notes = $booking->notes;

        $this->selected_services = [];
        foreach ($booking->services as $service) {
            $this->selected_services[$service->id] = [
                'selected' => true,
                'start_index' => $service->pivot->start_index,
                'end_index' => $service->pivot->end_index,
                'quantity' => $service->pivot->quantity,
                'note' => $service->pivot->note,
            ];
            $this->initServiceInput($service->id);
        }

        $this->usage_logs = $booking->usageLogs->map(function ($log) {
            return [
                'id' => $log->id,
                'service_name' => $log->service->name ?? 'Phí phòng/Khác',
                'type' => $log->type,
                'billing_unit' => $log->billing_unit,
                'start_index' => $log->start_index,
                'end_index' => $log->end_index,
                'quantity' => $log->quantity,
                'unit_price' => $log->unit_price,
                'total_amount' => $log->total_amount,
                'billing_date' => $log->billing_date ? $log->billing_date->format('Y-m-d') : null,
                'notes' => $log->notes,
            ];
        })->toArray();

        $this->manual_fee_date = date('Y-m-d');
        $this->activeModalTab = 'overview';
        $this->showModal = true;
    }

    public function initServiceInput($serviceId)
    {
        $service = \App\Models\Service::find($serviceId);
        if (!$service)
            return;

        $startIndex = 0;
        $logs = collect($this->usage_logs)->where('service_id', $serviceId)->sortByDesc('billing_date');
        if ($logs->isNotEmpty()) {
            $startIndex = $logs->first()['end_index'] ?? 0;
        }

        $this->service_inputs[$serviceId] = [
            'start_index' => $startIndex,
            'end_index' => '',
            'quantity' => 1,
            'unit_price' => number_format($service->unit_price, 0, ',', '.'),
            'billing_date' => date('Y-m-d'),
            'notes' => '',
        ];
    }

    public function addServiceLog($serviceId)
    {
        $input = $this->service_inputs[$serviceId] ?? null;
        if (!$input)
            return;
        $service = \App\Models\Service::find($serviceId);
        if (!$service)
            return;

        $price = (float) str_replace([',', '.'], '', $input['unit_price'] ?? '0');
        $total = 0;
        if ($service->type === 'meter') {
            $start = (float) str_replace([',', '.'], '', $input['start_index'] ?? '0');
            $end = (float) str_replace([',', '.'], '', $input['end_index'] ?? '0');
            $total = max(0, $end - $start) * $price;
        } else {
            $total = (float) ($input['quantity'] ?? 1) * $price;
        }

        $logData = [
            'service_id' => $serviceId,
            'service_name' => $service->name,
            'type' => $service->type,
            'billing_unit' => $service->unit_name,
            'start_index' => $input['start_index'] ?: 0,
            'end_index' => $input['end_index'] ?: 0,
            'quantity' => $input['quantity'] ?: 1,
            'unit_price' => $price,
            'total_amount' => $total,
            'billing_date' => $input['billing_date'] ?: date('Y-m-d'),
            'notes' => $input['notes'] ?? '',
        ];

        if ($this->editingBookingId) {
            $booking = \App\Models\Booking::find($this->editingBookingId);
            if ($booking) {
                $newDbLog = $booking->usageLogs()->create([
                    'service_id' => $logData['service_id'],
                    'type' => $logData['type'],
                    'billing_unit' => $logData['billing_unit'],
                    'start_index' => $logData['start_index'],
                    'end_index' => $logData['end_index'],
                    'quantity' => $logData['quantity'],
                    'unit_price' => $logData['unit_price'],
                    'total_amount' => $logData['total_amount'],
                    'billing_date' => $logData['billing_date'],
                    'notes' => $logData['notes'],
                ]);
                $logData['id'] = $newDbLog->id;
            }
        }
        $this->usage_logs[] = $logData;
        if ($service->type === 'meter') {
            $this->service_inputs[$serviceId]['start_index'] = $input['end_index'];
            $this->service_inputs[$serviceId]['end_index'] = '';
        }
    }

    public function addManualSurcharge()
    {
        if (!$this->manual_fee_amount)
            return;
        $amount = (float) str_replace([',', '.'], '', $this->manual_fee_amount);
        $logData = [
            'service_id' => null,
            'service_name' => 'Phí phụ thu khác',
            'type' => 'manual',
            'billing_unit' => 'Lần',
            'start_index' => 0,
            'end_index' => 0,
            'quantity' => 1,
            'unit_price' => $amount,
            'total_amount' => $amount,
            'billing_date' => $this->manual_fee_date ?: date('Y-m-d'),
            'notes' => $this->manual_fee_notes,
        ];
        if ($this->editingBookingId) {
            $booking = \App\Models\Booking::find($this->editingBookingId);
            if ($booking) {
                $newDbLog = $booking->usageLogs()->create([
                    'type' => 'manual',
                    'billing_unit' => 'Lần',
                    'unit_price' => $amount,
                    'total_amount' => $amount,
                    'billing_date' => $logData['billing_date'],
                    'notes' => $logData['notes'],
                ]);
                $logData['id'] = $newDbLog->id;
            }
        }
        $this->usage_logs[] = $logData;
        $this->manual_fee_amount = '';
        $this->manual_fee_notes = '';
    }

    public function removeUsageLog($index)
    {
        $log = $this->usage_logs[$index] ?? null;
        if ($log && isset($log['id'])) {
            \App\Models\BookingUsageLog::find($log['id'])?->delete();
        }
        unset($this->usage_logs[$index]);
        $this->usage_logs = array_values($this->usage_logs);
    }

    public function toggleService($serviceId)
    {
        if (!isset($this->selected_services[$serviceId])) {
            $this->selected_services[$serviceId] = ['selected' => false, 'start_index' => 0, 'end_index' => 0, 'quantity' => 1, 'note' => ''];
        }
        $this->selected_services[$serviceId]['selected'] = !($this->selected_services[$serviceId]['selected'] ?? false);
        if ($this->selected_services[$serviceId]['selected'])
            $this->initServiceInput($serviceId);
        else
            unset($this->service_inputs[$serviceId]);
    }

    public function save()
    {
        $cleanPrice = str_replace('.', '', $this->price);
        $cleanDeposit = str_replace('.', '', $this->deposit);
        $cleanUnitPrice = str_replace('.', '', $this->unit_price);

        $this->validate();

        $customerId = $this->customer_id;
        if ($this->activeTab === 'new') {
            $customer = \App\Models\Customer::create([
                'name' => $this->new_customer_name,
                'phone' => $this->new_customer_phone,
                'email' => $this->new_customer_email,
                'identity_id' => $this->new_customer_identity,
            ]);
            $customerId = $customer->id;
        }

        $data = [
            'customer_id' => $customerId,
            'room_id' => $this->room_id,
            'price_type' => $this->price_type,
            'unit_price' => $cleanUnitPrice,
            'check_in' => $this->check_in,
            'check_out' => ($this->price_type === 'month' && empty($this->check_out)) ? null : $this->check_out,
            'price' => $cleanPrice,
            'deposit' => $cleanDeposit,
            'status' => $this->status,
            'notes' => $this->notes,
        ];

        if ($this->editingBookingId) {
            $booking = \App\Models\Booking::find($this->editingBookingId);
            $booking->update($data);
        } else {
            $booking = \App\Models\Booking::create($data);
        }

        $syncData = [];
        foreach ($this->selected_services as $serviceId => $item) {
            if (!empty($item['selected'])) {
                $service = \App\Models\Service::find($serviceId);
                if ($service) {
                    $pivot = ['unit_price' => $service->unit_price, 'note' => $item['note'] ?? null];
                    if ($service->type === 'meter') {
                        $pivot['start_index'] = $item['start_index'] ?? 0;
                        $pivot['end_index'] = $item['end_index'] ?? 0;
                        $pivot['usage'] = max(0, $pivot['end_index'] - $pivot['start_index']);
                        $pivot['total_amount'] = $pivot['usage'] * $pivot['unit_price'];
                    } else {
                        $pivot['quantity'] = $item['quantity'] ?? 1;
                        $pivot['total_amount'] = $pivot['quantity'] * $pivot['unit_price'];
                    }
                    $syncData[$serviceId] = $pivot;
                }
            }
        }
        $booking->services()->sync($syncData);

        if (!$this->editingBookingId) {
            foreach ($this->usage_logs as $log) {
                $booking->usageLogs()->create([
                    'service_id' => $log['service_id'] ?: null,
                    'type' => $log['type'],
                    'billing_unit' => $log['billing_unit'],
                    'start_index' => $log['start_index'] ?? 0,
                    'end_index' => $log['end_index'] ?? 0,
                    'quantity' => $log['quantity'] ?? 1,
                    'unit_price' => $log['unit_price'],
                    'total_amount' => $log['total_amount'],
                    'billing_date' => $log['billing_date'],
                    'notes' => $log['notes'],
                ]);
            }
        }

        $this->showModal = false;
        $this->dispatch('refreshView');
    }

    public function getDaysInMonthProperty()
    {
        $start = \Carbon\Carbon::createFromDate($this->year, $this->month, 1);
        $daysInMonth = $start->daysInMonth;

        $days = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $days[] = $start->copy()->day($i);
        }

        return $days;
    }

    public function getRoomsProperty()
    {
        $query = \App\Models\Room::query()
            ->with([
                'area',
                'bookings' => function ($q) {
                    $startOfMonth = \Carbon\Carbon::create($this->year, $this->month, 1)->startOfDay();
                    $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

                    $q->where(function ($query) use ($startOfMonth, $endOfMonth) {
                        $query->where('check_in', '<=', $endOfMonth)
                            ->where(function ($sub) use ($startOfMonth) {
                                $sub->where('check_out', '>=', $startOfMonth)
                                    ->orWhereNull('check_out');
                            });
                    });
                }
            ]);

        if (session('admin_selected_area_id')) {
            $query->where('area_id', session('admin_selected_area_id'));
        }

        if ($this->selectedArea) {
            $query->where('area_id', $this->selectedArea);
        }

        return $query->get()->groupBy('area.name');
    }

    public function render()
    {
        return view('livewire.admin.booking-calendar', [
            'areas' => \App\Models\Area::all(),
            'roomsData' => $this->rooms,
            'customers' => \App\Models\Customer::orderBy('name')->get(),
            'all_services' => \App\Models\Service::where('is_active', true)->orderBy('name')->get(),
            'all_rooms' => \App\Models\Room::with('area')->orderBy('code')->get(),
        ])->layout('components.layouts.admin');
    }
}
