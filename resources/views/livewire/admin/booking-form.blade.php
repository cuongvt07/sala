<div>
@php
    $money = fn($v) => number_format((float) $v, 0, ',', '.');
    $methodLabels = \App\Models\Transaction::METHODS;
    $moneyInput = "\$el.value = \$el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')";
    $dateInput = "let v=\$el.value.replace(/\D/g,'').slice(0,8); \$el.value = v.length>=5 ? v.slice(0,2)+'/'+v.slice(2,4)+'/'+v.slice(4) : (v.length>=3 ? v.slice(0,2)+'/'+v.slice(2) : v)";
    $statusColors = ['pending' => 'bg-yellow-400 text-yellow-900', 'checked_in' => 'bg-green-500 text-white', 'checked_out' => 'bg-gray-500 text-white', 'cancelled' => 'bg-red-500 text-white'];
    $inp = 'w-full rounded-lg border-gray-300 py-1.5 px-2 text-sm focus:ring-blue-500 focus:border-blue-500';
    $lbl = 'block text-[10px] font-bold text-gray-500 uppercase mb-1';
@endphp

@if($show)
<div class="fixed inset-0 z-[100] overflow-y-auto" x-data x-on:keydown.escape.window="$wire.close()">
    <div class="fixed inset-0 bg-gray-900/60" wire:click="close"></div>
    <div class="relative min-h-screen flex items-start sm:items-center justify-center p-2 sm:p-6">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-5xl overflow-hidden">

            {{-- HEADER --}}
            <div class="bg-slate-800 text-white px-5 py-3 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    @if($booking)
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xl font-black">Phòng {{ $booking->room->code ?? '-' }}</span>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded {{ $statusColors[$booking->status] ?? 'bg-gray-500' }}">{{ \App\Models\Booking::STATUS_LABELS[$booking->status] ?? $booking->status }}</span>
                            <span class="text-[10px] uppercase text-slate-300">{{ $booking->isContract() ? 'Hợp đồng tháng' : 'Thuê ngày' }}</span>
                        </div>
                        <div class="text-xs text-slate-300 mt-0.5 truncate">
                            {{ $booking->customer->name ?? '' }} · {{ $booking->check_in?->format('d/m/Y') }} → {{ $booking->check_out?->format('d/m/Y') ?? 'Không thời hạn' }} · #{{ $booking->id }}
                        </div>
                    @else
                        <div class="text-lg font-black">Đặt phòng mới</div>
                    @endif
                </div>
                <button type="button" wire:click="close" class="p-1 hover:bg-white/10 rounded-lg">
                    <x-icon name="heroicon-o-x-mark" class="h-6 w-6" />
                </button>
            </div>

            {{-- TABS --}}
            <div class="flex border-b border-gray-200 px-5 bg-white">
                <button type="button" wire:click="$set('tab', 'info')" class="px-4 py-2.5 text-sm font-bold border-b-2 {{ $tab === 'info' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">Thông tin</button>
                @if($booking)
                    <button type="button" wire:click="$set('tab', 'payment')" class="px-4 py-2.5 text-sm font-bold border-b-2 {{ $tab === 'payment' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        Dịch vụ & Thanh toán
                        @if($sum && $sum['contract'] && collect($sum['periods'])->contains(fn($p) => !$p['txn'] && $p['days'] <= 3))
                            <span class="ml-1 inline-block w-2 h-2 rounded-full bg-red-500"></span>
                        @elseif($sum && !$sum['contract'] && $sum['remaining'] > 0)
                            <span class="ml-1 inline-block w-2 h-2 rounded-full bg-orange-500"></span>
                        @endif
                    </button>
                @endif
            </div>

            <div class="p-5 max-h-[calc(100vh-200px)] overflow-y-auto bg-gray-50/60">

            {{-- ================= TAB THÔNG TIN ================= --}}
            @if($tab === 'info')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    {{-- Đặt phòng --}}
                    <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                        <h4 class="text-xs font-black text-gray-700 uppercase">Đặt phòng</h4>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="col-span-2">
                                <label class="{{ $lbl }}">Phòng *</label>
                                <select wire:model.live="room_id" class="{{ $inp }} font-bold">
                                    <option value="">-- Chọn phòng --</option>
                                    @foreach($rooms as $areaName => $areaRooms)
                                        <optgroup label="{{ $areaName }}">
                                            @foreach($areaRooms as $r)
                                                <option value="{{ $r->id }}">{{ $r->code }} · {{ $r->type }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('room_id')<span class="text-[11px] text-red-500">{{ $message }}</span>@enderror
                            </div>

                            <div class="col-span-2">
                                <label class="{{ $lbl }}">Hình thức thuê</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" wire:click="$set('price_type', 'day')" class="py-1.5 rounded-lg text-sm font-bold border {{ $price_type === 'day' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300' }}">Theo ngày</button>
                                    <button type="button" wire:click="$set('price_type', 'month')" class="py-1.5 rounded-lg text-sm font-bold border {{ $price_type === 'month' ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-600 border-gray-300' }}">Hợp đồng tháng</button>
                                </div>
                            </div>

                            <div>
                                <x-ui.select-date wire:model.live="check_in" label="Ngày nhận *" />
                                @error('check_in')<span class="text-[11px] text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <x-ui.select-date wire:model.live="check_out" :label="$price_type === 'month' ? 'Ngày trả (nếu có)' : 'Ngày trả *'" />
                                @error('check_out')<span class="text-[11px] text-red-500">{{ $message }}</span>@enderror
                            </div>

                            <div>
                                <label class="{{ $lbl }}">{{ $price_type === 'month' ? 'Giá thuê / tháng' : 'Giá / đêm' }}</label>
                                <input type="text" wire:model.blur="unit_price" x-on:input="{{ $moneyInput }}" class="{{ $inp }} font-bold text-right">
                            </div>
                            @if($price_type === 'day')
                                <div>
                                    <label class="{{ $lbl }}">Tổng tiền phòng</label>
                                    <input type="text" wire:model.blur="price" x-on:input="{{ $moneyInput }}" class="{{ $inp }} font-black text-right text-blue-700 bg-blue-50">
                                </div>
                            @else
                                <div class="text-[11px] text-blue-700 bg-blue-50 rounded-lg p-2 leading-snug">
                                    Đến hạn đóng tiền <b>đúng ngày {{ $check_in ? \Carbon\Carbon::parse($check_in)->format('d') : '..' }}</b> mỗi tháng. Dashboard cảnh báo trước 3 ngày.
                                </div>
                            @endif

                            <div>
                                <label class="{{ $lbl }}">Trạng thái</label>
                                <select wire:model.live="status" class="{{ $inp }}">
                                    <option value="pending">Chờ nhận phòng</option>
                                    <option value="checked_in">Đang ở (đã nhận)</option>
                                    @if($bookingId)
                                        <option value="checked_out">Trả phòng</option>
                                        <option value="cancelled">Huỷ</option>
                                    @endif
                                </select>
                            </div>
                            <div>
                                <label class="{{ $lbl }}">Nguồn khách</label>
                                <select wire:model="source" class="{{ $inp }}">
                                    @foreach(['Hotline', 'Facebook', 'Airbnb', 'OTA', 'Khách cũ', 'Khác'] as $s)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label class="{{ $lbl }}">Ghi chú</label>
                                <textarea wire:model.blur="notes" rows="2" class="{{ $inp }}"></textarea>
                            </div>
                        </div>

                        @if($bookingId && in_array($status, ['checked_out', 'cancelled']))
                            <div class="rounded-lg border-2 border-red-300 bg-red-50 p-3 text-xs text-red-700 space-y-1">
                                <p class="font-black uppercase">⚠ Khi bấm Lưu, thông tin khách & đặt phòng sẽ bị XOÁ khỏi hệ thống.</p>
                                <p>Sổ thu chi vẫn được giữ. Hãy ghi nhận đủ tiền / hoàn cọc ở tab "Dịch vụ & Thanh toán" trước.</p>
                                @if($sum && !$sum['contract'] && $sum['remaining'] > 0)
                                    <p class="font-bold">Khách còn phải thu: {{ $money($sum['remaining']) }}đ</p>
                                @endif
                                @if($sum && $sum['contract'] && $sum['depositHeld'] > 0)
                                    <p class="font-bold">Đang giữ cọc: {{ $money($sum['depositHeld']) }}đ (chưa hoàn)</p>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Khách hàng --}}
                    <div class="bg-white rounded-xl border border-gray-200 p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black text-gray-700 uppercase">Khách hàng</h4>
                            @if($customer_id)
                                <button type="button" wire:click="clearCustomer" class="text-[11px] font-bold text-blue-600 hover:underline">Đổi / khách mới</button>
                            @endif
                        </div>

                        @if(!$customer_id)
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.300ms="customer_search" placeholder="🔍 Tìm khách đang có (tên, SĐT, CCCD)... hoặc nhập mới bên dưới" class="{{ $inp }}">
                                @if($customerResults->isNotEmpty())
                                    <div class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-56 overflow-y-auto">
                                        @foreach($customerResults as $c)
                                            <button type="button" wire:click="selectCustomer({{ $c->id }})" class="block w-full text-left px-3 py-2 text-sm hover:bg-blue-50">
                                                <span class="font-bold">{{ $c->name }}</span>
                                                <span class="text-gray-500 text-xs">{{ $c->phone }} {{ $c->identity_id ? '· ' . $c->identity_id : '' }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-[11px] bg-blue-50 text-blue-700 rounded px-2 py-1">Đang dùng hồ sơ khách #{{ $customer_id }} — sửa bên dưới sẽ cập nhật hồ sơ.</div>
                        @endif

                        <div class="grid grid-cols-2 gap-3">
                            <div class="col-span-2">
                                <label class="{{ $lbl }}">Họ tên *</label>
                                <input type="text" wire:model.blur="c_name" class="{{ $inp }} font-bold">
                                @error('c_name')<span class="text-[11px] text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="{{ $lbl }}">SĐT</label>
                                <input type="text" wire:model.blur="c_phone" class="{{ $inp }}">
                            </div>
                            <div>
                                <label class="{{ $lbl }}">Email</label>
                                <input type="email" wire:model.blur="c_email" class="{{ $inp }}">
                                @error('c_email')<span class="text-[11px] text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="{{ $lbl }}">CCCD / Passport {{ $status === 'checked_in' ? '*' : '' }}</label>
                                <input type="text" wire:model.blur="c_identity" class="{{ $inp }}">
                                @error('c_identity')<span class="text-[11px] text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="{{ $lbl }}">Quốc tịch {{ $status === 'checked_in' ? '*' : '' }}</label>
                                <input type="text" wire:model.blur="c_nationality" list="bf-countries" class="{{ $inp }}" placeholder="Vietnam">
                                @error('c_nationality')<span class="text-[11px] text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="{{ $lbl }}">Ngày sinh</label>
                                <input type="text" wire:model.blur="c_birthday" x-on:input="{{ $dateInput }}" placeholder="dd/mm/yyyy" class="{{ $inp }} text-center">
                            </div>
                            <div>
                                <label class="{{ $lbl }}">Hạn visa</label>
                                <input type="text" wire:model.blur="c_visa_expiry" x-on:input="{{ $dateInput }}" placeholder="dd/mm/yyyy" class="{{ $inp }} text-center">
                            </div>
                            <div>
                                <label class="{{ $lbl }}">Giới tính</label>
                                <select wire:model="c_gender" class="{{ $inp }}">
                                    <option value="">--</option>
                                    <option value="male">Nam</option>
                                    <option value="female">Nữ</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>
                            <div>
                                <label class="{{ $lbl }}">Ảnh giấy tờ</label>
                                <input type="file" wire:model="c_image" accept="image/*" class="text-xs w-full">
                                @error('c_image')<span class="text-[11px] text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        {{-- Người ở cùng --}}
                        <div class="pt-3 border-t border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[10px] font-black text-gray-500 uppercase">Người ở cùng ({{ count($additional_guests) }})</span>
                                <button type="button" wire:click="addGuest" class="text-[11px] font-bold text-blue-600 hover:underline">+ Thêm người</button>
                            </div>
                            @foreach($additional_guests as $i => $g)
                                <div wire:key="guest-{{ $i }}" class="grid grid-cols-6 gap-1.5 mb-2 items-center bg-gray-50 rounded-lg p-2">
                                    <input type="text" wire:model.blur="additional_guests.{{ $i }}.name" placeholder="Họ tên" class="col-span-3 rounded border-gray-300 py-1 px-2 text-xs">
                                    <input type="text" wire:model.blur="additional_guests.{{ $i }}.identity" placeholder="CCCD/Passport" class="col-span-2 rounded border-gray-300 py-1 px-2 text-xs">
                                    <button type="button" wire:click="removeGuest({{ $i }})" class="text-red-500 text-xs font-bold">Xoá</button>
                                    <input type="text" wire:model.blur="additional_guests.{{ $i }}.nationality" list="bf-countries" placeholder="Quốc tịch" class="col-span-2 rounded border-gray-300 py-1 px-2 text-xs">
                                    <input type="text" wire:model.blur="additional_guests.{{ $i }}.birthday" x-on:input="{{ $dateInput }}" placeholder="Ngày sinh" class="col-span-2 rounded border-gray-300 py-1 px-2 text-xs text-center">
                                    <input type="text" wire:model.blur="additional_guests.{{ $i }}.visa_expiry" x-on:input="{{ $dateInput }}" placeholder="Hạn visa" class="col-span-2 rounded border-gray-300 py-1 px-2 text-xs text-center">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <datalist id="bf-countries">
                    @foreach($countries as $country)<option value="{{ $country }}">@endforeach
                </datalist>
            @endif

            {{-- ================= TAB DỊCH VỤ & THANH TOÁN ================= --}}
            @if($tab === 'payment' && $booking && $sum)
                <div class="space-y-4">
                    {{-- Tóm tắt --}}
                    @if($sum['contract'])
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-white border rounded-xl p-3"><p class="text-[10px] font-bold text-gray-500 uppercase">Tiền thuê / tháng</p><p class="text-lg font-black text-blue-700">{{ $money($booking->unit_price) }}đ</p></div>
                            <div class="bg-white border rounded-xl p-3"><p class="text-[10px] font-bold text-gray-500 uppercase">Cọc đang giữ</p><p class="text-lg font-black text-indigo-700">{{ $money($sum['depositHeld']) }}đ</p></div>
                            <div class="bg-white border rounded-xl p-3"><p class="text-[10px] font-bold text-gray-500 uppercase">Dịch vụ chưa thu</p><p class="text-lg font-black text-orange-600">{{ $money($sum['unbilledTotal']) }}đ</p></div>
                        </div>
                    @else
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-white border rounded-xl p-3"><p class="text-[10px] font-bold text-gray-500 uppercase">Phải thu (phòng + DV)</p><p class="text-lg font-black text-gray-800">{{ $money($sum['due']) }}đ</p></div>
                            <div class="bg-white border rounded-xl p-3"><p class="text-[10px] font-bold text-gray-500 uppercase">Đã thu (gồm cọc)</p><p class="text-lg font-black text-green-700">{{ $money($sum['paid']) }}đ</p></div>
                            <div class="rounded-xl p-3 {{ $sum['remaining'] > 0 ? 'bg-orange-500 text-white' : 'bg-green-600 text-white' }}"><p class="text-[10px] font-bold uppercase opacity-80">{{ $sum['remaining'] >= 0 ? 'Còn lại' : 'Thu thừa (cần hoàn)' }}</p><p class="text-lg font-black">{{ $money(abs($sum['remaining'])) }}đ</p></div>
                        </div>
                    @endif

                    {{-- Kỳ thanh toán hợp đồng --}}
                    @if($sum['contract'])
                        <div class="bg-white border rounded-xl overflow-hidden">
                            <div class="px-4 py-2 bg-slate-100 text-xs font-black text-slate-700 uppercase">Kỳ thanh toán (đến hạn đúng ngày {{ $booking->check_in->format('d') }} hàng tháng)</div>
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 text-[10px] uppercase text-gray-500">
                                    <tr><th class="px-3 py-2 text-left">Kỳ</th><th class="px-3 py-2 text-left">Đến hạn</th><th class="px-3 py-2 text-left">Trạng thái</th><th class="px-3 py-2 text-right">Thao tác</th></tr>
                                </thead>
                                <tbody class="divide-y">
                                    @forelse($sum['periods'] as $p)
                                        <tr wire:key="period-{{ $p['due']->toDateString() }}" class="{{ !$p['txn'] && $p['days'] < 0 ? 'bg-red-50' : (!$p['txn'] && $p['days'] <= 3 ? 'bg-amber-50' : '') }}">
                                            <td class="px-3 py-2 font-bold">Kỳ {{ $p['no'] }}</td>
                                            <td class="px-3 py-2">{{ $p['due']->format('d/m/Y') }}</td>
                                            <td class="px-3 py-2">
                                                @if($p['txn'])
                                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-green-700 bg-green-100 px-2 py-0.5 rounded">✓ Đã thanh toán</span>
                                                    <span class="text-[11px] text-gray-500">{{ $money($p['txn']->amount) }}đ · {{ $p['txn']->method_label }} · {{ $p['txn']->txn_date->format('d/m') }}</span>
                                                @elseif($p['days'] < 0)
                                                    <span class="text-[11px] font-bold text-white bg-red-500 px-2 py-0.5 rounded">Chưa thanh toán · quá hạn {{ abs($p['days']) }} ngày</span>
                                                @elseif($p['days'] === 0)
                                                    <span class="text-[11px] font-bold text-white bg-orange-500 px-2 py-0.5 rounded">Chưa thanh toán · đến hạn hôm nay</span>
                                                @else
                                                    <span class="text-[11px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded">Chưa thanh toán · còn {{ $p['days'] }} ngày</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                                <button type="button" wire:click="printInvoice('{{ $p['due']->toDateString() }}')" class="text-[11px] font-bold text-gray-600 hover:underline mr-2">Hoá đơn</button>
                                                @if($p['txn'])
                                                    <button type="button" wire:click="deletePayment({{ $p['txn']->id }})" wire:confirm="Huỷ ghi nhận thanh toán kỳ này?" class="text-[11px] font-bold text-red-500 hover:underline">Huỷ</button>
                                                @else
                                                    <button type="button" wire:click="openPay('{{ $p['due']->toDateString() }}')" class="text-[11px] font-bold text-white bg-green-600 hover:bg-green-700 px-2.5 py-1 rounded">Xác nhận đã thu</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="px-3 py-4 text-center text-gray-400 text-xs">Chưa có kỳ nào</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- Form ghi nhận thu/chi --}}
                    @if($showPayForm)
                        <div class="bg-white border-2 {{ $pay_type === 'income' ? 'border-green-400' : 'border-red-400' }} rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-black {{ $pay_type === 'income' ? 'text-green-700' : 'text-red-700' }}">
                                    {{ $pay_type === 'income' ? 'Ghi nhận khoản THU' : 'Ghi nhận khoản CHI' }}
                                    @if($payingDue) · Kỳ {{ \Carbon\Carbon::parse($payingDue)->format('d/m/Y') }} @endif
                                </h4>
                                <button type="button" wire:click="$set('showPayForm', false)" class="text-gray-400 hover:text-gray-600 text-xs font-bold">Đóng</button>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
                                <div>
                                    <label class="{{ $lbl }}">Khoản</label>
                                    <select wire:model="pay_category" class="{{ $inp }}" @disabled($payingDue)>
                                        @foreach($pay_type === 'income' ? \App\Models\Transaction::INCOME_CATEGORIES : \App\Models\Transaction::EXPENSE_CATEGORIES as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="{{ $lbl }}">Số tiền (đ) *</label>
                                    <input type="text" wire:model="pay_amount" x-on:input="{{ $moneyInput }}" class="{{ $inp }} font-black text-right">
                                    @error('pay_amount')<span class="text-[11px] text-red-500">{{ $message }}</span>@enderror
                                </div>
                                <div>
                                    <label class="{{ $lbl }}">Hình thức *</label>
                                    <div class="grid grid-cols-2 gap-1">
                                        @foreach($methodLabels as $key => $label)
                                            <button type="button" wire:click="$set('pay_method', '{{ $key }}')" class="py-1.5 rounded-lg text-xs font-bold border {{ $pay_method === $key ? 'bg-slate-800 text-white border-slate-800' : 'bg-white text-gray-600 border-gray-300' }}">{{ $key === 'cash' ? 'Tiền mặt' : 'CK' }}</button>
                                        @endforeach
                                    </div>
                                </div>
                                <div>
                                    <label class="{{ $lbl }}">Ngày</label>
                                    <input type="date" wire:model="pay_date" class="{{ $inp }}">
                                </div>
                                <button type="button" wire:click="savePayment" wire:loading.attr="disabled" class="py-2 rounded-lg text-sm font-black text-white {{ $pay_type === 'income' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">Xác nhận</button>
                                <div class="col-span-2 md:col-span-3">
                                    <label class="{{ $lbl }}">Ghi chú</label>
                                    <input type="text" wire:model="pay_note" class="{{ $inp }}">
                                </div>
                                <div class="col-span-2 flex gap-2 items-end">
                                    <div class="flex-1"><label class="{{ $lbl }}">Nếu trả USD</label><input type="text" wire:model.live.debounce.400ms="pay_usd" placeholder="USD" class="{{ $inp }} text-right"></div>
                                    <div class="flex-1"><label class="{{ $lbl }}">Tỷ giá</label><input type="text" wire:model.live.debounce.400ms="pay_rate" class="{{ $inp }} text-right"></div>
                                </div>
                            </div>
                            @if($payingDue)
                                <p class="text-[11px] text-gray-500 mt-2">Số tiền gợi ý = tiền thuê tháng + dịch vụ chưa thu ({{ $money($sum['unbilledTotal']) }}đ). Các dịch vụ này sẽ được đánh dấu đã thu.</p>
                            @endif
                        </div>
                    @endif

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        {{-- Dịch vụ --}}
                        <div class="bg-white border rounded-xl p-4 space-y-3">
                            <h4 class="text-xs font-black text-gray-700 uppercase">Dịch vụ / phụ thu</h4>

                            @foreach($meterServices as $svc)
                                <div wire:key="meter-{{ $svc->id }}" class="flex items-end gap-2">
                                    <div class="w-24 text-xs font-bold text-gray-700 pb-2">{{ $svc->name }}<div class="text-[10px] font-normal text-gray-400">{{ $money($svc->unit_price) }}đ/{{ $svc->unit_name }}</div></div>
                                    <div class="flex-1"><label class="{{ $lbl }}">Số đầu</label><input type="text" wire:model="meter_inputs.{{ $svc->id }}.start" placeholder="{{ $lastIndexes[$svc->id] ?? 0 }}" class="{{ $inp }} text-center"></div>
                                    <div class="flex-1"><label class="{{ $lbl }}">Số cuối</label><input type="text" wire:model="meter_inputs.{{ $svc->id }}.end" class="{{ $inp }} text-center font-bold border-blue-400"></div>
                                    <button type="button" wire:click="saveMeter({{ $svc->id }})" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold">Chốt</button>
                                </div>
                            @endforeach

                            <div class="flex items-end gap-2 pt-2 border-t">
                                <div class="flex-1">
                                    <label class="{{ $lbl }}">Khoản phí</label>
                                    <select wire:model="fee_service_id" class="{{ $inp }}">
                                        <option value="">Phụ thu khác (tự nhập)</option>
                                        @foreach($feeServices as $svc)
                                            <option value="{{ $svc->id }}">{{ $svc->name }} ({{ $money($svc->unit_price) }}đ)</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-14"><label class="{{ $lbl }}">SL</label><input type="number" min="1" wire:model="fee_qty" class="{{ $inp }} text-center"></div>
                                <div class="w-28"><label class="{{ $lbl }}">Đơn giá</label><input type="text" wire:model="fee_amount" x-on:input="{{ $moneyInput }}" placeholder="mặc định" class="{{ $inp }} text-right"></div>
                            </div>
                            <div class="flex gap-2">
                                <input type="text" wire:model="fee_note" placeholder="Ghi chú (vd: giặt ủi, thêm đêm...)" class="{{ $inp }}">
                                <button type="button" wire:click="addFee" class="px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs font-bold whitespace-nowrap">+ Thêm</button>
                            </div>

                            <table class="w-full text-xs">
                                <tbody class="divide-y">
                                    @forelse($sum['logs'] as $log)
                                        <tr wire:key="log-{{ $log->id }}">
                                            <td class="py-1.5">
                                                <div class="font-bold text-gray-700">{{ $log->service->name ?? ($log->notes ?: 'Phụ thu') }}</div>
                                                <div class="text-[10px] text-gray-400">
                                                    {{ $log->billing_date?->format('d/m/Y') }}
                                                    @if($log->type === 'meter') · {{ rtrim(rtrim(number_format($log->start_index, 2, ',', '.'), '0'), ',') }} → {{ rtrim(rtrim(number_format($log->end_index, 2, ',', '.'), '0'), ',') }}
                                                    @else · x{{ rtrim(rtrim(number_format($log->quantity, 2, ',', '.'), '0'), ',') }} @endif
                                                </div>
                                            </td>
                                            <td class="py-1.5 text-right font-bold">{{ $money($log->total_amount) }}đ</td>
                                            <td class="py-1.5 text-right w-20">
                                                @if($log->transaction_id)
                                                    <span class="text-[10px] font-bold text-green-600">Đã thu</span>
                                                @else
                                                    <button type="button" wire:click="removeLog({{ $log->id }})" wire:confirm="Xoá khoản này?" class="text-[10px] font-bold text-red-500">Xoá</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="py-3 text-center text-gray-400">Chưa có dịch vụ</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Thu / chi --}}
                        <div class="bg-white border rounded-xl p-4 space-y-3">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <h4 class="text-xs font-black text-gray-700 uppercase">Phiếu thu / chi</h4>
                                <div class="flex gap-1.5 flex-wrap">
                                    <button type="button" wire:click="openPay(null, 'Tiền cọc')" class="text-[11px] font-bold px-2 py-1 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">+ Thu cọc</button>
                                    @if(!$sum['contract'])
                                        <button type="button" wire:click="openPay(null, 'Tiền phòng')" class="text-[11px] font-bold px-2 py-1 rounded bg-green-50 text-green-700 border border-green-200">+ Thu tiền</button>
                                    @else
                                        <button type="button" wire:click="openPay(null, 'Dịch vụ')" class="text-[11px] font-bold px-2 py-1 rounded bg-green-50 text-green-700 border border-green-200">+ Thu khác</button>
                                    @endif
                                    <button type="button" wire:click="openPay(null, 'Hoàn cọc', 'expense')" class="text-[11px] font-bold px-2 py-1 rounded bg-red-50 text-red-700 border border-red-200">+ Chi / hoàn cọc</button>
                                </div>
                            </div>
                            <table class="w-full text-xs">
                                <tbody class="divide-y">
                                    @forelse($sum['txns'] as $t)
                                        <tr wire:key="txn-{{ $t->id }}">
                                            <td class="py-1.5">
                                                <div class="font-bold {{ $t->type === 'income' ? 'text-gray-800' : 'text-red-600' }}">{{ $t->category }}</div>
                                                <div class="text-[10px] text-gray-400">{{ $t->txn_date->format('d/m/Y') }} · {{ $t->method_label }} {{ $t->description ? '· ' . $t->description : '' }}</div>
                                            </td>
                                            <td class="py-1.5 text-right font-black {{ $t->type === 'income' ? 'text-green-700' : 'text-red-600' }}">{{ $t->type === 'income' ? '+' : '-' }}{{ $money($t->amount) }}đ</td>
                                            <td class="py-1.5 text-right w-12">
                                                <button type="button" wire:click="deletePayment({{ $t->id }})" wire:confirm="Huỷ phiếu này? (sẽ xoá khỏi sổ thu chi)" class="text-[10px] font-bold text-red-500">Huỷ</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="py-3 text-center text-gray-400">Chưa có phiếu thu/chi</td></tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <div class="flex gap-2 pt-2 border-t">
                                <button type="button" wire:click="printInvoice" class="flex-1 py-1.5 rounded-lg text-xs font-bold bg-gray-100 hover:bg-gray-200">🧾 In hoá đơn</button>
                                <button type="button" wire:click="sendInvoice" wire:loading.attr="disabled" class="flex-1 py-1.5 rounded-lg text-xs font-bold bg-gray-100 hover:bg-gray-200">✉ Gửi email hoá đơn</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            </div>

            {{-- FOOTER --}}
            <div class="px-5 py-3 bg-white border-t flex items-center gap-2">
                @if($booking)
                    <button type="button" wire:click="delete" wire:confirm="Xoá đặt phòng này? Thông tin khách sẽ bị xoá (phiếu thu chi vẫn giữ)." class="text-sm font-bold text-red-500 hover:text-red-700">Xoá</button>
                    <button type="button" wire:click="printConfirmation" class="ml-2 text-sm font-bold text-indigo-600 hover:text-indigo-800">🖨 In xác nhận</button>
                @endif
                <div class="ml-auto flex items-center gap-3">
                    <button type="button" wire:click="close" class="text-sm font-bold text-gray-500 hover:text-gray-700">Đóng</button>
                    <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save" class="px-6 py-2 rounded-lg text-sm font-black text-white uppercase disabled:opacity-50 {{ in_array($status, ['checked_out', 'cancelled']) ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700' }}">
                        <span wire:loading.remove wire:target="save">{{ $status === 'checked_out' ? 'Trả phòng & xoá' : ($status === 'cancelled' ? 'Huỷ & xoá' : ($bookingId ? 'Lưu' : 'Tạo đặt phòng')) }}</span>
                        <span wire:loading wire:target="save">Đang lưu...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- ================= XEM TRƯỚC KHI IN ================= --}}
@if($printMode)
<div class="fixed inset-0 z-[130] overflow-y-auto">
    <div class="fixed inset-0 bg-black/60 print:hidden" wire:click="closePrint"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white rounded-xl shadow-2xl max-w-3xl w-full">
            <div class="absolute top-3 right-3 flex gap-2 print:hidden">
                @if($printMode === 'confirmation')
                    <button onclick="printBookingConfirmation('bfConfirmation')" class="bg-blue-600 text-white rounded-full p-2"><x-icon name="heroicon-o-printer" class="h-5 w-5" /></button>
                @else
                    <button onclick="window.print()" class="bg-blue-600 text-white rounded-full p-2"><x-icon name="heroicon-o-printer" class="h-5 w-5" /></button>
                @endif
                <button wire:click="closePrint" class="bg-gray-100 rounded-full p-2"><x-icon name="heroicon-o-x-mark" class="h-5 w-5" /></button>
            </div>

            @if($printMode === 'confirmation')
                @include('livewire.admin.partials.print-confirmation', ['d' => $printData])
            @else
                @include('livewire.admin.partials.print-invoice', ['d' => $printData])
            @endif
        </div>
    </div>
</div>
@endif
</div>
