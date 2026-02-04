<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Quản lý Đặt phòng</h1>
        <x-ui.button wire:click="create" variant="primary" size="md">
            + Tạo Booking mới
        </x-ui.button>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 px-3 py-2 rounded-lg relative mb-6 shadow-sm flex items-center gap-2" role="alert">
            <x-icon name="heroicon-o-check-circle" class="h-5 w-5" />
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <x-ui.card class="p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Room</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider w-[150px]">Khách hàng</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Thời gian</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Tiền phòng</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Cọc</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Ghi chú</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-900 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($bookings as $booking)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                             {{ $booking->room->code ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium max-w-[150px] truncate" title="{{ $booking->customer->name ?? '' }}">
                            <span class="font-bold text-gray-900">{{ $booking->customer->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                            <div class="flex flex-col">
                                <span class="text-[12px] text-gray-900">In: {{ $booking->check_in ? $booking->check_in->format('d/m/Y') : '-' }}</span>
                                <span class="text-[12px] {{ $booking->price_type == 'month' && !$booking->check_out ? 'text-red-500' : 'text-gray-900' }}">
                                    Out: {{ $booking->check_out ? $booking->check_out->format('d/m/Y') : 'Dài hạn' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-900 text-base">{{ number_format($booking->price, 0, ',', '.') }}đ</span>
                                @if($booking->unit_price > 0)
                                    <span class="text-[10px] text-gray-600 uppercase font-semibold">Đơn giá: {{ number_format($booking->unit_price, 0, ',', '.') }}đ</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex flex-col gap-1">
                                @if($booking->deposit > 0) <span class="text-xs">L1: {{ number_format($booking->deposit, 0, ',', '.') }}đ</span> @endif
                                @if($booking->deposit_2 > 0) <span class="text-xs">L2: {{ number_format($booking->deposit_2, 0, ',', '.') }}đ</span> @endif
                                @if($booking->deposit_3 > 0) <span class="text-xs">L3: {{ number_format($booking->deposit_3, 0, ',', '.') }}đ</span> @endif
                                @if($booking->deposit == 0 && $booking->deposit_2 == 0 && $booking->deposit_3 == 0) - @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @php
                                $statusColors = [
                                    'pending' => 'yellow',

                                    'checked_in' => 'green',
                                    'checked_out' => 'gray',
                                    'cancelled' => 'red',
                                ];
                                $statusLabels = [
                                    'pending' => 'Chờ lấy phòng',

                                    'checked_in' => 'Đang ở',
                                    'checked_out' => 'Đã trả',
                                    'cancelled' => 'Đã hủy',
                                ];
                            @endphp
                            <x-ui.badge :variant="$statusColors[$booking->status] ?? 'gray'">
                                {{ $statusLabels[$booking->status] ?? $booking->status }}
                            </x-ui.badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 truncate max-w-xs">
                            {{ $booking->notes }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                             <x-ui.button wire:click="edit({{ $booking->id }})" variant="secondary" size="sm">
                                Sửa
                            </x-ui.button>
                            <x-ui.button 
                                wire:click="delete({{ $booking->id }})" 
                                wire:confirm="Bạn có chắc chắn muốn xóa không?" 
                                variant="danger" 
                                size="sm">
                                Xóa
                            </x-ui.button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
            {{ $bookings->links() }}
        </div>
    </x-ui.card>

    <!-- Professional Vertical Tabbed Modal -->
    <x-ui.modal name="showModal" :title="$editingBookingId ? 'Quản lý Booking: ' . ($bookings->find($editingBookingId)->room->code ?? '') : 'Tạo Booking mới'" width="max-w-[80rem]">
        <div class="flex flex-col lg:flex-row h-[85vh] -m-6 overflow-hidden">
            <!-- LEFT SIDEBAR NAVIGATION -->
            <div class="w-full lg:w-52 bg-slate-950 border-r border-slate-900 flex flex-col p-2 gap-1">
                @php
                    $tabs = [
                        ['id' => 'overview', 'label' => 'Tổng quan', 'icon' => 'home'],
                        ['id' => 'customer', 'label' => 'Khách hàng', 'icon' => 'user'],
                        ['id' => 'room', 'label' => 'Phòng & Giá', 'icon' => 'building-office'],
                        ['id' => 'services', 'label' => 'Dịch vụ', 'icon' => 'wrench-screwdriver'],
                        ['id' => 'history', 'label' => 'Nhật ký & Chốt phí', 'icon' => 'list-bullet'],
                    ];
                @endphp

                @foreach($tabs as $tab)
                    <button type="button" 
                        wire:click="setTab('{{ $tab['id'] }}')"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-bold transition-all duration-200 {{ $activeModalTab === $tab['id'] ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-500 hover:bg-slate-900 hover:text-white' }}">
                        <x-icon name="heroicon-o-{{ $tab['icon'] }}" class="h-4 w-4" />
                        {{ $tab['label'] }}
                    </button>
                @endforeach

                <div class="mt-auto pt-2 border-t border-slate-900">
                    <div class="p-2 bg-slate-900/50 rounded-lg">
                        <p class="text-xs text-slate-500 uppercase font-black tracking-widest mb-1.5 px-1">Trạng thái</p>
                        <select wire:model="status" class="w-full bg-slate-900 border-slate-700 text-white text-sm font-bold rounded p-1.5 focus:ring-blue-600 focus:border-blue-500 transition-all">
                            <option value="pending">Chờ lấy phòng</option>

                            <option value="checked_in">Nhận phòng</option>
                            <option value="checked_out">Trả phòng</option>
                            <option value="cancelled">Hủy</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- RIGHT CONTENT AREA -->
            <div class="flex-1 flex flex-col bg-white overflow-hidden">
                <form wire:submit="save" class="flex flex-col h-full bg-gray-50/10">
                    <div class="flex-1 overflow-y-auto p-5">
                        
                        <!-- TAB: OVERVIEW -->
                        <div x-show="$wire.activeModalTab === 'overview'" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="bg-indigo-600 p-4 rounded-xl text-white shadow-lg overflow-hidden relative">
                                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-white/10 rounded-full blur-2xl"></div>
                                    <p class="text-xs font-black uppercase tracking-widest mb-1 opacity-80">Tổng cộng hóa đơn</p>
                                    <p class="text-3xl font-black">
                                        @php
                                            $logTotal = collect($usage_logs)->sum('total_amount');
                                            $basePrice = (float) str_replace(['.', ','], '', $price ?: 0);
                                            $totalFinal = $basePrice + $logTotal;
                                        @endphp
                                        {{ number_format($totalFinal, 0, ',', '.') }}đ
                                    </p>
                                    <div class="mt-2 text-xs bg-black/20 p-1.5 rounded-lg flex justify-between items-center">
                                        <span class="opacity-80">Còn phải thu:</span>
                                        <span class="font-black">{{ number_format($totalFinal - (float)str_replace(['.', ','], '', $deposit ?: 0), 0, ',', '.') }}đ</span>
                                    </div>
                                </div>
                                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-center">
                                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-2 px-1">Phân bổ nguồn thu</p>
                                    <div class="space-y-1.5 text-sm">
                                        <div class="flex justify-between items-center text-gray-900">
                                            <span>Tiền phòng gốc:</span>
                                            <span class="font-bold text-gray-900">{{ number_format($basePrice, 0, ',', '.') }}đ</span>
                                        </div>
                                        <div class="flex justify-between items-center text-gray-900">
                                            <span>Dịch vụ liên kết:</span>
                                            <span class="font-bold text-gray-900">{{ number_format(collect($usage_logs)->where('type', '!==', 'manual')->sum('total_amount'), 0, ',', '.') }}đ</span>
                                        </div>
                                        <div class="flex justify-between items-center text-gray-900">
                                            <span>Phụ thu thủ công:</span>
                                            <span class="font-bold text-indigo-600">{{ number_format(collect($usage_logs)->where('type', 'manual')->sum('total_amount'), 0, ',', '.') }}đ</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm space-y-3">
                                <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b pb-2 flex items-center gap-2">
                                    <div class="w-1 h-1 rounded-full bg-blue-600"></div>
                                    Tóm tắt thông tin
                                </h4>
                                <div class="grid grid-cols-2 gap-y-2 text-sm">
                                    <div class="text-gray-400 font-medium italic">Phòng:</div>
                                    <div class="font-bold text-gray-900 text-right">{{ $rooms->find($room_id)?->code ?? 'Chưa chọn' }}</div>
                                    <div class="text-gray-400 font-medium italic">Khách hàng:</div>
                                    <div class="font-bold text-gray-900 text-right truncate pl-4">{{ $activeTab === 'existing' ? ($customers->find($customer_id)?->name ?? 'Chưa chọn') : $new_customer_name }}</div>
                                    <div class="text-gray-400 font-medium italic">Thời gian:</div>
                                    <div class="text-right">
                                        <div class="font-bold text-gray-900">In: {{ \Carbon\Carbon::parse($check_in)->format('d/m/Y') }}</div>
                                        <div class="font-bold {{ $price_type == 'month' && !$check_out ? 'text-red-500' : 'text-gray-900' }}">
                                            Out: {{ $check_out ? \Carbon\Carbon::parse($check_out)->format('d/m/Y') : 'Dài hạn' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB: CUSTOMER -->
                        <div x-show="$wire.activeModalTab === 'customer'" class="space-y-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-base font-black text-gray-900 uppercase tracking-tight">Thông tin Khách</h3>
                                <button type="button" wire:click="$set('activeTab', '{{ $activeTab === 'existing' ? 'new' : 'existing' }}')"
                                    class="text-xs font-black py-1.5 px-3 rounded bg-gray-100 text-gray-600 uppercase hover:bg-blue-600 hover:text-white transition-all">
                                    {{ $activeTab === 'existing' ? '+ Tạo mới' : '← Tìm sẵn' }}
                                </button>
                            </div>

                            @if($activeTab === 'existing')
                                 <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm space-y-3">
                                    <select wire:model.live="customer_id" class="block w-full rounded-lg border-gray-200 bg-white p-2.5 text-sm font-bold text-gray-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm">
                                        <option value="">-- Chọn Khách hàng --</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                                        @endforeach
                                    </select>
                                    
                                    @if($customer_id && ($selectedCustomer = $customers->find($customer_id)))
                                        <div class="pt-3 border-t border-gray-100 grid grid-cols-2 gap-3">
                                            <div class="p-2 bg-gray-50 rounded-lg">
                                                <p class="text-xs text-gray-400 font-bold uppercase mb-0.5">SĐT</p>
                                                <p class="text-sm font-bold text-gray-900">{{ $selectedCustomer->phone }}</p>
                                            </div>
                                            <div class="p-2 bg-gray-50 rounded-lg">
                                                <p class="text-xs text-gray-400 font-bold uppercase mb-0.5">Identity</p>
                                                <p class="text-sm font-bold text-gray-900">{{ $selectedCustomer->identity ?: 'N/A' }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm grid grid-cols-2 gap-3">
                                    <div class="col-span-2"><x-ui.input label="Họ tên" id="new_customer_name" wire:model="new_customer_name" required compact /></div>
                                    <x-ui.input label="Số điện thoại" id="new_customer_phone" wire:model="new_customer_phone" compact />
                                    <x-ui.input label="Email" type="email" id="new_customer_email" wire:model="new_customer_email" compact />
                                    <x-ui.input label="CMND/Passport" id="new_customer_identity" wire:model="new_customer_identity" compact />
                                    <x-ui.input label="Số Visa" id="new_customer_visa_number" wire:model="new_customer_visa_number" compact />
                                    <div class="col-span-2 space-y-1">
                                         <label class="text-xs font-bold text-gray-400 uppercase">Ảnh Visa/Passport</label>
                                         <input type="file" wire:model="new_customer_image" class="block w-full text-xs text-gray-500 file:mr-4 file:py-1 file:px-4 file:rounded file:border-0 file:text-xs file:font-black file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- TAB: ROOM & PRICE -->
                        <div x-show="$wire.activeModalTab === 'room'" class="space-y-4">
                            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm space-y-4">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-400 uppercase">Chọn phòng</label>
                                    <select wire:model.live="room_id" class="block w-full rounded-lg border-gray-200 bg-white p-2.5 text-base font-black text-gray-900 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm">
                                        <option value="">-- Chọn Phòng --</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->code }} ({{ $room->type }}) - {{ $room->area->name ?? '' }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="col-span-2 p-3 bg-gray-50 rounded-lg space-y-2">
                                        <label class="text-xs font-bold text-gray-400 uppercase">Loại hình thuê</label>
                                        <div class="flex p-0.5 bg-gray-200 rounded-lg">
                                            <button type="button" wire:click="$set('price_type', 'day')" class="flex-1 py-1.5 rounded-md text-xs font-black uppercase {{ $price_type === 'day' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500' }}">Ngày</button>
                                            <button type="button" wire:click="$set('price_type', 'hour')" class="flex-1 py-1.5 rounded-md text-xs font-black uppercase {{ $price_type === 'hour' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500' }}">Giờ</button>
                                            <button type="button" wire:click="$set('price_type', 'month')" class="flex-1 py-1.5 rounded-md text-xs font-black uppercase {{ $price_type === 'month' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500' }}">Tháng</button>
                                        </div>
                                    </div>
                                    <x-ui.input label="Đơn giá" wire:model.blur="unit_price" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" compact />
                                    <x-ui.input label="Check-in" type="datetime-local" wire:model="check_in" compact />
                                    <x-ui.input label="Check-out" type="datetime-local" wire:model="check_out" compact />
                                    <x-ui.input label="Tổng tiền phòng" wire:model.blur="price" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" compact />
                                    <div class="col-span-2 grid grid-cols-3 gap-2">
                                        <x-ui.input label="Cọc L1" wire:model.blur="deposit" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" compact />
                                        <x-ui.input label="Cọc L2" wire:model.blur="deposit_2" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" compact />
                                        <x-ui.input label="Cọc L3" wire:model.blur="deposit_3" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" compact />
                                    </div>
                                </div>

                                <x-ui.textarea label="Ghi chú" wire:model="notes" rows="2" compact />
                            </div>
                        </div>

                        <!-- TAB: SERVICES -->
                        <div x-show="$wire.activeModalTab === 'services'" class="space-y-3">
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($all_services as $service)
                                    <div wire:click="toggleService({{ $service->id }})" class="p-2.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between {{ !empty($selected_services[$service->id]['selected']) ? 'border-blue-600 bg-blue-50/50' : 'border-gray-100 hover:border-gray-300' }}">
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded bg-white flex items-center justify-center border shadow-sm text-blue-500">
                                                <x-icon name="heroicon-o-sparkles" class="h-4 w-4" />
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900 leading-tight">{{ $service->name }}</p>
                                                <p class="text-xs text-gray-400 font-bold uppercase">{{ number_format($service->unit_price, 0, ',', '.') }}đ</p>
                                            </div>
                                        </div>
                                        <div class="w-4 h-4 rounded-full border flex items-center justify-center {{ !empty($selected_services[$service->id]['selected']) ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300' }}">
                                            @if(!empty($selected_services[$service->id]['selected'])) <x-icon name="heroicon-s-check" class="h-2.5 w-2.5" /> @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Selected Service Allocation Inputs --}}
                            <div class="mt-4 space-y-2 border-t pt-2">
                                @php $hasSel = collect($selected_services)->filter(fn($s) => !empty($s['selected']))->isNotEmpty(); @endphp
                                @if($hasSel)
                                    <h3 class="font-bold text-gray-900 text-xs uppercase tracking-wider mb-2">Nhập chỉ số & Số lượng</h3>
                                    @foreach($all_services as $service)
                                        @if(!empty($selected_services[$service->id]['selected']) && isset($service_inputs[$service->id]))
                                            <div wire:key="service-input-{{ $service->id }}" class="p-2 border rounded-lg hover:border-blue-300 transition-all bg-gray-50/30">
                                                <div class="grid grid-cols-2 lg:grid-cols-6 gap-2 items-end">
                                                    <div class="lg:col-span-1 pb-1.5"><p class="text-xs font-black text-blue-600 uppercase truncate">{{ $service->name }}</p></div>
                                                    @if($service->type === 'meter')
                                                        <div class="space-y-0.5"><label class="text-[10px] text-gray-400 uppercase font-black">Số đầu</label><input type="number" wire:model="service_inputs.{{ $service->id }}.start_index" class="w-full text-sm font-bold border-gray-100 bg-gray-50/50 text-gray-400 rounded p-1 h-7 cursor-not-allowed" readonly></div>
                                                        <div class="space-y-0.5"><label class="text-[10px] text-gray-400 uppercase font-black">Số mới</label><input type="number" wire:model="service_inputs.{{ $service->id }}.end_index" class="w-full text-sm font-bold border-blue-200 bg-white rounded p-1 h-7 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"></div>
                                                    @else
                                                        <div class="space-y-0.5 lg:col-span-2"><label class="text-[10px] text-gray-400 uppercase font-black">S.Lượng ({{ $service->unit_name }})</label><input type="number" wire:model="service_inputs.{{ $service->id }}.quantity" class="w-full text-sm font-bold border-gray-200 bg-white rounded p-1 h-7 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"></div>
                                                    @endif
                                                    <div class="space-y-0.5"><label class="text-[10px] text-gray-400 uppercase font-black">Đơn giá</label><input type="text" wire:model.blur="service_inputs.{{ $service->id }}.unit_price" class="w-full text-sm font-bold border-gray-200 bg-white rounded p-1 h-7 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"></div>
                                                    <div class="flex flex-col items-center pb-1"><p class="text-[10px] text-gray-400 uppercase font-black">Tạm tính</p><p class="text-xs font-black text-blue-600">@php $inp = $service_inputs[$service->id] ?? []; $up = (float)str_replace(['.',','],'',$inp['unit_price']??'0'); if($service->type==='meter'){ $rt = max(0, ((float)($inp['end_index']??0) - (float)($inp['start_index']??0))) * $up; }else{ $rt = ((float)($inp['quantity'] ?? 1)) * $up; } @endphp {{ number_format($rt, 0, ',', '.') }}đ</p></div>
                                                    <div class="pb-0.5"><button type="button" wire:click="addServiceLog({{ $service->id }})" class="w-full bg-blue-600 text-white rounded text-xs font-black uppercase py-1.5 hover:bg-blue-700 shadow-sm active:scale-95 transition-all">Chốt</button></div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- TAB: HISTORY & BILLING -->
                        <div x-show="$wire.activeModalTab === 'history'" class="space-y-4">
                            <div class="bg-white p-4 rounded-xl border border-gray-200 space-y-3">
                                <p class="text-xs font-black text-gray-400 uppercase tracking-widest border-b pb-1.5 flex items-center justify-between">
                                    <span>Chốt số & Phí phát sinh</span>
                                    <span class="text-blue-500 normal-case italic font-medium">Lưu tức thì</span>
                                </p>
                                <div class="space-y-2">
                                    {{-- Allocation inputs moved from here --}}

                                    <!-- MANUAL SURCHARGE ROW -->
                                    <div class="pt-2 border-t border-dashed border-gray-200 mt-2">
                                        <p class="text-xs font-black text-indigo-400 uppercase tracking-widest mb-1.5 px-1 flex items-center gap-1">
                                            <x-icon name="heroicon-o-plus-circle" class="h-2.5 w-2.5" />
                                            Phụ thu thủ công (Khác)
                                        </p>
                                        <div class="grid grid-cols-12 gap-1.5 bg-indigo-50/50 p-2 rounded-lg border border-indigo-100/50">
                                            <div class="col-span-4 space-y-0.5">
                                                <label class="text-[10px] text-gray-400 uppercase font-black">Số tiền</label>
                                                <input type="text" wire:model.blur="manual_fee_amount" class="w-full text-sm font-bold border-indigo-200 bg-white rounded p-1 h-7" placeholder="Số tiền..." x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                                            </div>
                                            <div class="col-span-6 space-y-0.5">
                                                <label class="text-[10px] text-gray-400 uppercase font-black">Ghi chú phí</label>
                                                <input type="text" wire:model="manual_fee_notes" class="w-full text-sm font-bold border-indigo-100 bg-white rounded p-1 h-7" placeholder="Lý do phụ thu...">
                                            </div>
                                            <div class="col-span-2 flex items-end pb-0.5">
                                                <button type="button" wire:click="addManualSurcharge" class="w-full bg-indigo-600 text-white rounded text-xs font-black uppercase py-1.5 hover:bg-indigo-700 shadow-sm active:scale-95 transition-all">Lưu</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest px-1">Lịch sử tiêu thụ</p>
                                <div class="space-y-1.5 h-48 overflow-y-auto pr-1 scrollbar-thin scrollbar-thumb-gray-200">
                                    @forelse($usage_logs as $idx => $log)
                                        <div class="group flex items-center gap-2 bg-white px-2 py-1.5 rounded-lg border border-gray-100 hover:border-indigo-200 shadow-sm transition-all animate-fadeIn">
                                            <div class="flex-1">
                                                <div class="flex justify-between items-center mb-0.5">
                                                    <p class="text-xs font-black text-gray-900 uppercase tracking-tight">{{ $log['service_name'] }}</p>
                                                    <p class="text-xs font-black text-indigo-600">{{ number_format($log['total_amount'], 0, ',', '.') }}đ</p>
                                                </div>
                                                <div class="flex gap-2 text-[10px] text-gray-400 font-bold items-center">
                                                    <span>📅 {{ \Carbon\Carbon::parse($log['billing_date'])->format('d/m') }}</span>
                                                    @if($log['type'] === 'meter') 
                                                        <span class="text-indigo-400"># {{ $log['start_index'] }} → {{ $log['end_index'] }}</span> 
                                                    @elseif($log['type'] === 'manual')
                                                        <span class="px-1 bg-indigo-50 text-indigo-500 rounded uppercase text-[8px]">Surcharge</span>
                                                    @else 
                                                        <span># SL: {{ $log['quantity'] }}</span> 
                                                    @endif
                                                    
                                                    @if(!empty($log['notes']))
                                                        <span class="text-gray-300 italic truncate max-w-[100px] border-l pl-2">"{{ $log['notes'] }}"</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <button type="button" wire:click="removeUsageLog({{ $idx }})" class="opacity-0 group-hover:opacity-100 p-1 text-red-300 hover:text-red-500 hover:bg-red-50 rounded transition-all"><x-icon name="heroicon-o-trash" class="h-3.5 w-3.5" /></button>
                                        </div>
                                    @empty
                                        <p class="py-4 text-center text-gray-300 text-[10px] italic">Chưa có lịch sử</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- FOOTER -->
                    <div class="px-5 py-3.5 bg-white border-t border-gray-100 flex justify-end gap-2 items-center">
                        <button type="button" @click="show = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:bg-gray-100 rounded-lg transition-all">Huỷ</button>
                        <button type="submit" class="px-8 py-2.5 bg-blue-600 text-white text-sm font-black uppercase tracking-widest rounded-lg shadow-lg shadow-blue-100 hover:bg-blue-700 active:scale-95 transition-all flex items-center gap-2">
                            <x-icon name="heroicon-o-check" class="h-4 w-4" />
                            {{ $editingBookingId ? 'Cập nhật' : 'Tạo mới' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </x-ui.modal>

    <style>
        [x-cloak] { display: none !important; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fadeIn { animation: fadeIn 0.3s ease-out forwards; }
    </style>
</div>
