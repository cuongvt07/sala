<?php

namespace App\Livewire\Admin;

use App\Mail\InvoiceMail;
use App\Models\Booking;
use App\Models\BookingUsageLog;
use App\Models\Customer;
use App\Models\Room;
use App\Models\Service;
use App\Models\Transaction;
use App\Services\BookingPurger;
use App\Traits\HasCountryData;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Form đặt phòng DÙNG CHUNG cho Lịch đặt phòng, Quản lý đặt phòng và Dashboard.
 * Mở bằng sự kiện:  $dispatch('open-booking', { id: 5 })  hoặc  $dispatch('new-booking', { roomId: 3, date: '2026-11-20' })
 * Sau khi lưu/xoá sẽ phát sự kiện 'booking-saved' để trang cha tự làm mới.
 */
class BookingForm extends Component
{
    use WithFileUploads;
    use HasCountryData;

    public bool $show = false;
    public $bookingId = null;
    public string $tab = 'info'; // info | payment

    // ---- Đặt phòng ----
    public $room_id;
    public $price_type = 'day'; // day | month (hợp đồng)
    public $unit_price = '';
    public $price = '';
    public $check_in;
    public $check_out;
    public $status = 'pending';
    public $source = 'Hotline';
    public $notes = '';
    public $additional_guests = [];

    // ---- Khách hàng (1 bộ trường duy nhất) ----
    public $customer_id = null;
    public $customer_search = '';
    public $c_name;
    public $c_phone;
    public $c_email;
    public $c_identity;
    public $c_gender;
    public $c_birthday;
    public $c_nationality;
    public $c_visa_expiry;
    public $c_image;

    // ---- Dịch vụ ----
    public $meter_inputs = [];   // [service_id => ['start' => .., 'end' => ..]]
    public $fee_service_id = ''; // '' = phụ thu tự nhập
    public $fee_qty = 1;
    public $fee_amount = '';
    public $fee_note = '';

    // ---- Ghi nhận thu/chi ----
    public bool $showPayForm = false;
    public $payingDue = null;    // Y-m-d khi thu tiền 1 kỳ hợp đồng
    public $pay_type = 'income';
    public $pay_category = 'Tiền cọc';
    public $pay_amount = '';
    public $pay_method = 'cash';
    public $pay_date;
    public $pay_note = '';
    public $pay_usd = '';
    public $pay_rate = '25.400';

    // ---- In ấn ----
    public $printMode = null;    // confirmation | invoice
    public $printData = [];

    public function mount()
    {
        // Deep-link: /admin/bookings?open=ID (&bill=1 để mở thẳng tab thanh toán)
        $openId = request()->query('open');
        if ($openId) {
            $this->openBooking((int) $openId, request()->query('bill') ? 'payment' : 'info');
        }
    }

    // =====================================================================
    // MỞ / ĐÓNG
    // =====================================================================

    #[On('open-booking')]
    public function openBooking($id, $tab = 'info')
    {
        $booking = Booking::with(['room', 'customer'])->find($id);
        if (!$booking) {
            $this->dispatch('toast', message: 'Không tìm thấy đặt phòng (có thể đã trả phòng và được xoá).', type: 'error');
            return;
        }
        if ($booking->room && !auth()->user()->canAccessArea($booking->room->area_id)) {
            $this->dispatch('toast', message: 'Bạn không có quyền xem booking của toà nhà này.', type: 'error');
            return;
        }

        $this->resetForm();
        $this->bookingId = $booking->id;
        $this->room_id = $booking->room_id;
        $this->price_type = $booking->isContract() ? 'month' : 'day';
        $this->unit_price = $this->fmt($booking->unit_price);
        $this->price = $this->fmt($booking->price);
        $this->check_in = $booking->check_in?->format('Y-m-d');
        $this->check_out = $booking->check_out?->format('Y-m-d');
        $this->status = $booking->status;
        $this->source = $booking->source ?: 'Hotline';
        $this->notes = $booking->notes;
        $this->additional_guests = collect($booking->additional_guests ?? [])->map(function ($g) {
            return [
                'name' => $g['name'] ?? '',
                'phone' => $g['phone'] ?? '',
                'identity' => $g['identity'] ?? ($g['identity_id'] ?? ''),
                'nationality' => $g['nationality'] ?? '',
                'birthday' => $this->displayDate($g['birthday'] ?? null),
                'visa_expiry' => $this->displayDate($g['visa_expiry'] ?? null),
            ];
        })->values()->toArray();

        if ($booking->customer) {
            $this->fillCustomer($booking->customer);
        }

        $this->tab = in_array($tab, ['info', 'payment']) ? $tab : 'info';
        $this->show = true;
    }

    #[On('new-booking')]
    public function newBooking($roomId = null, $date = null)
    {
        $this->resetForm();
        $this->room_id = $roomId ?: null;
        $this->check_in = $date ?: now()->format('Y-m-d');
        $this->check_out = Carbon::parse($this->check_in)->addDay()->format('Y-m-d');
        $this->applyRoomPrice();
        $this->recalcPrice();
        $this->show = true;
    }

    public function close()
    {
        $this->show = false;
        $this->printMode = null;
    }

    protected function resetForm(): void
    {
        $this->resetValidation();
        $this->reset([
            'bookingId', 'tab', 'room_id', 'price_type', 'unit_price', 'price', 'check_in', 'check_out',
            'status', 'source', 'notes', 'additional_guests',
            'customer_id', 'customer_search', 'c_name', 'c_phone', 'c_email', 'c_identity', 'c_gender',
            'c_birthday', 'c_nationality', 'c_visa_expiry', 'c_image',
            'meter_inputs', 'fee_service_id', 'fee_qty', 'fee_amount', 'fee_note',
            'showPayForm', 'payingDue', 'pay_type', 'pay_category', 'pay_amount', 'pay_method', 'pay_note', 'pay_usd',
            'printMode', 'printData',
        ]);
        $this->pay_date = now()->format('Y-m-d');
    }

    // =====================================================================
    // GIÁ
    // =====================================================================

    public function updatedRoomId()
    {
        $this->applyRoomPrice();
        $this->recalcPrice();
    }

    public function updatedPriceType()
    {
        if ($this->price_type === 'month') {
            $this->check_out = null; // hợp đồng: không bắt buộc ngày trả
        } elseif (!$this->check_out && $this->check_in) {
            $this->check_out = Carbon::parse($this->check_in)->addDay()->format('Y-m-d');
        }
        $this->applyRoomPrice();
        $this->recalcPrice();
    }

    public function updatedUnitPrice()
    {
        $this->recalcPrice();
    }

    public function updatedCheckIn()
    {
        $this->recalcPrice();
    }

    public function updatedCheckOut()
    {
        $this->recalcPrice();
    }

    protected function applyRoomPrice(): void
    {
        $room = $this->room_id ? Room::find($this->room_id) : null;
        if (!$room) {
            return;
        }
        $value = $this->price_type === 'month'
            ? ($room->price_month ?: $room->price_day)
            : $room->price_day;
        $this->unit_price = $this->fmt($value ?? 0);
    }

    /** Thuê ngày: tổng = số đêm x đơn giá. Hợp đồng: tiền mỗi kỳ = đơn giá tháng. */
    protected function recalcPrice(): void
    {
        $unit = $this->money($this->unit_price);

        if ($this->price_type === 'month') {
            $this->price = $this->fmt($unit);
            return;
        }

        $nights = $this->nights();
        if ($nights > 0) {
            $this->price = $this->fmt($nights * $unit);
        }
    }

    protected function nights(): int
    {
        try {
            if (!$this->check_in || !$this->check_out) {
                return 0;
            }
            $in = Carbon::parse($this->check_in)->startOfDay();
            $out = Carbon::parse($this->check_out)->startOfDay();
            return $out->gt($in) ? (int) $in->diffInDays($out) : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    // =====================================================================
    // KHÁCH HÀNG
    // =====================================================================

    public function selectCustomer($id)
    {
        $customer = Customer::find($id);
        if ($customer) {
            $this->fillCustomer($customer);
        }
        $this->customer_search = '';
    }

    public function clearCustomer()
    {
        $this->reset(['customer_id', 'c_name', 'c_phone', 'c_email', 'c_identity', 'c_gender', 'c_birthday', 'c_nationality', 'c_visa_expiry', 'c_image']);
    }

    protected function fillCustomer(Customer $customer): void
    {
        $this->customer_id = $customer->id;
        $this->c_name = $customer->name;
        $this->c_phone = $customer->phone;
        $this->c_email = $customer->email;
        $this->c_identity = $customer->identity_id;
        $this->c_gender = $customer->gender;
        $this->c_birthday = $customer->birthday?->format('d/m/Y');
        $this->c_nationality = $customer->nationality;
        $this->c_visa_expiry = $customer->visa_expiry?->format('d/m/Y');
    }

    public function updatedCNationality($value)
    {
        $this->c_nationality = $this->handleNationalityUpdate($value);
    }

    public function addGuest()
    {
        $this->additional_guests[] = ['name' => '', 'phone' => '', 'identity' => '', 'nationality' => '', 'birthday' => '', 'visa_expiry' => ''];
    }

    public function removeGuest($index)
    {
        unset($this->additional_guests[$index]);
        $this->additional_guests = array_values($this->additional_guests);
    }

    // =====================================================================
    // LƯU / XOÁ
    // =====================================================================

    protected function rules(): array
    {
        $allowedStatus = $this->bookingId ? 'pending,checked_in,checked_out,cancelled' : 'pending,checked_in';

        return [
            'room_id' => 'required|exists:rooms,id',
            'price_type' => 'required|in:day,month',
            'unit_price' => 'required',
            'check_in' => 'required|date',
            'check_out' => [$this->price_type === 'month' ? 'nullable' : 'required', 'date', 'after:check_in'],
            'status' => 'required|in:' . $allowedStatus,
            'source' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'c_name' => 'required|string|max:255',
            'c_phone' => 'nullable|string|max:30',
            'c_email' => 'nullable|email|max:255',
            'c_identity' => $this->status === 'checked_in' ? 'required|string|max:255' : 'nullable|string|max:255',
            'c_nationality' => $this->status === 'checked_in' ? 'required|string|max:255' : 'nullable|string|max:255',
            'c_image' => 'nullable|image|max:10240',
        ];
    }

    protected function messages(): array
    {
        return [
            'room_id.required' => 'Chọn phòng.',
            'check_in.required' => 'Chọn ngày nhận phòng.',
            'check_out.required' => 'Chọn ngày trả phòng.',
            'check_out.after' => 'Ngày trả phải sau ngày nhận.',
            'c_name.required' => 'Nhập tên khách.',
            'c_identity.required' => 'Cần CCCD/Passport khi nhận phòng.',
            'c_nationality.required' => 'Cần quốc tịch khi nhận phòng.',
            'c_email.email' => 'Email không hợp lệ.',
        ];
    }

    public function save()
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->tab = 'info';
            $this->dispatch('toast', message: 'Vui lòng kiểm tra lại thông tin.', type: 'error');
            throw $e;
        }

        $room = Room::find($this->room_id);
        if ($room && !auth()->user()->canAccessArea($room->area_id)) {
            $this->dispatch('toast', message: 'Bạn không có quyền thao tác booking của toà nhà này.', type: 'error');
            return;
        }

        // Trả phòng / huỷ: không cần kiểm tra trùng phòng
        $leaving = in_array($this->status, ['checked_out', 'cancelled']);
        if (!$leaving && $this->roomHasConflict()) {
            $this->dispatch('toast', message: 'Phòng đã có khách trong khoảng thời gian này. Vui lòng chọn phòng/ngày khác.', type: 'error');
            return;
        }

        $booking = DB::transaction(function () {
            $customer = $this->saveCustomer();
            $unit = $this->money($this->unit_price);

            $data = [
                'customer_id' => $customer->id,
                'room_id' => $this->room_id,
                'price_type' => $this->price_type,
                'is_contract' => $this->price_type === 'month',
                'unit_price' => $unit,
                'price' => $this->price_type === 'month' ? $unit : $this->money($this->price),
                'check_in' => $this->check_in,
                'check_out' => $this->check_out ?: null,
                'status' => $this->status,
                'source' => $this->source ?: null,
                'notes' => $this->notes,
                'additional_guests' => collect($this->additional_guests)
                    ->filter(fn ($g) => trim($g['name'] ?? '') !== '')
                    ->map(fn ($g) => [
                        'name' => trim($g['name']),
                        'phone' => $g['phone'] ?? null,
                        'identity' => $g['identity'] ?? null,
                        'nationality' => $g['nationality'] ?? null,
                        'birthday' => $this->parseDate($g['birthday'] ?? null),
                        'visa_expiry' => $this->parseDate($g['visa_expiry'] ?? null),
                    ])->values()->toArray(),
            ];

            if ($this->bookingId) {
                $booking = Booking::findOrFail($this->bookingId);
                $booking->update($data);
            } else {
                $booking = Booking::create($data);
            }

            return $booking;
        });

        $this->dispatch('booking-saved');

        // Khách trả phòng / huỷ -> xoá dữ liệu khách & booking (sổ thu chi vẫn giữ)
        if ($leaving) {
            BookingPurger::purge($booking);
            $this->show = false;
            $this->dispatch('toast', message: $this->status === 'checked_out'
                ? 'Đã trả phòng. Thông tin khách đã được xoá khỏi hệ thống (sổ thu chi vẫn giữ).'
                : 'Đã huỷ đặt phòng và xoá thông tin khách.', type: 'success');
            return;
        }

        $isNew = !$this->bookingId;
        $this->openBooking($booking->id, $isNew ? 'payment' : $this->tab);
        $this->dispatch('toast', message: $isNew ? 'Đã tạo đặt phòng. Ghi nhận tiền cọc/thanh toán ở tab này nếu có.' : 'Đã lưu thay đổi.', type: 'success');
    }

    protected function saveCustomer(): Customer
    {
        $data = [
            'name' => trim($this->c_name),
            'phone' => $this->c_phone ?: null,
            'email' => $this->c_email ?: null,
            'identity_id' => $this->c_identity ?: null,
            'gender' => $this->c_gender ?: null,
            'birthday' => $this->parseDate($this->c_birthday),
            'nationality' => $this->c_nationality ?: null,
            'visa_number' => $this->c_identity ?: null,
            'visa_expiry' => $this->parseDate($this->c_visa_expiry),
        ];

        if ($this->c_image) {
            $data['images'] = $this->storeCompressedImage($this->c_image);
        }

        $customer = $this->customer_id ? Customer::find($this->customer_id) : null;
        if ($customer) {
            $customer->update($data);
        } else {
            $customer = Customer::create($data);
            $this->customer_id = $customer->id;
        }

        return $customer;
    }

    /** Nén ảnh giấy tờ về JPEG chất lượng 60 để tiết kiệm dung lượng host. */
    protected function storeCompressedImage($file): string
    {
        try {
            $src = $file->getRealPath();
            $image = @imagecreatefromstring(file_get_contents($src));
            if ($image) {
                $filename = 'customers/' . uniqid() . '.jpg';
                $path = storage_path('app/public/' . $filename);
                if (!is_dir(dirname($path))) {
                    mkdir(dirname($path), 0755, true);
                }
                imagejpeg($image, $path, 60);
                imagedestroy($image);
                return $filename;
            }
        } catch (\Throwable $e) {
            // fallback bên dưới
        }
        return $file->store('customers', 'public');
    }

    protected function roomHasConflict(): bool
    {
        if (!$this->room_id || !$this->check_in) {
            return false;
        }

        $start = Carbon::parse($this->check_in)->startOfDay();
        $end = $this->check_out ? Carbon::parse($this->check_out)->startOfDay() : Carbon::parse('2999-12-31');

        // Sửa booking mà không đổi phòng/ngày -> bỏ qua (tránh chặn sửa tên, ghi chú...)
        if ($this->bookingId) {
            $orig = Booking::find($this->bookingId);
            if ($orig
                && (int) $orig->room_id === (int) $this->room_id
                && $orig->check_in?->format('Y-m-d') === $start->format('Y-m-d')
                && $orig->check_out?->format('Y-m-d') === ($this->check_out ? $end->format('Y-m-d') : null)) {
                return false;
            }
        }

        return Booking::where('room_id', $this->room_id)
            ->when($this->bookingId, fn ($q) => $q->where('id', '!=', $this->bookingId))
            ->whereIn('status', Booking::ACTIVE_STATUSES)
            ->where('check_in', '<', $end)
            ->where(fn ($q) => $q->where('check_out', '>', $start)->orWhereNull('check_out'))
            ->exists();
    }

    public function delete()
    {
        $booking = $this->currentBooking();
        if (!$booking) {
            return;
        }
        BookingPurger::purge($booking);
        $this->show = false;
        $this->dispatch('booking-saved');
        $this->dispatch('toast', message: 'Đã xoá đặt phòng.', type: 'success');
    }

    protected function currentBooking(): ?Booking
    {
        if (!$this->bookingId) {
            return null;
        }
        $booking = Booking::with(['room', 'customer'])->find($this->bookingId);
        if (!$booking) {
            $this->show = false;
            $this->dispatch('toast', message: 'Đặt phòng không còn tồn tại.', type: 'error');
            return null;
        }
        if ($booking->room && !auth()->user()->canAccessArea($booking->room->area_id)) {
            $this->dispatch('toast', message: 'Bạn không có quyền với toà nhà này.', type: 'error');
            return null;
        }
        return $booking;
    }

    // =====================================================================
    // DỊCH VỤ (điện, nước, phụ thu)
    // =====================================================================

    public function saveMeter($serviceId)
    {
        $booking = $this->currentBooking();
        $service = Service::find($serviceId);
        if (!$booking || !$service) {
            return;
        }

        $input = $this->meter_inputs[$serviceId] ?? [];
        $startRaw = trim((string) ($input['start'] ?? ''));
        // Bỏ trống số đầu -> lấy số cuối lần chốt trước
        $start = $startRaw === '' ? ($this->lastMeterIndex($booking, $serviceId) ?? 0.0) : $this->meterNumber($startRaw);
        $end = $this->meterNumber($input['end'] ?? '');
        if (($input['end'] ?? '') === '' || $end < $start) {
            $this->dispatch('toast', message: 'Nhập số cuối (lớn hơn hoặc bằng số đầu).', type: 'error');
            return;
        }

        $usage = $end - $start;
        $booking->usageLogs()->create([
            'service_id' => $service->id,
            'type' => 'meter',
            'billing_unit' => $service->unit_name,
            'start_index' => $start,
            'end_index' => $end,
            'quantity' => $usage,
            'unit_price' => $service->unit_price,
            'total_amount' => round($usage * (float) $service->unit_price),
            'billing_date' => now()->toDateString(),
        ]);

        unset($this->meter_inputs[$serviceId]);
        $this->dispatch('toast', message: "Đã chốt {$service->name}: {$usage} {$service->unit_name}.", type: 'success');
    }

    public function addFee()
    {
        $booking = $this->currentBooking();
        if (!$booking) {
            return;
        }

        $service = $this->fee_service_id ? Service::find($this->fee_service_id) : null;
        $qty = max(1, (float) $this->fee_qty);
        $unit = $service ? (float) $service->unit_price : $this->money($this->fee_amount);
        if ($this->fee_amount !== '' && $service) {
            $unit = $this->money($this->fee_amount); // cho phép sửa đơn giá
        }
        if ($unit <= 0) {
            $this->dispatch('toast', message: 'Nhập số tiền.', type: 'error');
            return;
        }

        $booking->usageLogs()->create([
            'service_id' => $service?->id,
            'type' => $service ? 'fixed' : 'manual',
            'billing_unit' => $service?->unit_name ?? 'Lần',
            'quantity' => $qty,
            'unit_price' => $unit,
            'total_amount' => round($qty * $unit),
            'billing_date' => now()->toDateString(),
            'notes' => $this->fee_note ?: null,
        ]);

        $this->reset(['fee_service_id', 'fee_qty', 'fee_amount', 'fee_note']);
        $this->dispatch('toast', message: 'Đã thêm khoản phí.', type: 'success');
    }

    public function removeLog($logId)
    {
        $booking = $this->currentBooking();
        if (!$booking) {
            return;
        }
        $log = BookingUsageLog::where('booking_id', $booking->id)->find($logId);
        if (!$log) {
            return;
        }
        if ($log->transaction_id) {
            $this->dispatch('toast', message: 'Khoản này đã được thu tiền, không thể xoá. Huỷ phiếu thu trước.', type: 'error');
            return;
        }
        $log->delete();
    }

    protected function lastMeterIndex(Booking $booking, $serviceId): ?float
    {
        $last = $booking->usageLogs()->where('service_id', $serviceId)->where('type', 'meter')->latest('id')->first();
        return $last ? (float) $last->end_index : null;
    }

    // =====================================================================
    // THU / CHI
    // =====================================================================

    public function openPay($due = null, $category = null, $type = 'income')
    {
        $booking = $this->currentBooking();
        if (!$booking) {
            return;
        }

        $this->resetValidation();
        $this->payingDue = $due;
        $this->pay_type = $type === 'expense' ? 'expense' : 'income';
        $this->pay_method = 'cash';
        $this->pay_date = now()->format('Y-m-d');
        $this->pay_note = '';
        $this->pay_usd = '';

        $unbilled = $booking->usageLogs()->whereNull('transaction_id')->sum('total_amount');

        if ($due) {
            // Thu tiền 1 kỳ hợp đồng: tiền thuê tháng + dịch vụ chưa thu
            $this->pay_category = 'Tiền phòng';
            $this->pay_amount = $this->fmt((float) $booking->unit_price + (float) $unbilled);
        } elseif ($this->pay_type === 'expense') {
            $this->pay_category = $category ?: 'Hoàn cọc';
            $this->pay_amount = '';
        } else {
            $this->pay_category = $category ?: 'Tiền cọc';
            $this->pay_amount = $this->pay_category === 'Tiền phòng'
                ? $this->fmt(max(0, $this->summary($booking)['remaining'] ?? 0))
                : '';
        }

        $this->showPayForm = true;
    }

    public function updatedPayUsd()
    {
        $usd = (float) str_replace(',', '.', (string) $this->pay_usd);
        $rate = $this->money($this->pay_rate);
        if ($usd > 0 && $rate > 0) {
            $this->pay_amount = $this->fmt(round($usd * $rate));
        }
    }

    public function updatedPayRate()
    {
        $this->updatedPayUsd();
    }

    public function savePayment()
    {
        $booking = $this->currentBooking();
        if (!$booking) {
            return;
        }

        $amount = $this->money($this->pay_amount);
        $this->validate([
            'pay_type' => 'required|in:income,expense',
            'pay_category' => 'required|string|max:50',
            'pay_method' => 'required|in:cash,transfer',
            'pay_date' => 'required|date',
        ], [], ['pay_date' => 'ngày']);

        if ($amount <= 0) {
            $this->addError('pay_amount', 'Nhập số tiền.');
            return;
        }

        if ($this->payingDue && $booking->transactions()->where('type', Transaction::INCOME)->whereDate('period_due', $this->payingDue)->exists()) {
            $this->dispatch('toast', message: 'Kỳ này đã được ghi nhận thanh toán.', type: 'error');
            $this->showPayForm = false;
            return;
        }

        $note = trim((string) $this->pay_note);
        if ((float) str_replace(',', '.', (string) $this->pay_usd) > 0) {
            $note = trim($note . ' (' . $this->pay_usd . ' USD x ' . $this->pay_rate . ')');
        }
        if ($this->payingDue) {
            $note = trim('Kỳ ' . Carbon::parse($this->payingDue)->format('d/m/Y') . '. ' . $note);
        }

        DB::transaction(function () use ($booking, $amount, $note) {
            $txn = Transaction::recordForBooking($booking, [
                'type' => $this->pay_type,
                'category' => $this->pay_category,
                'amount' => $amount,
                'method' => $this->pay_method,
                'txn_date' => $this->pay_date,
                'description' => $note ?: null,
                'period_due' => $this->payingDue,
            ]);

            // Thu tiền phòng/dịch vụ -> đánh dấu các dịch vụ chưa thu là đã thu trong phiếu này
            if ($this->pay_type === 'income' && in_array($this->pay_category, ['Tiền phòng', 'Dịch vụ'])) {
                $booking->usageLogs()->whereNull('transaction_id')->update(['transaction_id' => $txn->id]);
            }
        });

        $this->showPayForm = false;
        $this->payingDue = null;
        $this->dispatch('booking-saved');
        $this->dispatch('toast', message: 'Đã ghi nhận ' . ($this->pay_type === 'income' ? 'khoản thu' : 'khoản chi') . ' ' . $this->fmt($amount) . 'đ.', type: 'success');
    }

    public function deletePayment($txnId)
    {
        $booking = $this->currentBooking();
        if (!$booking) {
            return;
        }
        $txn = Transaction::where('booking_id', $booking->id)->find($txnId);
        if ($txn) {
            $txn->delete(); // các dịch vụ gắn với phiếu này tự trở về "chưa thu"
            $this->dispatch('booking-saved');
            $this->dispatch('toast', message: 'Đã huỷ phiếu.', type: 'success');
        }
    }

    /**
     * Tổng hợp tiền của 1 booking.
     * - Thuê ngày: phải thu = tiền phòng + dịch vụ; đã thu = thu - chi (cọc tính vào đã thu).
     * - Hợp đồng: theo từng kỳ; tiền cọc là khoản giữ riêng.
     */
    protected function summary(Booking $booking): array
    {
        $txns = $booking->transactions()->orderBy('txn_date')->orderBy('id')->get();
        $logs = $booking->usageLogs()->with('service')->orderBy('billing_date')->orderBy('id')->get();

        $income = $txns->where('type', Transaction::INCOME);
        $expense = $txns->where('type', Transaction::EXPENSE);
        $servicesTotal = (float) $logs->sum('total_amount');

        $result = [
            'txns' => $txns,
            'logs' => $logs,
            'servicesTotal' => $servicesTotal,
            'unbilledTotal' => (float) $logs->whereNull('transaction_id')->sum('total_amount'),
        ];

        if ($booking->isContract()) {
            $depositIn = (float) $income->where('category', 'Tiền cọc')->sum('amount');
            $depositOut = (float) $expense->where('category', 'Hoàn cọc')->sum('amount');
            $paidByDue = $income->whereNotNull('period_due')->keyBy(fn ($t) => $t->period_due->toDateString());

            $today = now()->startOfDay();
            $periods = [];
            foreach ($booking->contractDueDates(now()->addDays(35)) as $i => $due) {
                $periods[] = [
                    'no' => $i + 1,
                    'due' => $due,
                    'txn' => $paidByDue->get($due->toDateString()),
                    'days' => (int) $today->diffInDays($due, false), // âm = quá hạn
                ];
            }

            return $result + [
                'contract' => true,
                'periods' => array_reverse($periods), // kỳ mới nhất lên đầu
                'depositHeld' => $depositIn - $depositOut,
            ];
        }

        $due = (float) $booking->price + $servicesTotal;
        $paid = (float) $income->sum('amount') - (float) $expense->sum('amount');

        return $result + [
            'contract' => false,
            'due' => $due,
            'paid' => $paid,
            'remaining' => $due - $paid,
        ];
    }

    // =====================================================================
    // IN XÁC NHẬN / HOÁ ĐƠN / GỬI EMAIL
    // =====================================================================

    public function printConfirmation()
    {
        $booking = $this->currentBooking();
        if (!$booking) {
            return;
        }
        $deposit = (float) $booking->transactions()->where('type', Transaction::INCOME)->where('category', 'Tiền cọc')->sum('amount');

        $this->printData = [
            'customer_name' => $booking->customer->name ?? '-',
            'customer_phone' => $booking->customer->phone ?? '-',
            'room_code' => $booking->room->code ?? '-',
            'check_in' => $booking->check_in?->format('d / m / Y') ?? '-',
            'term_of_stay' => ($booking->check_in && $booking->check_out)
                ? $booking->check_in->startOfDay()->diffInDays($booking->check_out->startOfDay()) . ' night(s)'
                : 'Long-term contract',
            'unit_price' => $this->fmt($booking->unit_price),
            'price_type' => $booking->isContract() ? 'month' : 'night',
            'total_deposit' => $deposit,
            'guests' => collect($booking->additional_guests ?? [])->pluck('name')->filter()->values()->toArray(),
        ];
        $this->printMode = 'confirmation';
    }

    public function printInvoice($due = null)
    {
        $booking = $this->currentBooking();
        if (!$booking) {
            return;
        }
        [$logs, $roomPrice, $label] = $this->invoiceParts($booking, $due);

        $paid = 0;
        if (!$booking->isContract()) {
            $paid = (float) $booking->transactions()->where('type', Transaction::INCOME)->sum('amount')
                - (float) $booking->transactions()->where('type', Transaction::EXPENSE)->sum('amount');
        }

        $this->printData = [
            'label' => $label,
            'room_code' => $booking->room->code ?? '-',
            'customer_name' => $booking->customer->name ?? '-',
            'check_in' => $booking->check_in?->format('d/m/Y') ?? '-',
            'check_out' => $booking->check_out?->format('d/m/Y'),
            'room_price' => $roomPrice,
            'room_label' => $booking->isContract()
                ? 'Tiền phòng 1 tháng / Monthly rent'
                : 'Tiền phòng / Room (' . $this->nights() . ' đêm x ' . $this->fmt($booking->unit_price) . ')',
            'logs' => $logs->map(fn ($l) => [
                'name' => $l->service->name ?? ($l->notes ?: 'Phụ thu'),
                'type' => $l->type,
                'start' => (float) $l->start_index,
                'end' => (float) $l->end_index,
                'qty' => (float) $l->quantity,
                'unit' => $l->billing_unit,
                'unit_price' => (float) $l->unit_price,
                'total' => (float) $l->total_amount,
            ])->values()->toArray(),
            'total' => $roomPrice + (float) $logs->sum('total_amount'),
            'paid' => $paid,
        ];
        $this->printMode = 'invoice';
    }

    public function sendInvoice($due = null)
    {
        $booking = $this->currentBooking();
        if (!$booking) {
            return;
        }
        $email = $booking->customer->email ?? null;
        if (!$email) {
            $this->dispatch('toast', message: 'Khách chưa có email.', type: 'warning');
            return;
        }

        [$logs, $roomPrice, $label] = $this->invoiceParts($booking, $due);
        try {
            Mail::to($email)->send(new InvoiceMail($booking, $logs, $roomPrice, $label));
            BookingUsageLog::whereIn('id', $logs->pluck('id'))->update(['email_sent_at' => now()]);
            $this->dispatch('toast', message: 'Đã gửi hoá đơn tới ' . $email, type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Gửi email thất bại: ' . $e->getMessage(), type: 'error');
        }
    }

    /**
     * Hợp đồng: kỳ đã thu -> dịch vụ gắn với phiếu thu đó; kỳ chưa thu -> dịch vụ chưa thu.
     * Thuê ngày: toàn bộ tiền phòng + dịch vụ.
     */
    protected function invoiceParts(Booking $booking, $due): array
    {
        $logs = $booking->usageLogs()->with('service')->orderBy('billing_date')->get();

        if ($booking->isContract()) {
            $due = $due ?: optional(collect($booking->contractDueDates(now()->addDays(35)))->last())->toDateString();
            $txn = $due ? $booking->transactions()->where('type', Transaction::INCOME)->whereDate('period_due', $due)->first() : null;
            $logs = $txn ? $logs->where('transaction_id', $txn->id) : $logs->whereNull('transaction_id');
            $label = $due ? 'Kỳ ' . Carbon::parse($due)->format('d/m/Y') : null;
            return [$logs->values(), (float) $booking->unit_price, $label];
        }

        return [$logs, (float) $booking->price, null];
    }

    public function closePrint()
    {
        $this->printMode = null;
        $this->printData = [];
    }

    // =====================================================================
    // HELPERS
    // =====================================================================

    protected function money($value): float
    {
        return (float) preg_replace('/[^0-9]/', '', (string) $value);
    }

    protected function fmt($value): string
    {
        return number_format((float) $value, 0, ',', '.');
    }

    /** Chỉ số công tơ: '.' = nghìn, ',' = thập phân. "1.250,5" -> 1250.5 */
    protected function meterNumber($value): float
    {
        $clean = str_replace(',', '.', str_replace('.', '', trim((string) $value)));
        return is_numeric($clean) ? (float) $clean : 0.0;
    }

    protected function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                return substr($value, 0, 10);
            }
            $clean = str_replace([' ', '-', '.'], ['', '/', '/'], $value);
            return Carbon::createFromFormat('d/m/Y', $clean)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function displayDate($value): string
    {
        if (empty($value)) {
            return '';
        }
        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    public function render()
    {
        $data = [
            'booking' => null,
            'sum' => null,
            'rooms' => collect(),
            'meterServices' => collect(),
            'feeServices' => collect(),
            'customerResults' => collect(),
            'lastIndexes' => [],
        ];

        if ($this->show) {
            $user = auth()->user();
            $data['rooms'] = Room::with('area')
                ->when($user->isAreaRestricted(), fn ($q) => $q->where('area_id', $user->area_id))
                ->orderBy('area_id')->orderBy('code')->get()
                ->groupBy(fn ($r) => $r->area->name ?? 'Khác');

            if (strlen(trim($this->customer_search)) >= 1) {
                $term = '%' . trim($this->customer_search) . '%';
                $data['customerResults'] = Customer::where(fn ($q) => $q->where('name', 'like', $term)->orWhere('phone', 'like', $term)->orWhere('identity_id', 'like', $term))
                    ->orderBy('name')->limit(8)->get();
            }

            if ($this->bookingId) {
                $booking = Booking::with(['room.area', 'customer'])->find($this->bookingId);
                if ($booking) {
                    $data['booking'] = $booking;
                    $data['sum'] = $this->summary($booking);
                    $services = Service::where('is_active', true)->orderBy('name')->get();
                    $data['meterServices'] = $services->where('type', 'meter')->values();
                    $data['feeServices'] = $services->where('type', '!=', 'meter')->values();
                    foreach ($data['meterServices'] as $svc) {
                        $data['lastIndexes'][$svc->id] = $this->lastMeterIndex($booking, $svc->id);
                    }
                }
            }
        }

        $data['countries'] = $this->show ? array_values($this->getFormattedCountries()) : [];

        return view('livewire.admin.booking-form', $data);
    }
}
