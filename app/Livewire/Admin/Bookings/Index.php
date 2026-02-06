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
    public $new_customer_nationality;
    public $new_customer_visa_number;
    public $new_customer_visa_expiry;
    public $new_customer_notes;
    public $new_customer_image; // for file upload

    public $room_id;
    public $price_type = 'day'; // day, hour, month
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
    public $invoice_period;
    public $invoice_data = [];

    // Manual Fee Input
    public $manual_fee_amount;
    public $manual_fee_notes;
    public $manual_fee_billing_date;
    public $manual_fee_date;

    protected $listeners = ['area-selected' => '$refresh'];

    public function rules()
    {
        $rules = [
            'room_id' => 'required|exists:rooms,id',
            'price_type' => 'required|in:day,month',
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
            $rules['new_customer_visa_number'] = 'nullable|string|max:255';
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
        $this->updatePricing();
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
            // $this->price = $this->unit_price; // Removed auto-calc per user request
        } elseif ($this->price_type === 'day') {
            $this->unit_price = $room->price_day ?? 0;
            // $this->price = $this->unit_price; // Removed auto-calc per user request
        } elseif ($this->price_type === 'month') {
            // For month, we default to day price as unit, but total is manual
            $this->unit_price = $room->price_day ?? 0;
            // $this->price = $this->unit_price * 30; // Removed auto-calc per user request
        }
    }

    public function setTab($tab)
    {
        $this->activeModalTab = $tab;
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['customer_id', 'new_customer_name', 'new_customer_phone', 'new_customer_email', 'new_customer_identity', 'new_customer_visa_number', 'new_customer_visa_expiry', 'new_customer_notes', 'new_customer_image', 'room_id', 'price_type', 'unit_price', 'check_in', 'check_out', 'price', 'deposit', 'deposit_2', 'deposit_3', 'status', 'notes', 'editingBookingId', 'selected_services', 'usage_logs']);
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
        $this->activeTab = 'existing'; // Always default to existing for edit

        $this->room_id = $booking->room_id;
        $this->price_type = ($booking->price_type === 'month') ? 'month' : 'day'; // Default to day, map legacy 'hour' to day
        $this->unit_price = $booking->unit_price ?? 0;
        $this->check_in = $booking->check_in ? $booking->check_in->format('Y-m-d\TH:i') : null; // Ensure Y-m-d\TH:i for datetime-local
        $this->check_out = $booking->check_out ? $booking->check_out->format('Y-m-d\TH:i') : null;

        // Format money fields for display
        $this->price = number_format($booking->price, 0, ',', '.');
        $this->deposit = $booking->deposit ? number_format($booking->deposit, 0, ',', '.') : 0;
        $this->deposit_2 = $booking->deposit_2 ? number_format($booking->deposit_2, 0, ',', '.') : 0;
        $this->deposit_3 = $booking->deposit_3 ? number_format($booking->deposit_3, 0, ',', '.') : 0;

        $this->status = $booking->status;
        $this->notes = $booking->notes;

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

        // Load Usage Logs
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
        $this->activeModalTab = 'info';
        $this->showModal = true;
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

        // Gather all logs for this period
        $periodLogs = collect($this->usage_logs)->filter(function ($log) use ($period) {
            return \Carbon\Carbon::parse($log['billing_date'])->format('m/Y') === $period;
        });

        $this->invoice_data = [
            'period' => $period,
            'logs' => $periodLogs->values()->toArray(),
            'room_price' => $this->price ?? 0,
            'total' => $periodLogs->sum('total_amount') + ($this->price ?? 0),
            'booking' => [
                'customer_name' => $this->customer_name,
                'customer_phone' => $this->customer_phone,
                'room_code' => $this->selectedRoom?->code ?? '',
                'check_in' => $this->check_in,
            ]
        ];

        $this->showInvoiceModal = true;
    }

    public function closeInvoiceModal()
    {
        $this->showInvoiceModal = false;
        $this->invoice_data = [];
    }

    public function initServiceInput($serviceId)
    {
        $service = Service::find($serviceId);
        if (!$service)
            return;

        // Find last index from usage logs if it exists
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

    public function save()
    {
        // Sanitize money fields (remove dots)
        $this->price = str_replace('.', '', $this->price);
        $this->deposit = str_replace('.', '', $this->deposit);
        $this->deposit_2 = str_replace('.', '', $this->deposit_2);
        $this->deposit_3 = str_replace('.', '', $this->deposit_3);
        $this->unit_price = str_replace('.', '', $this->unit_price);

        $this->validate();

        $customerId = $this->customer_id;

        // Create new customer if tab is new
        if ($this->activeTab === 'new') {
            $imagePath = null;
            if ($this->new_customer_image) {
                // Compress Image Logic
                $originalPath = $this->new_customer_image->getRealPath();
                $filename = 'customers/' . uniqid() . '.jpg';
                $storagePath = storage_path('app/public/' . $filename);

                // Ensure directory exists
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

                // Save with 60% quality
                if ($image) {
                    imagejpeg($image, $storagePath, 60);
                    imagedestroy($image);
                    $imagePath = $filename;
                } else {
                    // Fallback if compression fails
                    $imagePath = $this->new_customer_image->store('customers', 'public');
                }
            }

            $customer = Customer::create([
                'name' => $this->new_customer_name,
                'phone' => $this->new_customer_phone,
                'email' => $this->new_customer_email,
                'identity_id' => $this->new_customer_identity,
                'nationality' => $this->new_customer_nationality,
                'visa_number' => $this->new_customer_visa_number,
                'visa_expiry' => $this->new_customer_visa_expiry,
                'notes' => $this->new_customer_notes,
                'images' => $imagePath,
            ]);
            $customerId = $customer->id;
        }

        $data = [
            'customer_id' => $customerId,
            'room_id' => $this->room_id,
            'price_type' => $this->price_type,
            'unit_price' => $this->unit_price,
            'check_in' => $this->check_in,
            'check_out' => ($this->price_type === 'month' && empty($this->check_out)) ? null : $this->check_out,
            'price' => $this->price,
            'deposit' => $this->deposit,
            'deposit_2' => $this->deposit_2,
            'deposit_3' => $this->deposit_3,
            'status' => $this->status,
            'notes' => $this->notes,
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

        session()->flash('success', $message);

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
        session()->flash('success', 'Xóa booking thành công.');
    }

    // Filters
    public $filterStatus = '';
    public $filterType = '';
    public $search = '';

    public function render()
    {
        $query = Booking::with(['customer', 'room.area'])->latest();

        if (session('admin_selected_area_id')) {
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
