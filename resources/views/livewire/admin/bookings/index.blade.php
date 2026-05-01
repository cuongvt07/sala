<div class="relative">
    {{-- Hiệu ứng Loading toàn trang khi đổi khu hoặc thao tác --}}
    <div wire:loading wire:target="area-selected, search, statusFilter, dateFilter" class="absolute inset-0 z-50 flex items-center justify-center bg-white/40 backdrop-blur-[2px] transition-all duration-300 rounded-2xl">
        <div class="flex flex-col items-center">
            <div class="w-10 h-10 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div>
            <span class="mt-2 text-xs font-bold text-blue-600 uppercase tracking-widest animate-pulse">Đang tải dữ liệu...</span>
        </div>
    </div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Quản lý Đặt phòng</h1>
        <x-ui.button wire:click="create" variant="primary" size="md">
            + Tạo Booking mới
        </x-ui.button>
    </div>


    <!-- Advanced Filters -->
    <x-ui.filter-group title="Bộ lọc" icon="heroicon-o-funnel" :show="true">
        <x-ui.filter-item label="Tìm kiếm khách/phòng">
            <div class="relative">
                <x-icon name="heroicon-o-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" />
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       placeholder="Tên, SĐT, mã phòng..." 
                       class="w-full pl-9 pr-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-xs font-medium">
            </div>
        </x-ui.filter-item>

        <x-ui.filter-item label="Trạng thái">
            <select wire:model.live="filterStatus" 
                    class="w-full px-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-xs font-medium appearance-none">
                <option value="">Tất cả</option>
                <option value="pending">Chờ lấy phòng</option>
                <option value="checked_in">Đang ở</option>
                <option value="checked_out">Đã trả</option>
                <option value="cancelled">Đã hủy</option>
            </select>
        </x-ui.filter-item>

        <x-ui.filter-item label="Loại hình">
            <select wire:model.live="filterType" 
                    class="w-full px-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-xs font-medium appearance-none">
                <option value="">Tất cả</option>
                <option value="day">Ngắn ngày</option>
                <option value="month">Thuê hợp đồng</option>
            </select>
        </x-ui.filter-item>

        <x-ui.filter-item label="Khu vực / Toà nhà">
            <select wire:model.live="filterArea" 
                    class="w-full px-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-xs font-medium appearance-none">
                <option value="">Tất cả</option>
                @foreach(\App\Models\Area::all() as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
        </x-ui.filter-item>
    </x-ui.filter-group>

    <x-ui.card class="p-0 overflow-x-auto shadow-sm border border-gray-200 rounded-xl">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider w-10">ID</th>
                    <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Phòng & Check-in</th>
                    <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider w-[140px]">Khách hàng</th>
                    <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Thời gian</th>
                    <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Tiền phòng</th>
                    <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Cọc</th>
                    <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-3 py-3 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Ghi chú</th>
                    <th class="px-3 py-3 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">Thao tác</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach ($bookings as $booking)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-3 py-3 whitespace-nowrap bg-gray-50/50">
                            <span class="text-[10px] font-black text-gray-400">#{{ $booking->id }}</span>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap bg-gray-50/50">
                            <div class="text-xs font-black text-gray-900">{{ $booking->room->code ?? '-' }}</div>
                            <div class="text-[9px] text-blue-600 font-bold mt-0.5">In: {{ $booking->check_in ? $booking->check_in->format('d/m') : '-' }}</div>
                        </td>
                        <td class="px-3 py-3 text-xs text-gray-900 max-w-[140px]">
                            <div class="font-bold text-gray-900 leading-tight">{{ $booking->customer->name ?? '-' }}</div>
                            <div class="text-[10px] text-gray-500">{{ $booking->customer->phone ?? '' }}</div>
                            @if($booking->customer && $booking->customer->nationality)
                                <div class="text-[9px] text-indigo-600 font-black uppercase mt-0.5 tracking-tighter">🌍 {{ $booking->customer->nationality }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-xs text-gray-900">
                            <div class="flex flex-col leading-tight">
                                <span class="text-[11px] font-medium"><span class="text-gray-400">In:</span> {{ $booking->check_in ? $booking->check_in->format('d/m/y') : '-' }}</span>
                                <span class="text-[11px] font-medium {{ $booking->price_type == 'month' && !$booking->check_out ? 'text-red-500' : '' }}"><span class="text-gray-400">Out:</span> {{ $booking->check_out ? $booking->check_out->format('d/m/y') : 'Hợp đồng' }}</span>
                                <span class="text-[9px] font-bold uppercase mt-0.5 {{ $booking->price_type === 'month' ? 'text-blue-600' : 'text-gray-400' }}">{{ $booking->price_type === 'month' ? 'Hợp đồng' : 'Ngày' }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-900">
                            <div class="flex flex-col leading-tight">
                                <span class="font-bold text-gray-900 text-sm">{{ number_format($booking->price, 0, ',', '.') }}đ</span>
                                @if($booking->unit_price > 0)<span class="text-[9px] text-gray-500 font-medium uppercase">Đơn giá: {{ number_format($booking->unit_price, 0, ',', '.') }}đ</span>@endif
                            </div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-xs text-gray-900">
                            <div class="flex flex-col gap-0.5">
                                @if($booking->deposit > 0) <span class="text-[10px] font-semibold text-indigo-600">L1: {{ number_format($booking->deposit, 0, ',', '.') }}đ</span> @endif
                                @if($booking->deposit_2 > 0) <span class="text-[10px] font-semibold text-indigo-600">L2: {{ number_format($booking->deposit_2, 0, ',', '.') }}đ</span> @endif
                                @if($booking->deposit_3 > 0) <span class="text-[10px] font-semibold text-indigo-600">L3: {{ number_format($booking->deposit_3, 0, ',', '.') }}đ</span> @endif
                                @if($booking->deposit == 0 && $booking->deposit_2 == 0 && $booking->deposit_3 == 0) <span class="text-gray-300">-</span> @endif
                                
                                @php
                                    $totalCoc = $booking->deposit + $booking->deposit_2 + $booking->deposit_3;
                                    $totalPrice = $booking->price ?? 0;
                                @endphp
                                @if($booking->is_contract)
                                    <div class="mt-0.5 text-[8px] font-black uppercase text-blue-600 bg-blue-50 px-1 py-0.5 rounded border border-blue-100 w-max">Hợp đồng</div>
                                @elseif($totalCoc > 0)
                                    @if($totalCoc >= $totalPrice)
                                        <div class="mt-0.5 text-[8px] font-black uppercase text-green-600 bg-green-50 px-1 py-0.5 rounded border border-green-100 w-max">Full</div>
                                    @else
                                        <div class="mt-0.5 text-[8px] font-black uppercase text-orange-600 bg-orange-50 px-1 py-0.5 rounded border border-orange-100 w-max">Thiếu</div>
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap">
                            @php
                                $statusColors = ['pending' => 'yellow', 'checked_in' => 'green', 'checked_out' => 'gray', 'cancelled' => 'red'];
                                $statusLabels = ['pending' => 'Chờ lấy', 'checked_in' => 'Đang ở', 'checked_out' => 'Đã trả', 'cancelled' => 'Hủy'];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ 
                                match($booking->status) {
                                    'pending' => 'bg-yellow-100 text-yellow-700 border border-yellow-200',
                                    'checked_in' => 'bg-green-100 text-green-700 border border-green-200',
                                    'checked_out' => 'bg-gray-100 text-gray-600 border border-gray-200',
                                    'cancelled' => 'bg-red-100 text-red-700 border border-red-200',
                                    default => 'bg-gray-100 text-gray-600'
                                }
                            }}">
                                {{ $statusLabels[$booking->status] ?? $booking->status }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-[11px] text-gray-500 max-w-[150px] line-clamp-2 truncate" title="{{ $booking->notes }}">
                            {{ $booking->notes }}
                        </td>
                        <td class="px-3 py-3 whitespace-nowrap text-right text-xs font-medium">
                            <div class="flex justify-end gap-1">
                                <button wire:click="edit({{ $booking->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Sửa">
                                    <x-icon name="heroicon-o-pencil-square" class="h-4 w-4" />
                                </button>
                                <button wire:click="delete({{ $booking->id }})" wire:confirm="Bạn có chắc chắn muốn xóa không?" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Xóa">
                                    <x-icon name="heroicon-o-trash" class="h-4 w-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">{{ $bookings->links() }}</div>
    </x-ui.card>

    <!-- Modal -->
    <x-ui.modal name="showModal" :title="$editingBookingId ? 'Quản lý Booking: ' . ($bookings->find($editingBookingId)->room->code ?? '-') : 'Tạo Booking mới'" width="max-w-5xl">
        @php
            $logTotal = collect($usage_logs)->sum('total_amount');
            $basePrice = (float) str_replace(['.', ','], '', $price ?: 0);
            $totalDeposit = (float)str_replace(['.', ','], '', $deposit ?: 0) + (float)str_replace(['.', ','], '', $deposit_2 ?: 0) + (float)str_replace(['.', ','], '', $deposit_3 ?: 0);
            $isEditing = !empty($editingBookingId);
            
            // Tính tiền dịch vụ đang chọn (chưa chốt)
            $pendingServiceTotal = 0;
            foreach($all_services as $svc) {
                if(!empty($selected_services[$svc->id]['selected']) && isset($service_inputs[$svc->id])) {
                    $inp = $service_inputs[$svc->id];
                    $up = (float)str_replace(['.',','],'', (string)($inp['unit_price'] ?? '0'));
                    if($svc->type === 'meter') {
                        $pendingServiceTotal += max(0, ((float)($inp['end_index'] ?? 0) - (float)($inp['start_index'] ?? 0))) * $up;
                    } else {
                        $pendingServiceTotal += ((float)($inp['quantity'] ?? 1)) * $up;
                    }
                }
            }
            $grandTotal = $basePrice + $logTotal + $pendingServiceTotal;
        @endphp

        <form wire:submit="save" class="space-y-0">
            <div class="max-h-[75vh] overflow-y-auto p-4 -m-6 mb-0 space-y-3 bg-gray-50">

                {{-- HEADER: READ-ONLY KHI EDIT --}}
                @if($isEditing)
                    <div class="bg-slate-800 p-4 rounded-lg text-white">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl font-black">{{ $rooms->find($room_id)?->code ?? '-' }}</span>
                                    @php $statusSelectBg = ['pending' => 'bg-yellow-500', 'checked_in' => 'bg-green-500', 'checked_out' => 'bg-gray-400', 'cancelled' => 'bg-red-500']; @endphp
                                    <select wire:model.live="status" class="px-2 py-0.5 text-[10px] font-bold uppercase rounded text-white border-0 cursor-pointer {{ $statusSelectBg[$status] ?? 'bg-gray-500' }}">
                                        <option value="pending" class="bg-yellow-500">Chờ lấy</option>
                                        <option value="checked_in" class="bg-green-500">Đang ở</option>
                                        <option value="checked_out" class="bg-gray-400">Đã trả</option>
                                        <option value="cancelled" class="bg-red-500">Đã hủy</option>
                                    </select>
                                    <span class="text-[10px] text-slate-400 uppercase">{{ $price_type === 'month' ? 'Thuê hợp đồng' : 'Ngắn ngày' }}</span>
                                </div>
                                <div class="flex items-center gap-4 text-sm">
                                    <div><span class="text-slate-400 text-[10px] uppercase">Khách:</span> <span class="font-bold ml-1">{{ $customers->find($customer_id)?->name ?? $new_customer_name }}</span></div>
                                    <div class="border-l border-slate-600 pl-4"><span class="text-slate-400 text-[10px] uppercase">In:</span> <span class="font-semibold ml-1">{{ $check_in ? \Carbon\Carbon::parse($check_in)->format('d/m/Y') : '-' }}</span></div>
                                    <div class="border-l border-slate-600 pl-4"><span class="text-slate-400 text-[10px] uppercase">Out:</span> <span class="font-semibold ml-1">{{ $check_out ? \Carbon\Carbon::parse($check_out)->format('d/m/Y') : 'Hợp đồng' }}</span></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div><span class="text-slate-400 text-[10px] uppercase">Tiền phòng:</span> <span class="font-bold ml-1">{{ number_format($basePrice, 0, ',', '.') }}đ</span></div>
                                <div><span class="text-slate-400 text-[10px] uppercase">Đã cọc:</span> <span class="font-bold text-green-400 ml-1">{{ number_format($totalDeposit, 0, ',', '.') }}đ</span></div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- TẠO MỚI: Form đầy đủ --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-indigo-600 p-3 rounded-lg text-white"><p class="text-[10px] uppercase font-bold opacity-80">Tổng tiền</p><p class="text-xl font-black">{{ number_format($grandTotal, 0, ',', '.') }}đ</p></div>
                        <div class="bg-green-600 p-3 rounded-lg text-white"><p class="text-[10px] uppercase font-bold opacity-80">Đã thu</p><p class="text-xl font-black">{{ number_format($totalDeposit, 0, ',', '.') }}đ</p></div>
                        <div class="bg-orange-500 p-3 rounded-lg text-white"><p class="text-[10px] uppercase font-bold opacity-80">Còn lại</p><p class="text-xl font-black">{{ number_format($grandTotal - $totalDeposit, 0, ',', '.') }}đ</p></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-white p-3 rounded-lg border border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-[10px] font-black text-gray-400 uppercase">Khách hàng</h4>
                                <button type="button" wire:click="$set('activeTab', '{{ $activeTab === 'existing' ? 'new' : 'existing' }}')" class="text-[9px] font-bold py-0.5 px-1.5 rounded bg-gray-100 text-gray-500 uppercase hover:bg-blue-600 hover:text-white">{{ $activeTab === 'existing' ? '+ Mới' : '← Chọn' }}</button>
                            </div>
                            @if($activeTab === 'existing')
                                <div class="space-y-1.5">
                                    <select wire:model.live="customer_id" class="w-full rounded border-gray-200 p-2 text-sm font-semibold"><option value="">-- Chọn khách hàng --</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>@endforeach</select>
                                    @error('customer_id')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    
                                    @if($customer_id)
                                        <div class="relative mt-2">
                                            <label class="text-[9px] text-gray-400 uppercase font-bold mb-1 block">Quốc tịch</label>
                                            <x-ui.select-search 
                                                wire:model.live="new_customer_nationality" 
                                                :options="$this->getFormattedCountries()"
                                                placeholder="Tìm quốc tịch (VNM, USA...)"
                                            />
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="space-y-1.5">
                                    <input type="text" wire:model="new_customer_name" placeholder="Họ tên *" class="w-full rounded border-gray-200 p-2 text-sm font-semibold">
                                    @error('new_customer_name')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                                    <div class="grid grid-cols-2 gap-1.5">
                                        <input type="text" wire:model="new_customer_phone" placeholder="SĐT" class="rounded border-gray-200 p-2 text-sm">
                                        <select wire:model="new_customer_gender" class="rounded border-gray-200 p-2 text-sm">
                                            <option value="">Giới tính</option>
                                            <option value="male">Nam</option>
                                            <option value="female">Nữ</option>
                                            <option value="other">Khác</option>
                                        </select>
                                        <input type="text" wire:model="new_customer_identity" placeholder="CMND/Passport" class="rounded border-gray-200 p-2 text-sm col-span-2">
                                        <div class="col-span-2">
                                            <x-ui.select-date wire:model="new_customer_birthday" label="Ngày sinh" />
                                        </div>
                                    </div>
                                    <div class="relative mt-2">
                                        <label class="text-[9px] text-gray-400 uppercase font-bold mb-1 block">Quốc tịch</label>
                                        <x-ui.select-search 
                                            wire:model.live="new_customer_nationality" 
                                            :options="$this->getFormattedCountries()"
                                            placeholder="Tìm quốc tịch (VNM, USA...)"
                                        />
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Additional Guests --}}
                        <div class="bg-white p-3 rounded-lg border border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-[10px] font-black text-gray-400 uppercase">Người ở cùng</h4>
                                <button type="button" wire:click="addGuest" class="text-[9px] bg-slate-600 text-white px-2 py-0.5 rounded font-bold hover:bg-slate-700">+ Thêm</button>
                            </div>
                            <div class="space-y-1.5">
                                @foreach($additional_guests as $index => $guest)
                                    <div class="flex items-center gap-2">
                                        <input type="text" wire:model="additional_guests.{{ $index }}.name" placeholder="Tên" class="flex-1 rounded border-gray-200 p-1.5 text-xs">
                                        <input type="text" wire:model="additional_guests.{{ $index }}.identity" placeholder="CMND" class="w-24 rounded border-gray-200 p-1.5 text-xs">
                                        <button type="button" wire:click="removeGuest({{ $index }})" class="text-red-500"><x-icon name="heroicon-o-trash" class="h-4 w-4" /></button>
                                    </div>
                                @endforeach
                                @if(empty($additional_guests))
                                    <p class="text-[9px] text-gray-400 italic text-center">Không có người ở cùng</p>
                                @endif
                            </div>
                        </div>

                        <div class="bg-white p-3 rounded-lg border border-gray-200">
                            <h4 class="text-[10px] font-black text-gray-400 uppercase mb-2">Phòng & Trạng thái</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <select wire:model.live="room_id" class="w-full rounded border-gray-200 p-2 text-sm font-bold"><option value="">-- Phòng --</option>@foreach($rooms as $r)<option value="{{ $r->id }}">{{ $r->code }}</option>@endforeach</select>
                                @php $statusBg = ['pending' => 'bg-yellow-100 border-yellow-300', 'checked_in' => 'bg-green-100 border-green-300', 'checked_out' => 'bg-gray-100 border-gray-300', 'cancelled' => 'bg-red-100 border-red-300']; @endphp
                                <select wire:model="status" class="w-full rounded border-2 p-2 text-sm font-bold {{ $statusBg[$status] ?? 'border-gray-200' }}"><option value="pending">Chờ lấy</option><option value="checked_in">Nhận phòng</option><option value="checked_out">Trả phòng</option><option value="cancelled">Hủy</option></select>
                                <div class="col-span-2 flex items-center gap-3">
                                    <div class="flex-1 flex p-0.5 bg-gray-200/50 rounded-lg text-center overflow-hidden border border-gray-200">
                                        <button type="button" wire:click="$set('price_type', 'day')" class="flex-1 py-1.5 rounded-md text-xs font-bold transition-all {{ $price_type === 'day' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">Ngày</button>
                                        <button type="button" wire:click="$set('price_type', 'month')" class="flex-1 py-1.5 rounded-md text-xs font-bold transition-all {{ $price_type === 'month' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">Tháng</button>
                                    </div>
                                    <div class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 rounded-lg border border-blue-200 h-full">
                                        <input type="checkbox" wire:model.live="is_contract" id="is_contract_idx" class="rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                                        <label for="is_contract_idx" class="text-[10px] font-black text-blue-700 cursor-pointer uppercase tracking-tight">Hợp đồng</label>
                                    </div>
                                </div>
                            </div>
                            @error('room_id')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="bg-white p-3 rounded-lg border border-gray-200">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase mb-2">Thời gian & Giá</h4>
                        <div class="grid grid-cols-6 gap-2">
                            <x-ui.select-date wire:model="check_in" label="Check-in" />
                            <x-ui.select-date wire:model="check_out" label="Check-out" />
                            <div><label class="text-[9px] text-blue-500 uppercase font-bold block mb-0.5">Đơn giá</label><input type="text" wire:model.blur="unit_price" class="w-full rounded border-gray-200 p-1.5 text-sm font-bold" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"></div>
                            <div><label class="text-[9px] text-blue-500 uppercase font-bold block mb-0.5">Tổng tiền</label><input type="text" wire:model.blur="price" class="w-full rounded border-blue-300 bg-blue-50 p-1.5 text-sm font-bold text-blue-600" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">@error('price')<span class="text-red-500 text-[10px]">{{ $message }}</span>@enderror</div>
                            <div><label class="text-[9px] text-gray-400 uppercase font-bold block mb-0.5">Cọc L1</label><input type="text" wire:model.blur="deposit" class="w-full rounded border-gray-200 p-1.5 text-sm font-semibold text-indigo-600" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"></div>
                            <div><label class="text-[9px] text-gray-400 uppercase font-bold block mb-0.5">Cọc L2/L3</label><div class="flex gap-1"><input type="text" wire:model.blur="deposit_2" class="w-1/2 rounded border-gray-200 p-1.5 text-sm font-semibold text-indigo-600" placeholder="L2" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"><input type="text" wire:model.blur="deposit_3" class="w-1/2 rounded border-gray-200 p-1.5 text-sm font-semibold text-indigo-600" placeholder="L3" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"></div></div>
                        </div>
                    </div>
                @endif

                {{-- ===== FLASH MESSAGES FOR EMAIL ===== --}}
                @if (session()->has('info'))
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2 mb-3">
                        <x-icon name="heroicon-o-information-circle" class="h-4 w-4 flex-shrink-0" /> {{ session('info') }}
                    </div>
                @endif
                @if (session()->has('warning'))
                    <div class="bg-amber-50 border border-amber-200 text-amber-700 px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2 mb-3">
                        <x-icon name="heroicon-o-exclamation-triangle" class="h-4 w-4 flex-shrink-0" /> {{ session('warning') }}
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg text-xs font-medium flex items-center gap-2 mb-3">
                        <x-icon name="heroicon-o-x-circle" class="h-4 w-4 flex-shrink-0" /> {{ session('error') }}
                    </div>
                @endif

                {{-- ===== LỊCH SỬ CÁC KỲ ĐÃ CHỐT ===== --}}
                @if(count($usage_logs) > 0)
                    @php
                        $logsByPeriod = collect($usage_logs)->groupBy(function($log) {
                            return \Carbon\Carbon::parse($log['billing_date'])->format('m/Y');
                        });
                        // Get all unique service names
                        $allServiceNames = collect($usage_logs)->pluck('service_name')->unique()->values();
                    @endphp
                    <div class="bg-white rounded-lg border border-blue-200 p-3 mb-3">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-xs font-black text-blue-700 flex items-center gap-2">
                                <x-icon name="heroicon-o-clock" class="h-4 w-4"/>
                                📊 Lịch sử các kỳ đã chốt ({{ $logsByPeriod->count() }} kỳ)
                            </h4>
                            {{-- Export Invoice Button --}}
                            @if($editingBookingId)
                                <div class="flex items-center gap-2">
                                    @php
                                        $unsentCount = collect($usage_logs)->whereNull('email_sent_at')->count();
                                        $invoiceCustomer = $customers->find($customer_id);
                                    @endphp
                                    @if($invoiceCustomer && $invoiceCustomer->email)
                                        <span class="text-[10px] text-gray-500">→ {{ $invoiceCustomer->email }} · {{ $unsentCount }} chưa gửi</span>
                                    @else
                                        <span class="text-[10px] text-amber-600">⚠ Không có email</span>
                                    @endif
                                    <button type="button" wire:click="exportInvoice" wire:loading.attr="disabled" wire:target="exportInvoice"
                                        class="px-3 py-1.5 bg-indigo-600 text-white text-[10px] font-black uppercase rounded-lg hover:bg-indigo-700 hover:shadow-[0_0_15px_rgba(79,70,229,0.4)] shadow-sm active:scale-95 transition-all flex items-center gap-1.5 disabled:opacity-50">
                                        <span wire:loading.remove wire:target="exportInvoice">
                                            <x-icon name="heroicon-o-paper-airplane" class="h-3 w-3" />
                                        </span>
                                        <span wire:loading wire:target="exportInvoice">
                                            <svg class="animate-spin h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        </span>
                                        Xuất & Gửi HĐ
                                    </button>
                                </div>
                            @endif
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border-collapse">
                                <thead class="bg-slate-700 text-white">
                                    <tr>
                                        <th class="border border-slate-600 px-3 py-2 text-left font-bold whitespace-nowrap">📅 Kỳ</th>
                                        @foreach($allServiceNames as $serviceName)
                                            <th class="border border-slate-600 px-2 py-2 text-center font-bold whitespace-nowrap">{{ $serviceName }}</th>
                                        @endforeach
                                        <th class="border border-slate-600 px-2 py-2 text-center font-bold whitespace-nowrap bg-blue-900">💰 Phòng</th>
                                        <th class="border border-slate-600 px-3 py-2 text-center font-bold whitespace-nowrap bg-yellow-600">🧾 TỔNG</th>
                                        <th class="border border-slate-600 px-2 py-2 text-center font-bold whitespace-nowrap">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logsByPeriod as $period => $logs)
                                        <tr class="hover:bg-gray-50">
                                            <!-- Kỳ -->
                                            <td class="border border-gray-200 px-3 py-2 font-bold text-blue-700 whitespace-nowrap">
                                                Tháng {{ explode('/', $period)[0] }}/{{ explode('/', $period)[1] }}
                                            </td>
                                            
                                            <!-- Các dịch vụ -->
                                            @foreach($allServiceNames as $serviceName)
                                                @php
                                                    $serviceLog = $logs->firstWhere('service_name', $serviceName);
                                                @endphp
                                                <td class="border border-gray-200 px-2 py-2 text-center {{ $serviceLog ? 'bg-green-50' : 'bg-gray-50' }}">
                                                    @if($serviceLog)
                                                        <div class="space-y-0.5">
                                                            @if(!empty($serviceLog['email_sent_at']))
                                                                <div class="flex justify-center mb-0.5">
                                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-green-50 text-green-600 rounded text-[9px] font-bold border border-green-100" title="Đã gửi lúc {{ $serviceLog['email_sent_at'] }}">
                                                                        <x-icon name="heroicon-s-check-circle" class="h-2.5 w-2.5" /> Đã gửi
                                                                    </span>
                                                                </div>
                                                            @endif
                                                            @if($serviceLog['type'] === 'meter')
                                                                <div class="text-[9px] text-gray-500">
                                                                    {{ $serviceLog['start_index'] }}→{{ $serviceLog['end_index'] }}
                                                                </div>
                                                            @elseif($serviceLog['type'] !== 'manual')
                                                                <div class="text-[9px] text-gray-500">×{{ $serviceLog['quantity'] }}</div>
                                                            @endif
                                                            <div class="font-bold {{ $serviceLog['type'] === 'manual' ? 'text-indigo-600' : 'text-green-600' }}">
                                                                {{ number_format($serviceLog['total_amount'], 0, ',', '.') }}đ
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-gray-300 text-xs">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            
                                            <!-- Tiền phòng -->
                                            <td class="border border-gray-200 px-2 py-2 text-center bg-blue-50">
                                                <div class="font-black text-blue-600">
                                                    {{ number_format($basePrice, 0, ',', '.') }}đ
                                                </div>
                                            </td>
                                            
                                            <!-- Tổng kỳ -->
                                            @php
                                                $periodTotal = $logs->sum('total_amount') + $basePrice;
                                            @endphp
                                            <td class="border border-gray-200 px-3 py-2 text-center bg-yellow-50">
                                                <div class="font-black text-yellow-700 text-sm">
                                                    {{ number_format($periodTotal, 0, ',', '.') }}đ
                                                </div>
                                            </td>
                                            
                                            <!-- Thao tác -->
                                            <td class="border border-gray-200 px-2 py-2 text-center">
                                                <div class="flex items-center justify-center gap-2">
                                                    <button type="button" wire:click="viewPeriodInvoice('{{ $period }}')" 
                                                            class="text-blue-500 hover:text-blue-700 transition-colors" 
                                                            title="Xem hóa đơn kỳ {{ $period }}">
                                                        <x-icon name="heroicon-o-document-text" class="h-4 w-4 inline"/>
                                                    </button>
                                                    <button type="button" wire:click="removePeriodLogs('{{ $period }}')" 
                                                            class="text-red-400 hover:text-red-600 transition-colors" 
                                                            title="Xóa toàn bộ kỳ {{ $period }}">
                                                        <x-icon name="heroicon-o-trash" class="h-4 w-4 inline"/>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- ===== 2 CỘT: DỊCH VỤ | BẢNG TỔNG CHI PHÍ ===== --}}
                <div class="grid grid-cols-5 gap-3">
                    <!-- CỘT TRÁI: Chọn dịch vụ (1/5) -->
                    <div class="col-span-2 bg-white rounded-lg border border-gray-200 p-3">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase mb-2">Chọn dịch vụ</h4>
                        <div class="space-y-1 max-h-64 overflow-y-auto">
                            @foreach($all_services as $service)
                                @php $isSelected = !empty($selected_services[$service->id]['selected']); @endphp
                                <div wire:click="toggleService({{ $service->id }})" class="p-2 rounded border cursor-pointer transition-all flex items-center gap-2 {{ $isSelected ? 'border-blue-500 bg-blue-50' : 'border-gray-100 hover:border-gray-300' }}">
                                    <div class="w-4 h-4 rounded-full border flex items-center justify-center flex-shrink-0 {{ $isSelected ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300' }}">
                                        @if($isSelected) <x-icon name="heroicon-s-check" class="h-2.5 w-2.5" /> @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-bold text-gray-900 truncate">{{ $service->name }}</p>
                                        <p class="text-[9px] text-gray-400">{{ number_format($service->unit_price, 0, ',', '.') }}đ/{{ $service->unit_name }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- CỘT PHẢI: Bảng Tổng chi phí (4/5) -->
                    <div class="col-span-3 bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <div class="bg-slate-100 px-3 py-2 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <h4 class="text-[10px] font-black text-slate-600 uppercase">Bảng Tổng Chi Phí</h4>
                                <div class="flex items-center gap-2">
                                    <label class="text-[10px] font-bold text-slate-600">📅 NGÀY CHỐT KỲ NÀY:</label>
                                    <x-ui.select-date wire:model="global_billing_date" />
                                </div>
                            </div>
                        </div>
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 text-gray-500">
                                <tr>
                                    <th class="px-3 py-2 text-left font-bold uppercase text-[10px]">Hạng mục</th>
                                    <th class="px-2 py-2 text-center font-bold uppercase text-[10px] w-20">Đơn giá</th>
                                    <th class="px-2 py-2 text-center font-bold uppercase text-[10px] w-28">Số liệu</th>
                                    <th class="px-3 py-2 text-right font-bold uppercase text-[10px] w-24">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <!-- Tiền phòng -->
                                @php
                                    $start = $check_in ? \Carbon\Carbon::parse($check_in) : null;
                                    $end = $check_out ? \Carbon\Carbon::parse($check_out) : null;
                                    $nightsCount = ($start && $end) ? $start->diffInDays($end) : 0;
                                    if($price_type === 'day') $nightsCount = max(1, $nightsCount);
                                    $uPrice = (float)str_replace(['.',','],'',$unit_price ?: 0);
                                @endphp
                                <tr class="bg-blue-50/50">
                                    <td class="px-3 py-2">
                                        <div class="font-bold text-gray-900">💰 {{ $price_type === 'month' ? 'Tiền phòng (Hợp đồng)' : 'Tiền phòng (Ngày)' }}</div>
                                        <div class="text-[10px] text-gray-500 font-medium italic">({{ $nightsCount }} đêm x {{ number_format($uPrice, 0, ',', '.') }}đ{{ $price_type === 'month' ? ' / 30' : '' }})</div>
                                    </td>
                                    <td class="px-2 py-2 text-center text-gray-400">-</td>
                                    <td class="px-2 py-2 text-center text-gray-400">-</td>
                                    <td class="px-3 py-2 text-right font-black text-blue-600">{{ number_format($basePrice, 0, ',', '.') }}đ</td>
                                </tr>

                                <!-- Liệt kê chi tiết các dịch vụ đã chốt trong lịch sử -->
                                @foreach(collect($usage_logs)->where('type', '!=', 'deduction') as $log)
                                    <tr class="bg-green-50/30 border-y border-green-100/50">
                                        <td class="px-3 py-2">
                                            <div class="font-bold text-green-700">✅ {{ $log['service_name'] }} (Đã chốt)</div>
                                            <div class="text-[9px] text-green-600 italic">Ngày chốt: {{ \Carbon\Carbon::parse($log['billing_date'])->format('d/m/Y') }}</div>
                                        </td>
                                        <td class="px-2 py-2 text-center text-gray-500">{{ number_format($log['unit_price'], 0, ',', '.') }}</td>
                                        <td class="px-2 py-2 text-center text-gray-500">
                                            @if($log['type'] === 'meter')
                                                {{ $log['start_index'] }}→{{ $log['end_index'] }}
                                            @else
                                                x{{ $log['quantity'] }}
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-right font-black text-green-600">{{ number_format($log['total_amount'], 0, ',', '.') }}đ</td>
                                    </tr>
                                @endforeach
                                
                                <!-- Các dịch vụ đã chọn -->
                                @foreach($all_services as $service)
                                    @if(!empty($selected_services[$service->id]['selected']) && isset($service_inputs[$service->id]))
                                        @php 
                                            $inp = $service_inputs[$service->id] ?? [];
                                            $up = (float)str_replace(['.',','],'', (string)($inp['unit_price'] ?? '0'));
                                            
                                            // Tìm số cuối gần nhất từ lịch sử
                                            $lastLog = collect($usage_logs)->where('service_id', $service->id)->sortByDesc('billing_date')->first();
                                            $suggestIndex = $lastLog ? ($lastLog['end_index'] ?? 0) : 0;
                                            
                                            if($service->type === 'meter') {
                                                $startIdx = (float)($inp['start_index'] ?? 0);
                                                $endIdx = (float)($inp['end_index'] ?? 0);
                                                $usage = max(0, $endIdx - $startIdx);
                                                $amount = $usage * $up;
                                            } else {
                                                $qty = (float)($inp['quantity'] ?? 1);
                                                $amount = $qty * $up;
                                            }
                                        @endphp
                                        <tr wire:key="row-{{ $service->id }}">
                                            <td class="px-3 py-2">
                                                <div class="font-semibold text-gray-800">⚡ {{ $service->name }}</div>
                                                @if($service->type === 'meter' && $suggestIndex > 0)
                                                    <div class="text-[9px] text-blue-500 mt-0.5">💡 Số gần nhất: <span class="font-bold">{{ number_format($suggestIndex, 0, ',', '.') }}</span></div>
                                                @endif
                                            </td>
                                            <td class="px-2 py-2 text-center">
                                                <input type="text" wire:model.blur="service_inputs.{{ $service->id }}.unit_price" class="w-full text-xs border-0 bg-transparent rounded p-1 text-center font-semibold" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                                            </td>
                                            <td class="px-2 py-2 text-center">
                                                @if($service->type === 'meter')
                                                    <div class="flex items-center gap-1 justify-center">
                                                        <div class="relative">
                                                            <input type="number" wire:model.live="service_inputs.{{ $service->id }}.start_index" class="w-16 text-xs border-2 border-gray-400 bg-gray-50 rounded p-1 text-center font-semibold focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="{{ $suggestIndex > 0 ? $suggestIndex : 'Đầu' }}" title="Số đầu kỳ">
                                                        </div>
                                                        <span class="text-gray-400 text-xs">→</span>
                                                        <input type="number" wire:model.live="service_inputs.{{ $service->id }}.end_index" class="w-16 text-xs border-2 border-blue-500 bg-blue-100 rounded p-1 text-center font-bold focus:border-blue-600 focus:ring-2 focus:ring-blue-300" placeholder="Cuối" title="Số cuối kỳ">
                                                    </div>
                                                @else
                                                    <input type="number" wire:model.live="service_inputs.{{ $service->id }}.quantity" class="w-14 text-xs border-2 border-gray-400 bg-gray-50 rounded p-1 text-center font-semibold mx-auto focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-right font-bold text-indigo-600">{{ number_format($amount, 0, ',', '.') }}đ</td>
                                        </tr>
                                    @endif
                                @endforeach

                                <!-- Phụ thu nhập mới (luôn ở cuối) -->
                                <tr class="bg-indigo-50/50 border-t-2 border-indigo-200">
                                    <td class="px-3 py-2 font-bold text-indigo-700">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm">➕ Phụ thu</span>
                                            <button type="button" wire:click="$toggle('showExtraNights')" class="text-[9px] bg-orange-100 text-orange-600 px-1.5 py-0.5 rounded border border-orange-200 hover:bg-orange-200">+ Đêm phòng</button>
                                        </div>
                                    </td>
                                    <td class="px-2 py-2 text-center">
                                        <span class="text-[9px] text-gray-400">-</span>
                                    </td>
                                    <td class="px-2 py-2" colspan="2">
                                        @if($showExtraNights ?? false)
                                            <div class="flex items-center gap-1 mb-2 bg-orange-50 p-1.5 rounded border border-orange-100">
                                                <input type="number" wire:model.live="extra_nights" class="w-12 rounded border-orange-200 p-1 text-[10px] bg-white text-center" placeholder="Đêm">
                                                <span class="text-[10px] text-orange-400">x</span>
                                                <input type="text" wire:model.blur="extra_night_price" class="flex-1 rounded border-orange-200 p-1 text-[10px] bg-white" placeholder="Giá" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                                                <button type="button" wire:click="addExtraNightsSurcharge" class="bg-orange-600 text-white rounded text-[10px] px-2 py-1">+</button>
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-2">
                                            <input type="text" wire:model="manual_fee_notes" class="flex-1 rounded border-indigo-200 p-1 text-xs bg-white" placeholder="Lý do...">
                                            <input type="text" wire:model.blur="manual_fee_amount" class="w-24 rounded border-indigo-200 p-1 text-xs font-bold bg-white text-right" placeholder="Số tiền" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                                            <button type="button" wire:click="addManualSurcharge" class="bg-indigo-600 text-white rounded text-[9px] font-bold px-2 py-1 hover:bg-indigo-700">+</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                                <!-- Khấu trừ cọc (Tự động) -->
                                @if(!empty($deposits) && !$is_contract)
                                    <tr class="bg-gray-50/50">
                                        <td colspan="4" class="px-3 py-1 text-[9px] font-black text-gray-500 uppercase bg-gray-100/50 border-y border-gray-200">
                                            KHẤU TRỪ TIỀN CỌC (TỰ ĐỘNG)
                                        </td>
                                    </tr>
                                    @foreach($deposits as $idx => $depState)
                                        <tr class="bg-green-50/30 border-t border-gray-100">
                                            <td class="px-3 py-2">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs font-bold text-green-700">
                                                        ✅ Tiền cọc (Lần {{ $idx }})
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-2 py-2 text-center text-gray-400">-</td>
                                            <td class="px-2 py-2 text-center text-gray-400">-</td>
                                            <td class="px-3 py-2 text-right font-bold text-green-600">
                                                -{{ number_format($depState['amount'], 0, ',', '.') }}đ
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>

                            @php
                                // Tính tổng tiền cọc đã áp dụng
                                $appliedDepositTotal = collect($deposits)->where('is_applied', true)->sum('amount');
                                $grandTotalAfterDeposit = $is_contract ? $grandTotal : ($grandTotal - $appliedDepositTotal);
                            @endphp
                            
                            <tfoot class="bg-slate-800 text-white">
                                <tr>
                                    <td colspan="3" class="px-3 py-2 text-right font-bold uppercase text-[11px]">CÒN LẠI:</td>
                                    <td class="px-3 py-2 text-right font-black text-lg">{{ number_format($grandTotalAfterDeposit, 0, ',', '.') }}đ</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- NÚT CHỐT + XEM HOÁ ĐƠN -->
                <div class="flex justify-end gap-2">
                    <button type="button" x-data x-on:click="$dispatch('open-bill-preview')" class="bg-indigo-600 text-white rounded-lg text-sm font-bold px-4 py-2 hover:bg-indigo-700 flex items-center gap-1">
                        <x-icon name="heroicon-o-document-text" class="h-4 w-4" /> Xem hoá đơn
                    </button>
                    @php $hasSel = collect($selected_services)->filter(fn($s) => !empty($s['selected']))->isNotEmpty(); @endphp
                    @if($hasSel)
                        <button type="button" wire:click="addAllServiceLogs" class="bg-green-600 text-white rounded-lg text-sm font-bold px-4 py-2 hover:bg-green-700 flex items-center gap-1">
                            <x-icon name="heroicon-o-check" class="h-4 w-4" /> Chốt dịch vụ
                        </button>
                    @endif
                </div>

                <!-- Ghi chú -->
                <div class="bg-white p-3 rounded-lg border border-gray-200">
                    <label class="text-[10px] text-gray-400 uppercase font-bold block mb-1">Ghi chú</label>
                    <textarea wire:model="notes" rows="2" class="w-full rounded border-gray-200 p-2 text-sm" placeholder="Ghi chú thêm..."></textarea>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="px-4 py-3 bg-white border-t border-gray-100 flex justify-end gap-2 items-center -mx-6 -mb-6 mt-4">
                @if($editingBookingId)
                    <button type="button" wire:click="viewConfirmation" class="px-4 py-2 bg-indigo-600 text-white rounded font-bold text-xs uppercase shadow-md hover:bg-indigo-700 flex items-center gap-1">
                        <x-icon name="heroicon-o-printer" class="h-4 w-4" /> In xác nhận
                    </button>
                @endif
                <button type="button" @click="show = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:bg-gray-100 rounded-lg">Huỷ</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white text-sm font-bold uppercase rounded-lg shadow-lg hover:bg-blue-700 flex items-center gap-2">
                    <x-icon name="heroicon-o-check" class="h-4 w-4" />{{ $editingBookingId ? 'Cập nhật' : 'Tạo mới' }}
                </button>
            </div>
        </form>
    </x-ui.modal>

    <!-- Bill Preview Modal -->
    <div x-data="{ showBill: false }" 
         x-on:open-bill-preview.window="showBill = true"
         x-show="showBill" 
         x-cloak
         class="fixed inset-0 z-[60] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen p-4">
            <!-- Backdrop -->
            <div x-show="showBill" x-on:click="showBill = false" class="fixed inset-0 bg-black/50"></div>
            
            <!-- Modal Content -->
            <div x-show="showBill" 
                 x-transition
                 class="relative bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                
                <!-- Close Button -->
                <button x-on:click="showBill = false" class="absolute top-4 right-4 z-10 bg-gray-100 hover:bg-gray-200 rounded-full p-2">
                    <x-icon name="heroicon-o-x-mark" class="h-5 w-5" />
                </button>

                <!-- Print Button -->
                <button onclick="window.print()" class="absolute top-4 right-16 z-10 bg-blue-600 hover:bg-blue-700 text-white rounded-full p-2">
                    <x-icon name="heroicon-o-printer" class="h-5 w-5" />
                </button>

                @php
                    $roomCode = $rooms->find($room_id)?->code ?? '-';
                    $customerName = $activeTab === 'existing' ? ($customers->find($customer_id)?->name ?? '-') : $new_customer_name;
                    $billingMonth = date('m.Y');
                    
                    // Tính toán các khoản
                    $roomPrice = (float)str_replace(['.',','],'', (string)($price ?? '0'));
                    
                    // Lấy điện, nước từ usage_logs + service_inputs
                    $electricLogs = collect($usage_logs)->where('service_name', 'like', '%Điện%')->merge(
                        collect($service_inputs)->filter(fn($i, $id) => 
                            !empty($selected_services[$id]['selected']) && 
                            ($all_services->find($id)?->name ?? '') == 'Điện'
                        )
                    );
                    $waterLogs = collect($usage_logs)->filter(fn($l) => str_contains(strtolower($l['service_name'] ?? ''), 'nước'));
                    
                    // Tính tổng Điện (dùng mb_strtolower cho Unicode)
                    $electricTotal = 0;
                    $electricStart = 0;
                    $electricEnd = 0;
                    $electricPrice = 0;
                    $electricUsage = 0;
                    foreach($all_services as $svc) {
                        $svcNameLower = mb_strtolower($svc->name, 'UTF-8');
                        if((str_contains($svcNameLower, 'điện') || str_contains($svcNameLower, 'dien') || $svc->id == 1) && !empty($selected_services[$svc->id]['selected']) && isset($service_inputs[$svc->id])) {
                            $inp = $service_inputs[$svc->id];
                            $electricStart = (float)($inp['start_index'] ?? 0);
                            $electricEnd = (float)($inp['end_index'] ?? 0);
                            $electricPrice = (float)str_replace(['.',','],'', (string)($inp['unit_price'] ?? '0'));
                            $electricUsage = max(0, $electricEnd - $electricStart);
                            $electricTotal = $electricUsage * $electricPrice;
                        }
                    }
                    
                    // Tính tổng Nước (hỗ trợ cả meter và fixed)
                    $waterTotal = 0;
                    $waterStart = 0;
                    $waterEnd = 0;
                    $waterPrice = 0;
                    $waterUsage = 0;
                    $waterQty = 0;
                    $waterIsMeter = false;
                    foreach($all_services as $svc) {
                        $svcNameLower = mb_strtolower($svc->name, 'UTF-8');
                        if((str_contains($svcNameLower, 'nước') || str_contains($svcNameLower, 'nuoc') || $svc->id == 2) && !empty($selected_services[$svc->id]['selected']) && isset($service_inputs[$svc->id])) {
                            $inp = $service_inputs[$svc->id];
                            $waterPrice = (float)str_replace(['.',','],'', (string)($inp['unit_price'] ?? '0'));
                            if($svc->type === 'meter') {
                                $waterIsMeter = true;
                                $waterStart = (float)($inp['start_index'] ?? 0);
                                $waterEnd = (float)($inp['end_index'] ?? 0);
                                $waterUsage = max(0, $waterEnd - $waterStart);
                                $waterTotal = $waterUsage * $waterPrice;
                            } else {
                                $waterQty = (float)($inp['quantity'] ?? 1);
                                $waterTotal = $waterQty * $waterPrice;
                            }
                        }
                    }
                    
                    // Các dịch vụ khác (không phải Điện/Nước)
                    $otherServicesTotal = 0;
                    $otherServicesList = [];
                    foreach($all_services as $svc) {
                        $svcNameLower = mb_strtolower($svc->name, 'UTF-8');
                        $isElectric = str_contains($svcNameLower, 'điện') || str_contains($svcNameLower, 'dien') || $svc->id == 1;
                        $isWater = str_contains($svcNameLower, 'nước') || str_contains($svcNameLower, 'nuoc') || $svc->id == 2;
                        if(!$isElectric && !$isWater && !empty($selected_services[$svc->id]['selected']) && isset($service_inputs[$svc->id])) {
                            $inp = $service_inputs[$svc->id];
                            $up = (float)str_replace(['.',','],'', (string)($inp['unit_price'] ?? '0'));
                            $qty = (float)($inp['quantity'] ?? 1);
                            $total = $qty * $up;
                            $otherServicesTotal += $total;
                            $otherServicesList[] = ['name' => $svc->name, 'total' => $total];
                        }
                    }
                    
                    // Phụ thu từ lịch sử (manual type)
                    $manualFeesTotal = collect($usage_logs)->where('type', 'manual')->sum('total_amount');
                    $loggedTotal = collect($usage_logs)->sum('total_amount');
                    $grandTotalBill = $roomPrice + $electricTotal + $waterTotal + $otherServicesTotal + $loggedTotal;
                    
                    // Tính tổng tiền cọc cho Bill
                    $billDep1 = (float)str_replace(['.',','],'', (string)($deposit ?? '0'));
                    $billDep2 = (float)str_replace(['.',','],'', (string)($deposit_2 ?? '0'));
                    $billDep3 = (float)str_replace(['.',','],'', (string)($deposit_3 ?? '0'));
                    $billTotalDeposit = $billDep1 + $billDep2 + $billDep3;
                    $billRemaining = $grandTotalBill - $billTotalDeposit;
                @endphp

                <!-- Bill Content -->
                <div class="p-10" style="font-family: 'Times New Roman', Times, serif;">
                    <!-- Header -->
                    <div class="flex justify-between items-start mb-6 pb-4 border-b-2 border-gray-800">
                        <div class="flex items-center gap-4">
                            <img src="{{ asset('logo.jpg') }}" alt="Logo" class="w-16 h-16 border-2 border-gray-800 rounded-full object-cover">
                            <div class="text-sm italic">Sala Apartment</div>
                        </div>
                        <div class="text-center flex-1 px-4">
                            <h1 class="text-sm font-bold uppercase">SALA APARTMENT AND HOTEL ĐÀ NẴNG</h1>
                            <p class="text-[11px] mt-1">Số điện thoại: 084 424 4567</p>
                            <p class="text-[11px]">Địa chỉ: 22 Lý Nhật Quang, Nại Hiên Đông, Sơn Trà, Đà Nẵng</p>
                        </div>
                        <img src="{{ asset('qr.jpg') }}" alt="QR Code" class="w-16 h-16 border border-gray-800 object-contain">
                    </div>

                    <!-- Title -->
                    <div class="text-center my-6">
                        <h2 class="text-lg font-bold">HÓA ĐƠN TIỀN PHÒNG/</h2>
                        <div class="text-base font-bold">ROOM BILL</div>
                    </div>

                    <!-- Info Box -->
                    <div class="flex justify-end mb-4">
                        <div class="border border-gray-800 px-4 py-2 text-right text-sm">
                            <p><em>Phòng/Room:</em> <strong>{{ $roomCode }}</strong></p>
                            <p><em>Check-in:</em> <strong>{{ $check_in ? \Carbon\Carbon::parse($check_in)->format('d/m/Y') : '-' }}</strong></p>
                            <p><em>Check-out:</em> <strong>{{ $check_out ? \Carbon\Carbon::parse($check_out)->format('d/m/Y') : '-' }}</strong></p>
                        </div>
                    </div>

                    <!-- Greeting -->
                    <div class="mb-4 text-sm">
                        <p>Kính gửi/ Dear <strong>{{ $customerName }}</strong>
                        @if(!empty($additional_guests))
                            và cộng sự/ and guests: <strong>{{ collect($additional_guests)->pluck('name')->join(', ') }}</strong>
                        @endif
                        </p>
                    </div>

                    <!-- Content -->
                    <div class="text-xs leading-relaxed mb-4">
                        <p>Xin chân thành cảm ơn quý khách đã chọn và sử dụng dịch vụ tại Sala Apartment and Hotel cho kỳ nghỉ của mình. Sala Apartment and Hotel kính gửi hóa đơn tiền phòng của quý khách như sau:</p>
                        <p class="mt-2">Thank you very much for choosing and using services at Sala Apartment and Hotel for your stay.</p>
                    </div>

                    <!-- Main Table -->
                    <table class="w-full border-collapse mb-4">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="border border-gray-800 p-2 text-xs font-bold text-center">Tiền phòng/<br>Room rental</th>
                                <th class="border border-gray-800 p-2 text-xs font-bold text-center">Nước/<br>Water</th>
                                <th class="border border-gray-800 p-2 text-xs font-bold text-center">Điện/<br>Electric</th>
                                @foreach($otherServicesList as $os)
                                    <th class="border border-gray-800 p-2 text-xs font-bold text-center">{{ $os['name'] }}</th>
                                @endforeach
                                @if($manualFeesTotal > 0)
                                    <th class="border border-gray-800 p-2 text-xs font-bold text-center">Phí khác/<br>Other</th>
                                @endif
                                @if($billDep1 > 0)
                                    <th class="border border-gray-800 p-2 text-xs font-bold text-center">Cọc 1/<br>Deposit 1</th>
                                @endif
                                @if($billDep2 > 0)
                                    <th class="border border-gray-800 p-2 text-xs font-bold text-center">Cọc 2/<br>Deposit 2</th>
                                @endif
                                @if($billDep3 > 0)
                                    <th class="border border-gray-800 p-2 text-xs font-bold text-center">Cọc 3/<br>Deposit 3</th>
                                @endif
                                <th class="border border-gray-800 p-2 text-xs font-bold text-center">CÒN LẠI/<br>REMAINING</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-gray-800 p-2 text-xs text-right">{{ number_format($roomPrice, 0, ',', '.') }}</td>
                                <td class="border border-gray-800 p-2 text-xs text-right">{{ number_format($waterTotal, 0, ',', '.') }}</td>
                                <td class="border border-gray-800 p-2 text-xs text-right">{{ number_format($electricTotal, 0, ',', '.') }}</td>
                                @foreach($otherServicesList as $os)
                                    <td class="border border-gray-800 p-2 text-xs text-right">{{ number_format($os['total'], 0, ',', '.') }}</td>
                                @endforeach
                                @if($manualFeesTotal > 0)
                                    <td class="border border-gray-800 p-2 text-xs text-right">{{ number_format($manualFeesTotal, 0, ',', '.') }}</td>
                                @endif
                                @if($billDep1 > 0)
                                    <td class="border border-gray-800 p-2 text-xs text-right text-green-600">-{{ number_format($billDep1, 0, ',', '.') }}</td>
                                @endif
                                @if($billDep2 > 0)
                                    <td class="border border-gray-800 p-2 text-xs text-right text-green-600">-{{ number_format($billDep2, 0, ',', '.') }}</td>
                                @endif
                                @if($billDep3 > 0)
                                    <td class="border border-gray-800 p-2 text-xs text-right text-green-600">-{{ number_format($billDep3, 0, ',', '.') }}</td>
                                @endif
                                <td class="border border-gray-800 p-2 text-sm text-right font-bold">{{ number_format($billRemaining, 0, ',', '.') }} VNĐ</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Electric Detail Table (if applicable) -->
                    @if($electricTotal > 0)
                        <div class="text-[10px] italic mb-2">* Chỉ số công tơ điện/Note: electronic index</div>
                        <table class="w-full border-collapse mb-4">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Số CTĐ đầu<br>Start Electronic index</th>
                                    <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Số CTĐ cuối<br>End Electronic index</th>
                                    <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Đơn giá/unit price<br>({{ number_format($electricPrice, 0, ',', '.') }} vnđ)</th>
                                    <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Tổng/Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-gray-800 p-2 text-xs text-center">{{ number_format($electricStart, 0, ',', '.') }}</td>
                                    <td class="border border-gray-800 p-2 text-xs text-center">{{ number_format($electricEnd, 0, ',', '.') }}</td>
                                    <td class="border border-gray-800 p-2 text-xs text-center">{{ number_format($electricUsage, 0, ',', '.') }} x {{ number_format($electricPrice, 0, ',', '.') }}</td>
                                    <td class="border border-gray-800 p-2 text-xs text-right font-semibold">{{ number_format($electricTotal, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @endif

                    <!-- Water Detail Table (if meter type) -->
                    @if($waterIsMeter && $waterTotal > 0)
                        <div class="text-[10px] italic mb-2">* Chỉ số công tơ nước/Note: water meter index</div>
                        <table class="w-full border-collapse mb-4">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Số CTN đầu<br>Start Water index</th>
                                    <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Số CTN cuối<br>End Water index</th>
                                    <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Đơn giá/unit price<br>({{ number_format($waterPrice, 0, ',', '.') }} vnđ)</th>
                                    <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Tổng/Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-gray-800 p-2 text-xs text-center">{{ number_format($waterStart, 0, ',', '.') }}</td>
                                    <td class="border border-gray-800 p-2 text-xs text-center">{{ number_format($waterEnd, 0, ',', '.') }}</td>
                                    <td class="border border-gray-800 p-2 text-xs text-center">{{ number_format($waterUsage, 0, ',', '.') }} x {{ number_format($waterPrice, 0, ',', '.') }}</td>
                                    <td class="border border-gray-800 p-2 text-xs text-right font-semibold">{{ number_format($waterTotal, 0, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @endif


                </div>
            </div>
        </div>
    </div>

    {{-- BOOKING CONFIRMATION PREVIEW MODAL --}}
    @if($showConfirmationModal && !empty($confirmation_data))
        <div class="fixed inset-0 z-[120] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm print:hidden" wire:click="closeConfirmationModal"></div>
                
                <div class="relative bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[95vh] overflow-y-auto p-0" style="font-family: 'Source Serif 4', Georgia, serif;">
                    <button wire:click="closeConfirmationModal" class="absolute top-4 right-4 z-10 bg-gray-100 hover:bg-gray-200 rounded-full p-2 print:hidden">
                        <x-icon name="heroicon-o-x-mark" class="h-5 w-5" />
                    </button>

                    <button onclick="printBookingConfirmation('confirmationBillContentIndex')" class="absolute top-4 right-16 z-10 bg-blue-600 hover:bg-blue-700 text-white rounded-full p-2">
                        <x-icon name="heroicon-o-printer" class="h-5 w-5" />
                    </button>

                    <style>
                        :root {
                          --gold: #b8975a;
                          --dark: #1a1a1a;
                          --mid: #444;
                          --light: #f8f5f0;
                          --border: #d4c9b0;
                          --accent: #8b7340;
                        }

                        .booking-confirmation-page {
                          background: #fff;
                          width: 100%;
                          padding: 80px 100px;
                          position: relative;
                          overflow: hidden;
                          color: var(--dark);
                        }

                        /* Watermark */
                        .booking-confirmation-page::before {
                          content: "S";
                          position: absolute;
                          font-family: 'Playfair Display', serif;
                          font-size: 400px;
                          color: rgba(184,151,90,0.04);
                          top: 50%;
                          left: 50%;
                          transform: translate(-50%, -50%);
                          pointer-events: none;
                          line-height: 1;
                        }



                        .header-lux {
                          display: flex;
                          align-items: center;
                          justify-content: space-between;
                          margin-bottom: 32px;
                          gap: 20px;
                        }

                        .logo-area {
                          display: flex;
                          align-items: center;
                          gap: 14px;
                          flex-shrink: 0;
                        }

                        .logo-img {
                          width: 100px;
                          height: auto;
                          object-fit: contain;
                        }

                        .hotel-info {
                          text-align: center;
                          flex: 1;
                        }

                        .hotel-name {
                          font-family: 'Playfair Display', serif;
                          font-size: 20px;
                          font-weight: 700;
                          color: var(--dark);
                          letter-spacing: 0.05em;
                          text-transform: uppercase;
                        }

                        .hotel-contact {
                          font-size: 12.5px;
                          color: var(--mid);
                          line-height: 2;
                          margin-top: 4px;
                        }

                        .qr-box-lux {
                          width: 64px;
                          height: 64px;
                          border: 1.5px solid var(--border);
                          flex-shrink: 0;
                          display: flex;
                          align-items: center;
                          justify-content: center;
                          overflow: hidden;
                        }

                        .divider-lux {
                          display: flex;
                          align-items: center;
                          gap: 12px;
                          margin: 4px 0 28px;
                        }
                        .divider-line { flex: 1; height: 1px; background: var(--border); }
                        .divider-diamond {
                          width: 8px; height: 8px;
                          background: var(--gold);
                          transform: rotate(45deg);
                          flex-shrink: 0;
                        }

                        .title-block {
                          text-align: center;
                          margin-bottom: 28px;
                        }

                        .title-label {
                          font-family: 'Playfair Display', serif;
                          font-size: 26px;
                          font-weight: 700;
                          letter-spacing: 0.15em;
                          text-transform: uppercase;
                          color: var(--dark);
                        }

                        .title-sub {
                          font-size: 12.5px;
                          color: var(--mid);
                          letter-spacing: 0.08em;
                          margin-top: 4px;
                          font-style: italic;
                        }

                        .intro {
                          font-size: 13.5px;
                          line-height: 1.8;
                          text-align: center;
                          color: var(--mid);
                          margin-bottom: 28px;
                          font-style: italic;
                        }

                        .details-table {
                          width: 100%;
                          border-collapse: collapse;
                          font-size: 13.5px;
                          margin-bottom: 28px;
                        }

                        .details-table td {
                          padding: 14px 18px;
                          border: 1px solid var(--border);
                          vertical-align: top;
                          line-height: 1.6;
                        }

                        .details-table .label {
                          color: var(--mid);
                          font-weight: 300;
                          white-space: nowrap;
                          width: 25%;
                        }

                        .details-table .value {
                          font-weight: 600;
                          color: var(--dark);
                          letter-spacing: 0.02em;
                        }

                        .price-note {
                          font-size: 11.5px;
                          color: var(--mid);
                          font-weight: 300;
                          font-style: italic;
                          margin-top: 4px;
                        }

                        .paid-badge {
                          display: inline-block;
                          background: #e8f4ec;
                          color: #2e7d4f;
                          border: 1px solid #a8d5b5;
                          padding: 1px 10px;
                          border-radius: 20px;
                          font-size: 11px;
                          letter-spacing: 0.06em;
                          text-transform: uppercase;
                          font-weight: 600;
                          margin-left: 8px;
                          vertical-align: middle;
                          font-style: normal;
                        }

                        .signature-lux {
                          text-align: right;
                          margin-top: 32px;
                          padding-top: 20px;
                        }

                        .sig-title {
                          font-size: 12px;
                          letter-spacing: 0.12em;
                          text-transform: uppercase;
                          color: var(--mid);
                          font-weight: 300;
                        }

                        .sig-name {
                          font-family: 'Playfair Display', serif;
                          font-size: 16px;
                          color: var(--dark);
                          margin-top: 4px;
                          font-style: italic;
                        }

                        .sig-company {
                          font-size: 12px;
                          color: var(--gold);
                          letter-spacing: 0.1em;
                          text-transform: uppercase;
                          margin-top: 2px;
                        }

                        .sig-line {
                          width: 160px;
                          height: 1px;
                          background: var(--border);
                          margin: 14px 0 8px auto;
                        }

                    </style>

                    <div class="booking-confirmation-page" id="confirmationBillContentIndex">


                        <!-- Header -->
                        <div class="header-lux">
                            <div class="logo-area">
                                <img src="{{ asset('logo.jpg') }}" alt="Sala Logo" class="logo-img">
                            </div>

                            <div class="hotel-info">
                                <div class="hotel-name">Sala Apartment and Hotel Da Nang</div>
                                <div class="hotel-contact">
                                    Hotline: +84 84 424 4567<br>
                                    Address: 16 Ly Nhat Quang, Son Tra district, Da Nang city
                                </div>
                            </div>

                            <div class="qr-box-lux">
                                <img src="{{ asset('qr.jpg') }}" alt="QR" class="w-full h-full object-contain">
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="divider-lux">
                            <div class="divider-line"></div>
                            <div class="divider-diamond"></div>
                            <div class="divider-line"></div>
                        </div>

                        <!-- Title -->
                        <div class="title-block">
                            <div class="title-label">Booking Confirmation</div>
                        </div>

                        <!-- Intro -->
                        <p class="intro">
                            Thank you for choosing and using services at Sala Apartment and Hotel for your trip.<br>
                            Sala Apartment and Hotel would like to confirm your booking:
                        </p>

                        <!-- Details Table -->
                        <table class="details-table">
                            <tr>
                                <td class="label">Names of the guest</td>
                                <td class="value">
                                    <div class="font-black">{{ $confirmation_data['customer_name'] }}</div>
                                    @if(!empty($confirmation_data['additional_guests']))
                                        <div class="text-[11px] text-gray-500 font-medium mt-1">
                                            Stay with: {{ collect($confirmation_data['additional_guests'])->pluck('name')->join(', ') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="label">Phone number</td>
                                <td class="value">{{ $confirmation_data['customer_phone'] }}</td>
                            </tr>
                            <tr>
                                <td class="label">Room</td>
                                <td class="value">{{ $confirmation_data['room_code'] }}</td>
                                <td class="label">Number of guests</td>
                                <td class="value">{{ sprintf('%02d', count($confirmation_data['additional_guests'] ?? []) + 1) }}</td>
                            </tr>
                            <tr>
                                <td class="label">Arrival date</td>
                                <td class="value">{{ $confirmation_data['check_in'] }}</td>
                                <td class="label">Term of stay</td>
                                <td class="value">{{ $confirmation_data['term_of_stay'] }}</td>
                            </tr>
                            <tr class="full-row">
                                <td class="label">Price of the room</td>
                                <td colspan="3" class="value">
                                    {{ $confirmation_data['unit_price'] }} VND / {{ $confirmation_data['price_type'] }}
                                    <div class="price-note">(not included deposit fee, electric fee and water fee)</div>
                                </td>
                            </tr>
                            <tr class="full-row">
                                <td class="label">Deposit money</td>
                                <td colspan="3" class="value">
                                    {{ number_format($confirmation_data['total_deposit'], 0, ',', '.') }} VND
                                    @if($confirmation_data['total_deposit'] > 0)
                                        <span class="paid-badge">✓ Paid</span>
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <!-- Divider -->
                        <div class="divider-lux">
                            <div class="divider-line"></div>
                            <div class="divider-diamond"></div>
                            <div class="divider-line"></div>
                        </div>

                        <!-- Signature -->
                        <div class="signature-lux">
                            <div class="sig-title">Apartment Confirmation</div>
                            <div class="sig-line"></div>
                            <div class="sig-name">Ngo Thi Phuong Thao</div>
                            <div class="sig-company">Sala Apartment</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- INVOICE MODAL - Matching Bill Preview Format --}}
    @if($showInvoiceModal && !empty($invoice_data))
        <div x-data="{ showInvoice: @entangle('showInvoiceModal') }"
             x-show="showInvoice"
             x-cloak
             class="fixed inset-0 z-[60] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <!-- Backdrop -->
                <div x-show="showInvoice" x-on:click="$wire.closeInvoiceModal()" class="fixed inset-0 bg-black/50"></div>
                
                <!-- Modal Content -->
                <div x-show="showInvoice"
                     x-transition
                     class="relative bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                    
                    <!-- Close Button -->
                    <button x-on:click="$wire.closeInvoiceModal()" class="absolute top-4 right-4 z-10 bg-gray-100 hover:bg-gray-200 rounded-full p-2">
                        <x-icon name="heroicon-o-x-mark" class="h-5 w-5" />
                    </button>

                    <!-- Print Button -->
                    <button onclick="window.print()" class="absolute top-4 right-16 z-10 bg-blue-600 hover:bg-blue-700 text-white rounded-full p-2">
                        <x-icon name="heroicon-o-printer" class="h-5 w-5" />
                    </button>

                    @php
                        $roomCode = $invoice_data['booking']['room_code'] ?? '-';
                        $customerName = $invoice_data['booking']['customer_name'] ?? '-';
                        $checkIn = $invoice_data['booking']['check_in'] ?? '-';
                        $period = $invoice_data['period'] ?? '';
                        
                        // Organize services by type
                        $electricLog = collect($invoice_data['logs'])->first(fn($l) => str_contains(mb_strtolower($l['service_name'] ?? '', 'UTF-8'), 'điện'));
                        $waterLog = collect($invoice_data['logs'])->first(fn($l) => str_contains(mb_strtolower($l['service_name'] ?? '', 'UTF-8'), 'nước'));
                        $otherLogs = collect($invoice_data['logs'])->reject(function($l) {
                            $name = mb_strtolower($l['service_name'] ?? '', 'UTF-8');
                            return str_contains($name, 'điện') || str_contains($name, 'nước');
                        });
                        
                        $electricTotal = $electricLog['total_amount'] ?? 0;
                        $waterTotal = $waterLog['total_amount'] ?? 0;
                        $otherTotal = $otherLogs->sum('total_amount');
                        $roomPrice = $invoice_data['room_price'] ?? 0;
                        $grandTotal = $invoice_data['total'] ?? 0;
                    @endphp

                    <!-- Bill Content - Matching main bill preview format -->
                    <div class="p-10" style="font-family: 'Times New Roman', Times, serif;">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-6 pb-4 border-b-2 border-gray-800">
                            <div class="flex items-center gap-4">
                                <img src="{{ asset('logo.jpg') }}" alt="Logo" class="w-16 h-16 border-2 border-gray-800 rounded-full object-cover">
                                <div class="text-sm italic">Sala Apartment</div>
                            </div>
                            <div class="text-center flex-1 px-4">
                                <h1 class="text-sm font-bold uppercase">SALA APARTMENT AND HOTEL ĐÀ NẴNG</h1>
                                <p class="text-[11px] mt-1">Số điện thoại: 084 424 4567</p>
                                <p class="text-[11px]">Địa chỉ: 22 Lý Nhật Quang, Nại Hiên Đông, Sơn Trà, Đà Nẵng</p>
                            </div>
                            <img src="{{ asset('qr.jpg') }}" alt="QR Code" class="w-16 h-16 border border-gray-800 object-contain">
                        </div>

                        <!-- Title -->
                        <div class="text-center my-6">
                            <h2 class="text-lg font-bold">HÓA ĐƠN TIỀN PHÒNG - KỲ {{ $period }}/</h2>
                            <div class="text-base font-bold">ROOM BILL - PERIOD {{ $period }}</div>
                        </div>

                        <!-- Info Box -->
                        <div class="flex justify-end mb-4">
                            <div class="border border-gray-800 px-4 py-2 text-right text-sm">
                                <p><em>Phòng/Room:</em> <strong>{{ $roomCode }}</strong></p>
                                <p><em>Check-in:</em> <strong>{{ $checkIn }}</strong></p>
                                <p><em>Kỳ/Period:</em> <strong>{{ $period }}</strong></p>
                            </div>
                        </div>

                        <!-- Greeting -->
                        <div class="mb-4 text-sm">
                            <p>Kính gửi/ Dear <strong>{{ $customerName }}</strong></p>
                        </div>

                        <!-- Content -->
                        <div class="text-xs leading-relaxed mb-4">
                            <p>Xin chân thành cảm ơn quý khách đã chọn và sử dụng dịch vụ tại Sala Apartment and Hotel cho kỳ nghỉ của mình. Sala Apartment and Hotel kính gửi hóa đơn tiền phòng kỳ {{ $period }} của quý khách như sau:</p>
                            <p class="mt-2">Thank you very much for choosing and using services at Sala Apartment and Hotel for your stay.</p>
                        </div>

                        <!-- Main Table -->
                        <table class="w-full border-collapse mb-4">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="border border-gray-800 p-2 text-xs font-bold text-center">Tiền phòng/<br>Room rental</th>
                                    @if($waterTotal > 0)
                                        <th class="border border-gray-800 p-2 text-xs font-bold text-center">Nước/<br>Water</th>
                                    @endif
                                    @if($electricTotal > 0)
                                        <th class="border border-gray-800 p-2 text-xs font-bold text-center">Điện/<br>Electric</th>
                                    @endif
                                    @foreach($otherLogs as $otherLog)
                                        <th class="border border-gray-800 p-2 text-xs font-bold text-center">{{ $otherLog['service_name'] }}</th>
                                    @endforeach
                                    <th class="border border-gray-800 p-2 text-xs font-bold text-center">TỔNG/<br>TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="border border-gray-800 p-2 text-xs text-right">{{ number_format($roomPrice, 0, ',', '.') }}</td>
                                    @if($waterTotal > 0)
                                        <td class="border border-gray-800 p-2 text-xs text-right">{{ number_format($waterTotal, 0, ',', '.') }}</td>
                                    @endif
                                    @if($electricTotal > 0)
                                        <td class="border border-gray-800 p-2 text-xs text-right">{{ number_format($electricTotal, 0, ',', '.') }}</td>
                                    @endif
                                    @foreach($otherLogs as $otherLog)
                                        <td class="border border-gray-800 p-2 text-xs text-right">{{ number_format($otherLog['total_amount'], 0, ',', '.') }}</td>
                                    @endforeach
                                    <td class="border border-gray-800 p-2 text-sm text-right font-bold">{{ number_format($grandTotal, 0, ',', '.') }} VNĐ</td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Electric Detail Table -->
                        @if($electricLog && $electricLog['type'] === 'meter')
                            <div class="text-[10px] italic mb-2">* Chỉ số công tơ điện/Note: electronic index</div>
                            <table class="w-full border-collapse mb-4">
                                <thead>
                                    <tr class="bg-gray-200">
                                        <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Số CTĐ đầu<br>Start Electronic index</th>
                                        <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Số CTĐ cuối<br>End Electronic index</th>
                                        <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Đơn giá/unit price<br>({{ number_format($electricLog['unit_price'], 0, ',', '.') }} vnđ)</th>
                                        <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Tổng/Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border border-gray-800 p-2 text-xs text-center">{{ number_format($electricLog['start_index'], 0, ',', '.') }}</td>
                                        <td class="border border-gray-800 p-2 text-xs text-center">{{ number_format($electricLog['end_index'], 0, ',', '.') }}</td>
                                        <td class="border border-gray-800 p-2 text-xs text-center">{{ $electricLog['end_index'] - $electricLog['start_index'] }} x {{ number_format($electricLog['unit_price'], 0, ',', '.') }}</td>
                                        <td class="border border-gray-800 p-2 text-xs text-right font-semibold">{{ number_format($electricTotal, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif

                        <!-- Water Detail Table -->
                        @if($waterLog && $waterLog['type'] === 'meter')
                            <div class="text-[10px] italic mb-2">* Chỉ số công tơ nước/Note: water meter index</div>
                            <table class="w-full border-collapse mb-4">
                                <thead>
                                    <tr class="bg-gray-200">
                                        <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Số CTN đầu<br>Start Water index</th>
                                        <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Số CTN cuối<br>End Water index</th>
                                        <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Đơn giá/unit price<br>({{ number_format($waterLog['unit_price'], 0, ',', '.') }} vnđ)</th>
                                        <th class="border border-gray-800 p-2 text-[11px] font-bold text-center">Tổng/Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="border border-gray-800 p-2 text-xs text-center">{{ number_format($waterLog['start_index'], 0, ',', '.') }}</td>
                                        <td class="border border-gray-800 p-2 text-xs text-center">{{ number_format($waterLog['end_index'], 0, ',', '.') }}</td>
                                        <td class="border border-gray-800 p-2 text-xs text-center">{{ $waterLog['end_index'] - $waterLog['start_index'] }} x {{ number_format($waterLog['unit_price'], 0, ',', '.') }}</td>
                                        <td class="border border-gray-800 p-2 text-xs text-right font-semibold">{{ number_format($waterTotal, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        @endif

                        <!-- Footer -->
                        <div class="text-xs mt-6 space-y-2">
                            <p class="italic">• Vui lòng thanh toán trước ngày 05 hàng tháng.</p>
                            <p class="italic">• Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ: 084 424 4567</p>
                            <p class="italic">• Please pay before the 5th of each month.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>[x-cloak] { display: none !important; }</style>
</div>
