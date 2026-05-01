<?php

namespace App\Livewire\Admin\Bookings;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Booking;

use App\Models\Customer;
use App\Models\Room;
use App\Models\Service;
use App\Models\BookingUsageLog;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceMail;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $showModal = false;
    public $editingBookingId = null;
    public $activeModalTab = 'info'; // info, billing

    // Form inputs
    public $activeTab = 'existing'; // 'existing' or 'new' for customer tab

    // Services
    public $selected_services = [];

    // Temporary inputs for History Tab row-based logging
    public $service_inputs = []; // [service_id => ['start_index' => val, 'end_index' => val, 'quantity' => val, 'unit_price' => val, 'billing_date' => val]]

    // Usage Logs (History Tab)
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

    // Existing Customer
    public $customer_id;

    // New Customer
    public $new_customer_name;
    public $new_customer_phone;
    public $new_customer_email;
    public $new_customer_identity;
    public $new_customer_gender;
    public $new_customer_birthday;
    public $new_customer_nationality;
    public $new_customer_visa_number;
    public $new_customer_visa_expiry;
    public $new_customer_notes;
    public $new_customer_image;

    public $additional_guests = [];

    public function addGuest()
    {
        $this->additional_guests[] = ['name' => '', 'identity' => ''];
    }

    public function removeGuest($index)
    {
        unset($this->additional_guests[$index]);
        $this->additional_guests = array_values($this->additional_guests);
    } // for file upload

    public $room_id;
    public $price_type = 'day'; // day, hour, month
    public $is_contract = false;
    public $unit_price = 0;
    public $check_in;
    public $check_out;
    public $price;
    public $deposit = 0;
    public $deposit_2 = 0;
    public $deposit_3 = 0;
    public $status = 'pending';
    public $notes;

    // Global billing date for all services in this period
    public $global_billing_date;

    // Invoice Modal
    public $showInvoiceModal = false;
    public $invoice_year;
    public $invoice_data = [];

    public $showConfirmationModal = false;
    public $confirmation_data = [];

    public $deposits = []; // Stores state of deposits (1, 2, 3)

    // Manual Fee Input
    public $manual_fee_amount;
    public $manual_fee_notes;
    public $manual_fee_billing_date;
    public $manual_fee_date;
    public $manual_fee_date_input;

    public $extra_nights = 0;
    public $extra_night_price = 0;
    public $showExtraNights = false;

    protected $listeners = ['area-selected' => '$refresh'];

    use \App\Traits\HasCountryData;

    public function updatedNewCustomerNationality($value)
    {
        $this->new_customer_nationality = $this->handleNationalityUpdate($value);
    }

    public function rules()
    {
        $rules = [
            'room_id' => 'required|exists:rooms,id',
            'price_type' => 'required|in:day,month',
            'is_contract' => 'boolean',
            'unit_price' => 'required|numeric|min:0',
            'check_in' => 'required|date',
            'check_out' => [
                $this->price_type === 'month' ? 'nullable' : 'required',
                'date',
                $this->price_type !== 'month' ? 'after:check_in' : '',
            ],
            'price' => 'required|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
            'deposit_2' => 'nullable|numeric|min:0',
            'deposit_3' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,checked_in,checked_out,cancelled',
            'notes' => 'nullable|string',
        ];

        if ($this->activeTab === 'existing') {
            $rules['customer_id'] = 'required|exists:customers,id';
        } else {
            $rules['new_customer_name'] = 'required|string|max:255';
            $rules['new_customer_phone'] = 'nullable|string|max:20';
            $rules['new_customer_email'] = 'nullable|email|max:255';
            $rules['new_customer_identity'] = 'nullable|string|max:255';
            $rules['new_customer_nationality'] = 'nullable|string|max:255';
            $rules['new_customer_visa_number'] = 'nullable|string|max:255';
            $rules['new_customer_birthday'] = 'nullable|date';
            $rules['new_customer_visa_expiry'] = 'nullable|date';
            $rules['new_customer_image'] = 'nullable|image|max:10240'; // 10MB
        }

        return $rules;
    }

    public function updatedRoomId()
    {
        $this->updatePricing();
    }

    public function updatedPriceType()
    {
        $this->is_contract = ($this->price_type === 'month');
        $this->updatePricing();
        $this->calculateTotal();
    }

    public function setPriceType($type)
    {
        $this->price_type = $type;
        $this->updatedPriceType();
    }

    public function updatedCheckIn()
    {
        $this->calculateTotal();
    }

    public function updatedCheckOut()
    {
        $this->calculateTotal();
    }

    public function updatedCustomerId($value)
    {
        if ($value) {
            $customer = Customer::find($value);
            if ($customer) {
                $this->new_customer_name = $customer->name;
                $this->new_customer_phone = $customer->phone;
                $this->new_customer_email = $customer->email;
                $this->new_customer_gender = $customer->gender;
                $this->new_customer_birthday = $customer->birthday ? $customer->birthday->format('Y-m-d') : null;
                $this->new_customer_identity = $customer->identity_id;
                $this->new_customer_nationality = $customer->nationality;
                $this->new_customer_visa_number = $customer->visa_number;
                $this->new_customer_visa_expiry = $customer->visa_expiry ? $customer->visa_expiry->format('Y-m-d') : null;
            }
        } else {
            $this->reset(['new_customer_name', 'new_customer_phone', 'new_customer_email', 'new_customer_gender', 'new_customer_birthday', 'new_customer_identity', 'new_customer_nationality', 'new_customer_visa_number', 'new_customer_visa_expiry']);
        }
    }

    public function updatedUnitPrice()
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        if (!$this->check_in || !$this->check_out || !$this->unit_price || $this->price_type === 'hour') {
            return;
        }

        try {
            $start = \Carbon\Carbon::parse($this->check_in);
            $end = \Carbon\Carbon::parse($this->check_out);

            if ($end->lte($start)) {
                return;
            }

            $unitPrice = (float) str_replace(['.', ','], '', $this->unit_price);
            $nights = $start->diffInDays($end);
            
            if ($this->price_type === 'day') {
                $nights = max(1, $nights);
                $total = $nights * $unitPrice;
            } else {
                // Contract (Month) - Calculate daily rate (monthly / 30)
                $total = ($unitPrice / 30) * $nights;
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

        $room = Room::find($this->room_id);
        if (!$room)
            return;

        if ($this->price_type === 'hour') {
            $this->unit_price = $room->price_hour ?? 0;
        } elseif ($this->price_type === 'day') {
            $this->unit_price = $room->price_day ?? 0;
        } elseif ($this->price_type === 'month') {
            $this->unit_price = $room->price_month ?? 0;
        }

        $this->extra_night_price = number_format((float)str_replace(['.', ','], '', $this->unit_price), 0, ',', '.');
    }

    public function setTab($tab)
    {
        $this->activeModalTab = $tab;
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['customer_id', 'new_customer_name', 'new_customer_phone', 'new_customer_email', 'new_customer_identity', 'new_customer_visa_number', 'new_customer_visa_expiry', 'new_customer_notes', 'new_customer_image', 'room_id', 'price_type', 'is_contract', 'unit_price', 'check_in', 'check_out', 'price', 'deposit', 'deposit_2', 'deposit_3', 'status', 'notes', 'editingBookingId', 'selected_services', 'usage_logs']);
        $this->price_type = 'day';
        $this->activeTab = 'existing';
        $this->manual_fee_date = date('Y-m-d');
        $this->activeModalTab = 'info';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $booking = Booking::with(['services', 'usageLogs.service'])->findOrFail($id);
        $this->editingBookingId = $id;

        $this->customer_id = $booking->customer_id;
        if ($booking->customer) {
            $this->new_customer_nationality = $booking->customer->nationality;
        }
        $this->room_id = $booking->room_id; // Always default to existing for edit
        $this->price_type = ($booking->price_type === 'month') ? 'month' : 'day'; // Default to day, map legacy 'hour' to day
        $this->is_contract = (bool)$booking->is_contract;
        $this->unit_price = $booking->unit_price ?? 0;
        $this->check_in = $booking->check_in ? $booking->check_in->format('Y-m-d') : null; // Ensure Y-m-d for date input
        $this->check_out = $booking->check_out ? $booking->check_out->format('Y-m-d') : null;

        // Format money fields for display
        $this->price = number_format($booking->price, 0, ',', '.');
        $this->deposit = $booking->deposit ? number_format($booking->deposit, 0, ',', '.') : 0;
        $this->deposit_2 = $booking->deposit_2 ? number_format($booking->deposit_2, 0, ',', '.') : 0;
        $this->deposit_3 = $booking->deposit_3 ? number_format($booking->deposit_3, 0, ',', '.') : 0;

        $this->status = $booking->status;
        $this->notes = $booking->notes;
        $this->additional_guests = $booking->additional_guests ?? [];

        // Load Usage Logs first so we can use them for suggestions
        $this->usage_logs = $booking->usageLogs->map(function ($log) {
            return [
                'id' => $log->id,
                'service_id' => $log->service_id,
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
                'email_sent_at' => $log->email_sent_at ? $log->email_sent_at->format('d/m/Y H:i') : null,
            ];
        })->toArray();

        // Load selected services
        $this->selected_services = [];
        foreach ($booking->services as $service) {
            $this->selected_services[$service->id] = [
                'selected' => true,
                'start_index' => $service->pivot->start_index,
                'end_index' => $service->pivot->end_index,
                'quantity' => $service->pivot->quantity,
                'note' => $service->pivot->note,
            ];

            // Initialize service inputs for History tab
            $this->initServiceInput($service->id);
        }



        $this->manual_fee_date = date('Y-m-d');
        $this->extra_night_price = number_format($booking->unit_price, 0, ',', '.');
        $this->activeModalTab = 'info';
        $this->activeModalTab = 'info';
        $this->showModal = true;

        // Initialize Deposit States
        $this->loadDepositStates();
    }

    public function updatedDeposit() { $this->loadDepositStates(); }
    public function updatedDeposit2() { $this->loadDepositStates(); }
    public function updatedDeposit3() { $this->loadDepositStates(); }

    public function loadDepositStates()
    {
        $this->deposits = [];
        $depositFields = [1 => 'deposit', 2 => 'deposit_2', 3 => 'deposit_3'];
        
        $booking = $this->editingBookingId ? Booking::find($this->editingBookingId) : null;

        foreach ($depositFields as $index => $field) {
            $amountStr = str_replace(['.', ','], '', (string)($this->{$field} ?? '0'));
            $depositAmount = (float) $amountStr;

            if ($depositAmount > 0) {
                // Check if already applied (log exists with specific note)
                $noteKey = 'deposit_' . $index;
                $log = collect($this->usage_logs)->first(function ($l) use ($noteKey) {
                    return (($l['type'] ?? '') === 'deduction' && ($l['notes'] ?? '') === $noteKey);
                });

                if (!$log && $booking && !$this->is_contract) {
                    // Auto-apply if not already in logs
                    $logData = [
                        'service_id' => null,
                        'type' => 'deduction',
                        'service_name' => "KHẤU TRỪ CỌC LẦN $index",
                        'billing_unit' => 'transaction',
                        'start_index' => null,
                        'end_index' => null,
                        'quantity' => 1,
                        'unit_price' => -1 * $depositAmount,
                        'total_amount' => -1 * $depositAmount,
                        'billing_date' => $this->global_billing_date ?: date('Y-m-d'),
                        'notes' => $noteKey,
                    ];
                    $newDbLog = $booking->usageLogs()->create($logData);
                    $logData['id'] = $newDbLog->id;
                    $this->usage_logs[] = $logData;
                    $log = $logData;
                }

                $this->deposits[$index] = [
                    'amount' => $depositAmount,
                    'is_applied' => true, // Always true now
                    'log_id' => $log['id'] ?? null,
                    'applied_date' => $log['billing_date'] ?? null,
                    'is_current_period' => true
                ];
            }
        }
    }

    public function addUsageLog()
    {
        $this->validate([
            'new_log.unit_price' => 'required',
            'new_log.billing_date' => 'required|date',
        ]);

        $price = str_replace('.', '', $this->new_log['unit_price']);
        $quantity = $this->new_log['quantity'] ?: 1;

        $total = 0;
        if ($this->new_log['type'] === 'meter') {
            $usage = max(0, ($this->new_log['end_index'] ?? 0) - ($this->new_log['start_index'] ?? 0));
            $total = $usage * $price;
        } else {
            $total = $quantity * $price;
        }

        $serviceName = 'Phí khác';
        if ($this->new_log['service_id']) {
            $serviceName = Service::find($this->new_log['service_id'])->name;
        }

        $this->usage_logs[] = [
            'service_id' => $this->new_log['service_id'],
            'service_name' => $serviceName,
            'type' => $this->new_log['type'],
            'billing_unit' => $this->new_log['billing_unit'],
            'start_index' => $this->new_log['start_index'],
            'end_index' => $this->new_log['end_index'],
            'quantity' => $quantity,
            'unit_price' => $price,
            'total_amount' => $total,
            'billing_date' => $this->new_log['billing_date'],
            'notes' => $this->new_log['notes'],
        ];

        // Reset new log form
        $this->new_log = [
            'service_id' => '',
            'type' => 'fixed',
            'billing_unit' => 'quantity',
            'start_index' => '',
            'end_index' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'billing_date' => date('Y-m-d'),
            'notes' => '',
        ];
    }

    public function removeUsageLog($index)
    {
        $log = $this->usage_logs[$index] ?? null;

        if ($log && isset($log['id'])) {
            BookingUsageLog::find($log['id'])?->delete();
        }

        unset($this->usage_logs[$index]);
        $this->usage_logs = array_values($this->usage_logs);
    }

    public function removePeriodLogs($period)
    {
        // Remove all logs for a specific period (e.g., "01/2026")
        $toRemove = [];
        foreach ($this->usage_logs as $index => $log) {
            if (\Carbon\Carbon::parse($log['billing_date'])->format('m/Y') === $period) {
                // Remove from DB if it has an ID
                if (isset($log['id'])) {
                    BookingUsageLog::find($log['id'])?->delete();
                }
                $toRemove[] = $index;
            }
        }

        // Remove from array
        foreach (array_reverse($toRemove) as $index) {
            unset($this->usage_logs[$index]);
        }
        $this->usage_logs = array_values($this->usage_logs);
    }

    public function viewPeriodInvoice($period)
    {
        $this->invoice_period = $period;

        // Get booking data from database
        $booking = null;
        if ($this->editingBookingId) {
            $booking = Booking::with('customer', 'room')->find($this->editingBookingId);
        }

        // Gather all logs for this period
        $periodLogs = collect($this->usage_logs)->filter(function ($log) use ($period) {
            return \Carbon\Carbon::parse($log['billing_date'])->format('m/Y') === $period;
        });

        // Convert price to numeric (remove dots and đ)
        $roomPrice = (float) str_replace(['.', 'đ', ','], '', $this->price ?? '0');

        $this->invoice_data = [
            'period' => $period,
            'logs' => $periodLogs->values()->toArray(),
            'room_price' => $roomPrice,
            'total' => $periodLogs->sum('total_amount') + $roomPrice,
            'booking' => [
                'customer_name' => $booking?->customer?->name ?? 'N/A',
                'customer_phone' => $booking?->customer?->phone ?? 'N/A',
                'room_code' => $booking?->room?->code ?? 'N/A',
                'check_in' => $booking?->check_in?->format('d/m/Y') ?? 'N/A',
            ]
        ];

        $this->showInvoiceModal = true;
    }

    public function closeInvoiceModal()
    {
        $this->showInvoiceModal = false;
        $this->invoice_data = [];
    }

    public function viewConfirmation()
    {
        if (!$this->editingBookingId) {
            $this->dispatch('toast', message: 'Vui lòng lưu booking trước khi in xác nhận.', type: 'error');
            return;
        }

        $booking = Booking::with(['customer', 'room'])->find($this->editingBookingId);
        if (!$booking) return;

        $roomPrice = (float) str_replace(['.', 'đ', ','], '', $this->price ?? '0');
        $dep1 = (float) str_replace(['.', 'đ', ','], '', $this->deposit ?? '0');
        $dep2 = (float) str_replace(['.', 'đ', ','], '', $this->deposit_2 ?? '0');
        $dep3 = (float) str_replace(['.', 'đ', ','], '', $this->deposit_3 ?? '0');
        $totalDeposit = $dep1 + $dep2 + $dep3;

        $this->confirmation_data = [
            'booking_id' => $booking->id,
            'customer_name' => $booking->customer->name ?? 'N/A',
            'customer_phone' => $booking->customer->phone ?? 'N/A',
            'room_code' => $booking->room->code ?? 'N/A',
            'check_in' => $booking->check_in ? $booking->check_in->format('d / m / Y') : 'N/A',
            'check_out' => $booking->check_out ? $booking->check_out->format('d / m / Y') : 'Hợp đồng',
            'term_of_stay' => $booking->check_in && $booking->check_out ? $booking->check_in->diff($booking->check_out)->format('%a đêm') : 'Dài hạn',
            'unit_price' => number_format((float)str_replace(['.',','],'',$this->unit_price ?: 0), 0, ',', '.'),
            'price_type' => $this->price_type === 'month' ? 'tháng' : 'ngày',
            'room_price' => $roomPrice,
            'total_deposit' => $totalDeposit,
            'remaining' => $this->is_contract ? $roomPrice : ($roomPrice - $totalDeposit),
            'notes' => $this->notes,
            'additional_guests' => $this->additional_guests,
            'created_at' => now()->format('d/m/Y H:i')
        ];

        $this->showConfirmationModal = true;
    }

    public function closeConfirmationModal()
    {
        $this->showConfirmationModal = false;
        $this->confirmation_data = [];
    }

    public function initServiceInput($serviceId)
    {
        $service = Service::find($serviceId);
        if (!$service)
            return;

        // Always set start_index to null to show placeholder (hint)
        // The View will handle showing the suggested index in the placeholder

        $this->service_inputs[$serviceId] = [
            'start_index' => null, // Explicitly null to show placeholder
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

        $service = Service::find($serviceId);
        if (!$service)
            return;

        // Sanitize price: remove dots and commas, then cast to float
        $priceStr = str_replace([',', '.'], '', $input['unit_price'] ?? '0');
        $price = (float) $priceStr;

        $total = 0;
        if ($service->type === 'meter') {
            // Ensure indices are treated as numbers, cleaning any accidental dots/commas
            $start = (float) str_replace([',', '.'], '', $input['start_index'] ?? '0');
            $end = (float) str_replace([',', '.'], '', $input['end_index'] ?? '0');
            $usage = max(0, $end - $start);
            $total = $usage * $price;
        } else {
            $qty = (float) ($input['quantity'] ?? 1);
            $total = $qty * $price;
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
            'billing_date' => $this->global_billing_date ?: date('Y-m-d'),
            'notes' => $input['notes'] ?? '',
        ];

        if ($this->editingBookingId) {
            $booking = Booking::find($this->editingBookingId);
            if ($booking) {
                $newDbLog = $booking->usageLogs()->create([
                    'service_id' => $serviceId,
                    'type' => $service->type,
                    'billing_unit' => $service->unit_name,
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

        // Prepare next Start Index
        if ($service->type === 'meter') {
            $this->service_inputs[$serviceId]['start_index'] = $input['end_index'];
            $this->service_inputs[$serviceId]['end_index'] = '';
        }
    }

    public function addAllServiceLogs()
    {
        // Chốt tất cả dịch vụ đã chọn cùng lúc
        foreach ($this->selected_services as $serviceId => $data) {
            if (!empty($data['selected']) && isset($this->service_inputs[$serviceId])) {
                $this->addServiceLog($serviceId);
            }
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
            'billing_date' => $this->global_billing_date ?: date('Y-m-d'),
            'notes' => $this->manual_fee_notes,
        ];

        if ($this->editingBookingId) {
            $booking = Booking::find($this->editingBookingId);
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

        // Reset inputs
        $this->manual_fee_amount = '';
        $this->manual_fee_notes = '';
    }

    public function addExtraNightsSurcharge()
    {
        if (!$this->extra_nights || !$this->extra_night_price)
            return;

        $price = (float) str_replace([',', '.'], '', $this->extra_night_price);
        $nights = (float) $this->extra_nights;
        $total = $nights * $price;

        $logData = [
            'service_id' => null,
            'service_name' => "Tiền phòng thêm ($nights đêm)",
            'type' => 'manual',
            'billing_unit' => 'Đêm',
            'start_index' => 0,
            'end_index' => 0,
            'quantity' => $nights,
            'unit_price' => $price,
            'total_amount' => $total,
            'billing_date' => $this->global_billing_date ?: date('Y-m-d'),
            'notes' => "Phụ thu $nights đêm phòng",
        ];

        if ($this->editingBookingId) {
            $booking = Booking::find($this->editingBookingId);
            if ($booking) {
                $newDbLog = $booking->usageLogs()->create([
                    'type' => 'manual',
                    'billing_unit' => 'Đêm',
                    'unit_price' => $price,
                    'total_amount' => $total,
                    'billing_date' => $logData['billing_date'],
                    'notes' => $logData['notes'],
                ]);
                $logData['id'] = $newDbLog->id;
            }
        }

        $this->usage_logs[] = $logData;

        // Reset inputs
        $this->extra_nights = 0;
    }

    public function toggleService($serviceId)
    {
        if (!isset($this->selected_services[$serviceId])) {
            $this->selected_services[$serviceId] = [
                'selected' => false,
                'start_index' => 0,
                'end_index' => 0,
                'quantity' => 1,
                'note' => '',
            ];
        }

        $this->selected_services[$serviceId]['selected'] = !($this->selected_services[$serviceId]['selected'] ?? false);

        if ($this->selected_services[$serviceId]['selected']) {
            $this->initServiceInput($serviceId);
        } else {
            unset($this->service_inputs[$serviceId]);
        }
    }

    public function exportInvoice()
    {
        if (!$this->editingBookingId) {
            $this->dispatch('toast', message: 'Vui lòng lưu booking trước khi xuất hoá đơn.', type: 'error');
            return;
        }

        $booking = Booking::with(['customer', 'room', 'usageLogs.service'])->find($this->editingBookingId);
        if (!$booking) return;

        // Get all logs that are not deductions (deposits)
        $logs = $booking->usageLogs()
            ->where('type', '!=', 'deduction')
            ->orderBy('billing_date')
            ->get();

        if ($logs->isEmpty()) {
            $this->dispatch('toast', message: 'Không có khoản phí dịch vụ nào để xuất hoá đơn.', type: 'info');
        }

        $customerEmail = $booking->customer->email ?? null;

        if ($customerEmail) {
            try {
                Mail::to($customerEmail)->send(new InvoiceMail($booking, $logs));

                // Mark logs as email sent (optional, but keep for tracking)
                $logs->each(function ($log) {
                    $log->update(['email_sent_at' => now()]);
                });

                $this->dispatch('toast', message: 'Đã gửi hoá đơn đến ' . $customerEmail . ' thành công!', type: 'success');
            } catch (\Exception $e) {
                $this->dispatch('toast', message: 'Gửi email thất bại: ' . $e->getMessage(), type: 'error');
            }
        } else {
            $this->dispatch('toast', message: 'Khách hàng không có email. Không thể gửi hoá đơn.', type: 'warning');
        }

        // Reload booking data to refresh email_sent_at badges
        $this->edit($this->editingBookingId);
    }

    public function save()
    {
        // Sanitize money fields (remove dots)
        $this->price = str_replace('.', '', $this->price);
        $this->deposit = str_replace('.', '', $this->deposit);
        $this->deposit_2 = str_replace('.', '', $this->deposit_2);
        $this->deposit_3 = str_replace('.', '', $this->deposit_3);
        $this->unit_price = str_replace('.', '', $this->unit_price);

        // Convert empty strings to null for decimal columns
        $this->price = $this->price === '' ? null : $this->price;
        $this->deposit = $this->deposit === '' ? null : $this->deposit;
        $this->deposit_2 = $this->deposit_2 === '' ? null : $this->deposit_2;
        $this->deposit_3 = $this->deposit_3 === '' ? null : $this->deposit_3;
        $this->unit_price = $this->unit_price === '' ? null : $this->unit_price;

        $this->validate();

        $customerId = $this->customer_id;

        // Create new customer if tab is new
        if ($this->activeTab === 'new') {
            $imagePath = null;
            if ($this->new_customer_image) {
                $originalPath = $this->new_customer_image->getRealPath();
                $filename = 'customers/' . uniqid() . '.jpg';
                $storagePath = storage_path('app/public/' . $filename);

                if (!file_exists(dirname($storagePath))) {
                    mkdir(dirname($storagePath), 0755, true);
                }

                $info = getimagesize($originalPath);
                if ($info['mime'] == 'image/jpeg')
                    $image = imagecreatefromjpeg($originalPath);
                elseif ($info['mime'] == 'image/gif')
                    $image = imagecreatefromgif($originalPath);
                elseif ($info['mime'] == 'image/png')
                    $image = imagecreatefrompng($originalPath);
                else
                    $image = imagecreatefromstring(file_get_contents($originalPath));

                if ($image) {
                    imagejpeg($image, $storagePath, 60);
                    imagedestroy($image);
                    $imagePath = $filename;
                } else {
                    $imagePath = $this->new_customer_image->store('customers', 'public');
                }
            }

            $customer = Customer::create([
                'name' => $this->new_customer_name,
                'phone' => $this->new_customer_phone,
                'email' => $this->new_customer_email,
                'gender' => $this->new_customer_gender,
                'birthday' => $this->new_customer_birthday,
                'identity_id' => $this->new_customer_identity,
                'nationality' => $this->new_customer_nationality ?: 'Vietnam',
                'visa_number' => $this->new_customer_visa_number,
                'visa_expiry' => $this->new_customer_visa_expiry,
                'notes' => $this->new_customer_notes,
                'images' => $imagePath,
            ]);
            $customerId = $customer->id;
        } elseif ($customerId) {
            // Auto Sync existing customer
            $customer = Customer::find($customerId);
            if ($customer) {
                $customer->update([
                    'name' => $this->new_customer_name ?: $customer->name,
                    'phone' => $this->new_customer_phone ?: $customer->phone,
                    'email' => $this->new_customer_email ?: $customer->email,
                    'gender' => $this->new_customer_gender ?: $customer->gender,
                    'birthday' => $this->new_customer_birthday ?: $customer->birthday,
                    'identity_id' => $this->new_customer_identity ?: $customer->identity_id,
                    'nationality' => $this->new_customer_nationality ?: $customer->nationality,
                ]);
            }
        }

        $data = [
            'customer_id' => $customerId,
            'room_id' => $this->room_id,
            'price_type' => $this->price_type,
            'is_contract' => $this->is_contract,
            'unit_price' => $this->unit_price,
            'check_in' => $this->check_in,
            'check_out' => ($this->price_type === 'month' && empty($this->check_out)) ? null : $this->check_out,
            'price' => $this->price,
            'deposit' => $this->deposit,
            'deposit_2' => $this->deposit_2,
            'deposit_3' => $this->deposit_3,
            'status' => $this->status,
            'notes' => $this->notes,
            'additional_guests' => $this->additional_guests,
        ];

        if ($this->editingBookingId) {
            $booking = Booking::find($this->editingBookingId);
            $booking->update($data);
            $message = 'Cập nhật booking thành công.';
        } else {
            $booking = Booking::create($data);
            $message = 'Tạo booking mới thành công.';
        }

        // Sync Services
        $syncData = [];
        foreach ($this->selected_services as $serviceId => $item) {
            if (!empty($item['selected'])) {
                $service = Service::find($serviceId);
                if ($service) {
                    $pivot = [
                        'unit_price' => $service->unit_price,
                        'note' => $item['note'] ?? null,
                    ];

                    if ($service->type === 'meter') {
                        $pivot['start_index'] = $item['start_index'] ?? 0;
                        $pivot['end_index'] = $item['end_index'] ?? 0;
                        $pivot['usage'] = max(0, ($pivot['end_index'] - $pivot['start_index']));
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

        // Usage logs for NEW bookings only (Persistent logs are handled immediately)
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

        $this->dispatch('toast', message: $message, type: 'success');

        if (!$this->editingBookingId) {
            $this->showModal = false;
            $this->reset(['customer_id', 'new_customer_name', 'new_customer_phone', 'new_customer_email', 'new_customer_identity', 'new_customer_visa_number', 'new_customer_visa_expiry', 'new_customer_notes', 'new_customer_image', 'room_id', 'price_type', 'unit_price', 'check_in', 'check_out', 'price', 'deposit', 'deposit_2', 'deposit_3', 'status', 'notes', 'editingBookingId', 'selected_services', 'usage_logs']);
        } else {
            // Re-load to ensure everything is fresh
            $this->edit($this->editingBookingId);
        }
    }

    public function delete($id)
    {
        Booking::find($id)->delete();
        $this->dispatch('toast', message: 'Xóa booking thành công.', type: 'success');
    }

    // Filters
    public $filterStatus = '';
    public $filterType = '';
    public $filterArea = '';
    public $search = '';

    public function render()
    {
        $query = Booking::with(['customer', 'room.area'])->latest();

        if ($this->filterArea) {
            $query->whereHas('room', function ($q) {
                $q->where('area_id', $this->filterArea);
            });
        } elseif (session('admin_selected_area_id')) {
            $query->whereHas('room', function ($q) {
                $q->where('area_id', session('admin_selected_area_id'));
            });
        }

        // Apply filters
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterType) {
            $query->where('price_type', $this->filterType);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('customer', function ($subQ) {
                    $subQ->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                })->orWhereHas('room', function ($subQ) {
                    $subQ->where('code', 'like', '%' . $this->search . '%');
                })->orWhere('id', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.admin.bookings.index', [
            'bookings' => $query->paginate(10),
            'customers' => Customer::orderBy('name')->get(),
            'rooms' => Room::with('area')->orderBy('code')->get(),
            'all_services' => Service::where('is_active', true)->orderBy('name')->get(),
        ])->layout('components.layouts.admin');
    }
}
