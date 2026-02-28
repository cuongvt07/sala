<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Area;
use App\Models\Service;
use App\Models\Customer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class BookingCalendar extends Component
{
    use \Livewire\WithFileUploads;

    public $startDate;
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

    // Customer Details for Check-in
    public $customer_identity;
    public $customer_nationality;
    public $customer_visa_number;
    public $customer_visa_expiry;

    public $room_id;
    public $price_type = 'day';
    public $unit_price = 0;
    public $check_in;
    public $check_out;
    public $price;
    public $deposit = 0;
    public $deposit_2 = 0;
    public $deposit_3 = 0;
    public $status = 'pending';
    public $notes;

    public $manual_fee_amount;
    public $manual_fee_notes;
    public $manual_fee_date;
    public $countries = [];

    protected $listeners = ['area-selected' => '$refresh', 'refreshView' => '$refresh'];

    public function updatedCustomerId($value)
    {
        if ($value) {
            $customer = Customer::find($value);
            if ($customer) {
                // Pre-fill check-in info from existing customer
                $this->customer_identity = $customer->identity_id;
                $this->customer_nationality = $customer->nationality;
                $this->customer_visa_number = $customer->visa_number;
                $this->customer_visa_expiry = $customer->visa_expiry ? $customer->visa_expiry->format('Y-m-d') : null;
            }
        } else {
            $this->reset(['customer_identity', 'customer_nationality', 'customer_visa_number', 'customer_visa_expiry']);
        }
    }

    public function mount()
    {
        $this->startDate = now()->format('Y-m-d');

        // Load countries list
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

    public function nextMonth()
    {
        $this->startDate = \Carbon\Carbon::parse($this->startDate ?? now()->format('Y-m-d'))->addDays(30)->format('Y-m-d');
    }

    public function prevMonth()
    {
        $this->startDate = \Carbon\Carbon::parse($this->startDate ?? now()->format('Y-m-d'))->subDays(30)->format('Y-m-d');
    }

    public function goToToday()
    {
        $this->startDate = now()->format('Y-m-d');
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
            'deposit_2' => 'nullable',
            'deposit_3' => 'nullable',
            'status' => 'required|in:pending,checked_in,checked_out,cancelled',
            'notes' => 'nullable|string',
            'customer_id' => $this->activeTab === 'existing' ? 'required' : 'nullable',
            'new_customer_name' => $this->activeTab === 'new' ? 'required|string|max:255' : 'nullable',
        ];
    }

    public function updatedRoomId()
    {
        $this->updatePricing();
    }

    public function updatedPriceType()
    {
        if ($this->price_type === 'month') {
            $this->check_out = '';
            $this->resetValidation('check_out');
        }
        $this->updatePricing();
        $this->calculateTotal();
    }

    public function updatedCheckIn()
    {
        $this->calculateTotal();
    }

    public function updatedCheckOut()
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        if (!$this->check_in || !$this->check_out || !$this->unit_price)
            return;

        try {
            $start = Carbon::parse($this->check_in);
            $end = Carbon::parse($this->check_out);

            if ($end->lte($start))
                return;

            $unitPrice = (float) str_replace(['.', ','], '', $this->unit_price);

            if ($this->price_type === 'day') {
                // Calculate days, including partial days if needed, but per requirement "day" usually means 24h blocks or calendar days.
                // Logic based on nightly rate:
                $diff = abs($start->diffInDays($end));
                // If less than 1 day but parsed, count as 1? Or float? 
                // Usually hotels count nights. 
                $days = max(1, $diff);
                $total = $days * $unitPrice;
            } else {
                // Month
                $months = $start->diffInMonths($end);
                $days = $start->copy()->addMonths($months)->diffInDays($end);
                // Simple approximation or exact logic? 
                // Let's stick to simple unit_price propagation for now if month, or 1 month default.
                // Actually, if price_type is month, usually fixed monthly price.
                // Let's just use unit_price if month, or calculate if multiple months.
                $total = max(1, $months) * $unitPrice;
                if ($months < 1 && $days > 0) {
                    // Partial month logic? For now let's just default to unitPrice for simplicity unless duration > 1 month
                    $total = $unitPrice;
                }
            }

            $this->price = number_format($total, 0, ',', '.');

        } catch (\Exception $e) {
            // Ignore parse errors
        }
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
        $this->reset(['customer_id', 'new_customer_name', 'new_customer_phone', 'new_customer_email', 'new_customer_identity', 'new_customer_nationality', 'new_customer_visa_number', 'new_customer_visa_expiry', 'new_customer_notes', 'new_customer_image', 'customer_identity', 'customer_nationality', 'customer_visa_number', 'customer_visa_expiry', 'room_id', 'price_type', 'unit_price', 'check_in', 'check_out', 'price', 'deposit', 'deposit_2', 'deposit_3', 'status', 'notes', 'editingBookingId', 'selected_services', 'usage_logs']);

        $this->room_id = $roomId;
        $this->check_in = $date . 'T00:00';
        $this->check_out = \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d\T00:00');
        $this->price_type = 'day';
        $this->activeTab = 'existing';
        $this->manual_fee_date = date('Y-m-d');
        $this->activeModalTab = 'overview';
        $this->showModal = true;

        $this->updatePricing();
        $this->calculateTotal();
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
        $this->deposit_2 = $booking->deposit_2 ? number_format($booking->deposit_2, 0, ',', '.') : 0;
        $this->deposit_3 = $booking->deposit_3 ? number_format($booking->deposit_3, 0, ',', '.') : 0;
        $this->status = $booking->status;
        $this->notes = $booking->notes;

        // Load Customer Check-in Info
        if ($booking->customer) {
            $this->customer_identity = $booking->customer->identity_id;
            $this->customer_nationality = $booking->customer->nationality;
            $this->customer_visa_number = $booking->customer->visa_number;
            $this->customer_visa_expiry = $booking->customer->visa_expiry ? $booking->customer->visa_expiry->format('Y-m-d') : null;
        }

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
        $cleanDeposit2 = str_replace('.', '', $this->deposit_2);
        $cleanDeposit3 = str_replace('.', '', $this->deposit_3);
        $cleanUnitPrice = str_replace('.', '', $this->unit_price);

        // Check-in Requirement Validation
        if ($this->status === 'checked_in') {
            $this->validate([
                'customer_identity' => 'required',
                'customer_nationality' => 'required',
            ], [
                'customer_identity.required' => 'Vui lòng nhập CMT/CCCD/Passport khi nhận phòng.',
                'customer_nationality.required' => 'Vui lòng nhập quốc tịch khi nhận phòng.',
            ]);
        }

        $this->validate();

        $customerId = $this->customer_id;

        if ($this->activeTab === 'new') {
            // Tạo khách hàng mới - chỉ lưu thông tin cơ bản
            $newCustomerData = [
                'name' => $this->new_customer_name,
                'phone' => $this->new_customer_phone,
                'email' => $this->new_customer_email,
                'identity_id' => $this->new_customer_identity,
            ];

            // Chỉ thêm thông tin check-in nếu trạng thái là 'checked_in'
            if ($this->status === 'checked_in') {
                $identityValue = $this->customer_identity ?: $this->new_customer_identity;
                $newCustomerData['identity_id'] = $identityValue;
                $newCustomerData['nationality'] = $this->customer_nationality;
                $newCustomerData['visa_number'] = $identityValue; // Lưu cùng giá trị với identity_id
                $newCustomerData['visa_expiry'] = $this->customer_visa_expiry;
            }

            $customer = \App\Models\Customer::create($newCustomerData);
            $customerId = $customer->id;
        } elseif ($customerId && $this->status === 'checked_in') {
            // Chỉ cập nhật thông tin check-in khi trạng thái là 'checked_in'
            $customer = \App\Models\Customer::find($customerId);
            if ($customer) {
                $customerDataToUpdate = [
                    'identity_id' => $this->customer_identity,
                    'nationality' => $this->customer_nationality,
                    'visa_number' => $this->customer_identity, // Lưu cùng giá trị với identity_id
                    'visa_expiry' => $this->customer_visa_expiry,
                ];
                // Filter out nulls to avoid overwriting existing values with null
                $filteredData = array_filter($customerDataToUpdate, fn($value) => !is_null($value) && $value !== '');
                if (!empty($filteredData)) {
                    $customer->update($filteredData);
                }
            }
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
            'deposit_2' => $cleanDeposit2,
            'deposit_3' => $cleanDeposit3,
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

        session()->flash('message', $this->editingBookingId ? 'Cập nhật đặt phòng thành công!' : 'Tạo đặt phòng thành công!');

        $this->reset(['editingBookingId', 'showModal']);
        $this->dispatch('refreshView');
    }

    public function getDaysInMonthProperty()
    {
        $start = \Carbon\Carbon::parse($this->startDate ?? now()->format('Y-m-d'));
        
        $days = [];
        for ($i = 0; $i < 30; $i++) {
            $days[] = $start->copy()->addDays($i);
        }

        return $days;
    }

    public function getRoomsProperty()
    {
        $query = \App\Models\Room::query()
            ->with([
                'area',
                'bookings' => function ($q) {
                    $startOfWindow = \Carbon\Carbon::parse($this->startDate ?? now()->format('Y-m-d'))->startOfDay();
                    $endOfWindow = $startOfWindow->copy()->addDays(29)->endOfDay();
                    // Eager load bookings for the displayed window
                    $q->where('status', '!=', 'checked_out')
                        ->where(function ($query) use ($startOfWindow, $endOfWindow) {
                        $query->where('check_in', '<=', $endOfWindow)
                            ->where(function ($sub) use ($startOfWindow) {
                                $sub->where('check_out', '>=', $startOfWindow)
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

        return $query->get()->groupBy('area.name')->map(function ($rooms) {
            foreach ($rooms as $room) {
                $this->calculateStacking($room);
            }
            return $rooms;
        });
    }

    public function calculateStacking($room)
    {
        $bookings = $room->bookings->sortBy('check_in');
        // "k cần xếp chồng nữa mà để 1 dòng hết" -> No lanes needed, stack_index always 0.

        foreach ($bookings as $booking) {
            // Determine Visual Start Day (relative to month start)
            $windowStart = \Carbon\Carbon::parse($this->startDate ?? now()->format('Y-m-d'))->startOfDay();

            // "lúc này k quan tâm thười gain nữa" -> Normalize to StartOfDay
            $checkInDate = (is_a($booking->check_in, 'Carbon\Carbon') ? $booking->check_in : \Carbon\Carbon::parse($booking->check_in))->startOfDay();
            $checkOutDate = ($booking->check_out ? (is_a($booking->check_out, 'Carbon\Carbon') ? $booking->check_out : \Carbon\Carbon::parse($booking->check_out)) : $checkInDate->copy()->addDay())->startOfDay();

            // Raw start index based on Date only
            $diffStartDays = $windowStart->diffInDays($checkInDate, false); // Int

            if ($booking->price_type === 'month') {
                // Keep existing Month logic but mapped to new base?
                // Visual Start usually at start of day (0.0) for Month?
                $visualStart = (float) $diffStartDays;

                // Cross-month handling specific for Month Type
                if ($visualStart < 0) {
                    $visualStart = 0;
                    $diffM1vcCheckout = $windowStart->diffInDays($checkOutDate, false);
                    $remainingDays = max(1, $diffM1vcCheckout);
                    if ($remainingDays <= 10) {
                        $visualDays = $remainingDays;
                    } else {
                        $visualDays = 5;
                    }
                } else {
                    $visualDays = 10;
                }
            } elseif ($booking->price_type === 'hour') {
                $visualStart = (float) $diffStartDays;
                $visualDays = 0.5;
                if ($visualStart < 0)
                    $visualStart = 0;
            } else {
                // Type 'day'.
                // "chia đổi ngày đó ra" -> Start and End at 0.5 (Noon)
                // Visual Start = Date + 0.5
                // Visual End = Date + 0.5

                $rawStartPos = $diffStartDays + 0.5;
                $diffEndDays = $windowStart->diffInDays($checkOutDate, false);
                $rawEndPos = $diffEndDays + 0.5;

                $visualStart = $rawStartPos;
                $visualDays = $rawEndPos - $rawStartPos;

                // Minimum width safety? Request says "chia đôi". 1 day -> 1.5 - 0.5 = 1.0. Correct.

                // Cross-month handling
                // If started prev month (Start < 0)
                if ($visualStart < 0) {
                    $loss = abs($visualStart);
                    $visualStart = 0;
                    $visualDays = max(0, $visualDays - $loss);
                }
            }

            $booking->visual_days = $visualDays;
            $booking->visual_start = $visualStart;
            $booking->stack_index = 0; // Always top row
        }

        $room->max_stack_index = 0; // Single row height
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
