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
    public $customer_name;
    public $customer_phone;
    public $customer_email;
    public $customer_identity;
    public $customer_gender;
    public $customer_nationality;
    public $customer_birthday;
    public $customer_visa_number;
    public $customer_visa_expiry;

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

    public $additional_guests = []; // Each guest: {name, identity}

    public function addGuest()
    {
        $this->additional_guests[] = ['name' => '', 'identity' => ''];
    }

    public function removeGuest($index)
    {
        $this->additional_guests = array_values(array_filter($this->additional_guests, fn($i) => $i !== $index, ARRAY_FILTER_USE_KEY));
    }

    public function updatedCustomerId($value)
    {
        if ($value) {
            $customer = Customer::find($value);
            if ($customer) {
                // Pre-fill check-in info from existing customer
                $this->customer_name = $customer->name;
                $this->customer_phone = $customer->phone;
                $this->customer_email = $customer->email;
                $this->customer_gender = $customer->gender;
                $this->customer_birthday = $customer->birthday ? $customer->birthday->format('Y-m-d') : null;
                $this->customer_identity = $customer->identity_id;
                $this->customer_nationality = $customer->nationality;
                $this->customer_visa_number = $customer->visa_number;
                $this->customer_visa_expiry = $customer->visa_expiry ? $customer->visa_expiry->format('Y-m-d') : null;
            }
        } else {
            $this->reset(['customer_name', 'customer_phone', 'customer_email', 'customer_gender', 'customer_birthday', 'customer_identity', 'customer_nationality', 'customer_visa_number', 'customer_visa_expiry']);
        }
    }

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
    public $source;

    public $manual_fee_amount;
    public $manual_fee_notes;
    public $manual_fee_date;
    public $countries = [];

    protected $listeners = ['area-selected' => '$refresh', 'refreshView' => '$refresh'];

    public function mount()
    {
        $this->startDate = now()->format('Y-m-d');
    }

    public $invoice_data = [];
    public $invoice_period = '';
    public $showInvoiceModal = false;

    public $showConfirmationModal = false;
    public $confirmation_data = [];

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
            'remaining' => $roomPrice - $totalDeposit,
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
                \Illuminate\Support\Facades\Mail::to($customerEmail)->send(new \App\Mail\InvoiceMail($booking, $logs));

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
                function ($attribute, $value, $fail) {
                    if ($this->price_type !== 'month' && $value && $this->check_in) {
                        try {
                            $start = \Carbon\Carbon::parse($this->check_in);
                            $end = \Carbon\Carbon::parse($value);
                            if ($end->lte($start)) {
                                $fail('Ngày trả phải sau ngày nhận.');
                            }
                        } catch (\Exception $e) {}
                    }
                },
            ],
            'price' => 'required',
            'deposit' => 'nullable',
            'deposit_2' => 'nullable',
            'deposit_3' => 'nullable',
            'status' => 'required|in:pending,checked_in,checked_out,cancelled',
            'source' => 'required|string',
            'notes' => 'nullable|string',
            'customer_id' => $this->activeTab === 'existing' ? 'required' : 'nullable',
            'new_customer_name' => $this->activeTab === 'new' ? 'required|string|max:255' : 'nullable',
            'new_customer_phone' => 'nullable',
            'new_customer_identity' => 'nullable',
            'new_customer_nationality' => 'nullable',
        ];
    }

    public function updatedRoomId()
    {
        $this->updatePricing();
    }

    public function updatedUnitPrice()
    {
        $this->calculateTotal();
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
                // Contract (formerly Month)
                // Calculate total based on daily rate = monthly price / 30
                $nights = $start->diffInDays($end);
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
        $room = \App\Models\Room::find($this->room_id);
        if (!$room)
            return;

        $priceVal = $room->price_day ?? 0;
        $this->unit_price = number_format($priceVal, 0, ',', '.');
    }

    public function setTab($tab)
    {
        $this->activeModalTab = $tab;
    }

    public function createBooking($roomId, $date)
    {
        \Illuminate\Support\Facades\Log::info('BookingCalendar CreateBooking Triggered', ['room_id' => $roomId, 'date' => $date]);
        $this->resetValidation();
        $this->reset(['customer_id', 'new_customer_name', 'new_customer_phone', 'new_customer_email', 'new_customer_identity', 'new_customer_nationality', 'new_customer_visa_number', 'new_customer_visa_expiry', 'new_customer_notes', 'new_customer_image', 'customer_identity', 'customer_nationality', 'customer_visa_number', 'customer_visa_expiry', 'room_id', 'price_type', 'unit_price', 'check_in', 'check_out', 'price', 'deposit', 'deposit_2', 'deposit_3', 'status', 'source', 'notes', 'editingBookingId', 'selected_services', 'usage_logs']);

        $this->room_id = $roomId;
        $this->check_in = $date;
        $this->check_out = \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d');
        $this->price_type = 'day';
        $this->status = 'pending';
        $this->source = 'Hotline';
        $this->activeTab = 'existing';
        $this->manual_fee_date = date('Y-m-d');
        $this->activeModalTab = 'overview';
        $this->showModal = true;

        $this->updatePricing();
        $this->calculateTotal();
    }

    public function editBooking($id)
    {
        \Illuminate\Support\Facades\Log::info('BookingCalendar EditBooking Triggered', ['id' => $id]);
        $this->resetValidation();
        $booking = \App\Models\Booking::with(['services', 'usageLogs.service', 'room', 'customer'])->findOrFail($id);
        $this->editingBookingId = $id;

        $this->customer_id = $booking->customer_id;
        $this->activeTab = 'existing';
        $this->room_id = $booking->room_id;
        $this->price_type = ($booking->price_type === 'month') ? 'month' : 'day';
        $this->unit_price = number_format($booking->unit_price ?? 0, 0, ',', '.');
        $this->check_in = $booking->check_in ? $booking->check_in->format('Y-m-d') : null;
        $this->check_out = $booking->check_out ? $booking->check_out->format('Y-m-d') : null;
        $this->price = number_format($booking->price, 0, ',', '.');
        $this->deposit = $booking->deposit ? number_format($booking->deposit, 0, ',', '.') : 0;
        $this->deposit_2 = $booking->deposit_2 ? number_format($booking->deposit_2, 0, ',', '.') : 0;
        $this->deposit_3 = $booking->deposit_3 ? number_format($booking->deposit_3, 0, ',', '.') : 0;
        $this->status = $booking->status;
        $this->source = $booking->source ?: 'Hotline';
        $this->notes = $booking->notes;

        // Load Customer Check-in Info
        if ($booking->customer) {
            $this->customer_name = $booking->customer->name;
            $this->customer_phone = $booking->customer->phone;
            $this->customer_email = $booking->customer->email;
            $this->customer_gender = $booking->customer->gender;
            $this->customer_birthday = $booking->customer->birthday ? $booking->customer->birthday->format('Y-m-d') : null;
            $this->customer_identity = $booking->customer->identity_id;
            $this->customer_nationality = $booking->customer->nationality;
            $this->customer_visa_number = $booking->customer->visa_number;
            $this->customer_visa_expiry = $booking->customer->visa_expiry ? $booking->customer->visa_expiry->format('Y-m-d') : null;
        }

        $this->additional_guests = $booking->additional_guests ?? [];

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

        // Lấy đơn giá từ input, nếu không có hoặc bằng 0 thì lấy đơn giá mặc định của dịch vụ
        $priceStr = $input['unit_price'] ?? '';
        if (empty($priceStr) || $priceStr == '0') {
            $price = (float)$service->unit_price;
        } else {
            $price = (float) str_replace([',', '.'], '', $priceStr);
        }
        $total = 0;
        $qty = 0;
        if ($service->type === 'meter') {
            $start = (float) str_replace([',', '.'], '', $input['start_index'] ?? '0');
            $end = (float) str_replace([',', '.'], '', $input['end_index'] ?? '0');
            $qty = max(0, $end - $start);
            $total = $qty * $price;
        } else {
            $qty = (float) ($input['quantity'] ?? 1);
            $total = $qty * $price;
        }

        $logData = [
            'service_id' => $serviceId,
            'service_name' => $service->name,
            'type' => $service->type,
            'billing_unit' => $service->unit_name,
            'start_index' => $input['start_index'] ?? 0,
            'end_index' => $input['end_index'] ?? 0,
            'quantity' => $qty,
            'unit_price' => $price,
            'total_amount' => $total,
            'billing_date' => $input['billing_date'] ?? date('Y-m-d'),
            'notes' => $input['notes'] ?? '',
        ];

        if ($this->editingBookingId) {
            $booking = \App\Models\Booking::find($this->editingBookingId);
            if ($booking) {
                $newDbLog = $booking->usageLogs()->create([
                    'service_id' => $logData['service_id'],
                    'type' => $logData['type'],
                    'billing_unit' => $logData['billing_unit'],
                    'start_index' => $logData['start_index'] ?: 0,
                    'end_index' => $logData['end_index'] ?: 0,
                    'quantity' => $logData['quantity'],
                    'unit_price' => $logData['unit_price'],
                    'total_amount' => $logData['total_amount'],
                    'billing_date' => $logData['billing_date'],
                    'notes' => $logData['notes'],
                ]);

                // ĐỒNG BỘ: Thêm vào danh sách dịch vụ đang sử dụng (Pivot)
                if ($logData['service_id']) {
                    $booking->services()->syncWithoutDetaching([
                        $logData['service_id'] => [
                            'unit_price' => $logData['unit_price'],
                            'start_index' => $logData['end_index'] ?: 0, // Số cuối kỳ này là số đầu kỳ sau
                            'end_index' => 0,
                            'usage' => 0,
                            'quantity' => 1,
                            'total_amount' => 0,
                        ]
                    ]);
                }

                $this->dispatch('toast', message: 'Đã chốt dịch vụ: ' . ($logData['service_name']), type: 'success');

                $logData['id'] = $newDbLog->id;
            }
        }
        $this->usage_logs[] = $logData;
        if ($service->type === 'meter') {
            $this->service_inputs[$serviceId]['start_index'] = $input['end_index'];
            $this->service_inputs[$serviceId]['end_index'] = '';
        }
    }

    public function addAllServiceLogs()
    {
        foreach ($this->service_inputs as $serviceId => $input) {
            // Chốt nếu có số cuối (điện nước) HOẶC là dịch vụ cố định đang được chọn
            $isMeter = !empty($input['end_index']);
            $isSelected = !empty($this->selected_services[$serviceId]['selected']);
            
            if ($isMeter || $isSelected) {
                $this->addServiceLog($serviceId);
            }
        }
        $this->dispatch('toast', message: 'Đã cập nhật chỉ số dịch vụ.', type: 'success');
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

    public function saveBooking()
    {
        // Ensure source is set if empty
        if (empty($this->source)) {
            $this->source = 'Hotline';
        }

        \Illuminate\Support\Facades\Log::info('BookingCalendar Save Started', [
            'editingBookingId' => $this->editingBookingId,
            'status' => $this->status,
            'customer_id' => $this->customer_id,
            'source' => $this->source,
            'activeTab' => $this->activeTab
        ]);

        $cleanPrice = str_replace('.', '', $this->price);
        $cleanDeposit = str_replace('.', '', $this->deposit);
        $cleanDeposit2 = str_replace('.', '', $this->deposit_2);
        $cleanDeposit3 = str_replace('.', '', $this->deposit_3);
        $cleanUnitPrice = str_replace('.', '', $this->unit_price);

        // Convert empty strings to null for decimal columns
        $cleanPrice = $cleanPrice === '' ? null : $cleanPrice;
        $cleanDeposit = $cleanDeposit === '' ? null : $cleanDeposit;
        $cleanDeposit2 = $cleanDeposit2 === '' ? null : $cleanDeposit2;
        $cleanDeposit3 = $cleanDeposit3 === '' ? null : $cleanDeposit3;
        $cleanUnitPrice = $cleanUnitPrice === '' ? null : $cleanUnitPrice;

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

        try {
            $this->validate();
            \Illuminate\Support\Facades\Log::info('BookingCalendar Validation Passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('BookingCalendar Validation Failed', ['errors' => $e->errors()]);
            $this->dispatch('toast', message: 'Vui lòng kiểm tra lại thông tin nhập liệu.', type: 'error');
            throw $e;
        }

        $customerId = $this->customer_id;

        if ($this->activeTab === 'new') {
            // Tạo khách hàng mới - chỉ lưu thông tin cơ bản
            $newCustomerData = [
                'name' => $this->new_customer_name,
                'phone' => $this->new_customer_phone,
                'email' => $this->new_customer_email,
                'gender' => $this->new_customer_gender,
                'birthday' => $this->new_customer_birthday,
                'identity_id' => $this->new_customer_identity,
            ];

            // Chỉ thêm thông tin check-in nếu trạng thái là 'checked_in'
            if ($this->status === 'checked_in') {
                $identityValue = $this->customer_identity ?: $this->new_customer_identity;
                $newCustomerData['identity_id'] = $identityValue;
                $newCustomerData['nationality'] = $this->customer_nationality ?: 'Vietnam';
                $newCustomerData['visa_number'] = $identityValue; // Lưu cùng giá trị với identity_id
                $newCustomerData['visa_expiry'] = $this->customer_visa_expiry;
            }
            $customer = \App\Models\Customer::create($newCustomerData);
            $customerId = $customer->id;
        } elseif ($customerId) {
            // ĐỒNG BỘ: Luôn cập nhật thông tin khách hàng nếu có thay đổi từ form
            $customer = \App\Models\Customer::find($customerId);
            if ($customer) {
                $customerDataToUpdate = [
                    'name' => $this->customer_name ?: $customer->name,
                    'phone' => $this->customer_phone ?: $customer->phone,
                    'email' => $this->customer_email ?: $customer->email,
                    'gender' => $this->customer_gender ?: $customer->gender,
                    'birthday' => $this->customer_birthday ?: $customer->birthday,
                    'identity_id' => $this->customer_identity ?: $customer->identity_id,
                    'nationality' => $this->customer_nationality ?: $customer->nationality,
                    'visa_number' => $this->customer_identity ?: $customer->visa_number,
                    'visa_expiry' => $this->customer_visa_expiry ?: $customer->visa_expiry,
                ];
                $customer->update($customerDataToUpdate);
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
            'source' => $this->source,
            'notes' => $this->notes,
            'additional_guests' => $this->additional_guests,
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

        $this->dispatch('toast', message: $this->editingBookingId ? 'Cập nhật đặt phòng thành công!' : 'Tạo đặt phòng thành công!', type: 'success');

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
                $visualStart = (float) $diffStartDays;
                $diffEndDays = $windowStart->diffInDays($checkOutDate, false);
                $visualDays = $diffEndDays - $diffStartDays;

                if ($visualStart < 0) {
                    $loss = abs($visualStart);
                    $visualStart = 0;
                    $visualDays = max(0, $visualDays - $loss);
                }
                
                // Ensure at least some width if it starts/ends within window
                if ($visualDays <= 0 && $diffEndDays >= 0) {
                    $visualDays = 1;
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

    public function deleteBooking($id)
    {
        \Illuminate\Support\Facades\Log::info('BookingCalendar DeleteBooking Triggered', ['id' => $id]);
        try {
            \App\Models\Booking::find($id)?->delete();
            $this->dispatch('toast', message: 'Đã xóa đặt phòng thành công.', type: 'success');
            $this->showModal = false;
            $this->editingBookingId = null;
            $this->dispatch('refreshView');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('BookingCalendar DeleteBooking Failed', ['error' => $e->getMessage()]);
            $this->dispatch('toast', message: 'Lỗi khi xóa đặt phòng.', type: 'error');
        }
    }

    public function render()
    {
        // Tính toán các khoản tiền cho Bảng Tổng Chi Phí
        $logTotal = collect($this->usage_logs)->sum('total_amount');
        $basePrice = (float) str_replace(['.', ','], '', $this->price ?: 0);
        
        $pendingServiceTotal = 0;
        $allServices = \App\Models\Service::where('is_active', true)->get();
        foreach($allServices as $svc) {
            if(!empty($this->selected_services[$svc->id]['selected']) && isset($this->service_inputs[$svc->id])) {
                $inp = $this->service_inputs[$svc->id];
                $up = (float)str_replace(['.',','],'', (string)($inp['unit_price'] ?? '0'));
                if($svc->type === 'meter') {
                    $pendingServiceTotal += max(0, ((float)($inp['end_index'] ?? 0) - (float)($inp['start_index'] ?? 0))) * $up;
                } else {
                    $pendingServiceTotal += ((float)($inp['quantity'] ?? 1)) * $up;
                }
            }
        }
        
        $grandTotal = $basePrice + $logTotal + $pendingServiceTotal;

        return view('livewire.admin.booking-calendar', [
            'areas' => \App\Models\Area::all(),
            'roomsData' => $this->rooms,
            'days' => $this->daysInMonth,
            'customers' => \App\Models\Customer::orderBy('name')->get(),
            'all_services' => $allServices,
            'all_rooms' => \App\Models\Room::with('area')->orderBy('code')->get(),
            'logTotal' => $logTotal,
            'basePrice' => $basePrice,
            'pendingServiceTotal' => $pendingServiceTotal,
            'grandTotal' => $grandTotal,
        ])->layout('components.layouts.admin');
    }
}
