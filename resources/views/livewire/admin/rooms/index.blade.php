<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Quản lý Phòng</h1>
        <x-ui.button wire:click="create" variant="primary" size="md">
            + Thêm Phòng
        </x-ui.button>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 px-3 py-2 rounded-lg relative mb-6 shadow-sm flex items-center gap-2" role="alert">
            <x-icon name="heroicon-o-check-circle" class="h-5 w-5" />
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Advanced Filters -->
    <x-ui.filter-group title="Bộ lọc" icon="heroicon-o-funnel" :show="true">
        <x-ui.filter-item label="Mã phòng / Tìm kiếm">
            <div class="relative">
                <x-icon name="heroicon-o-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" />
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       placeholder="Nhập mã phòng..." 
                       class="w-full pl-9 pr-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-xs font-medium">
            </div>
        </x-ui.filter-item>

        <x-ui.filter-item label="Loại phòng">
            <select wire:model.live="filterType" 
                    class="w-full px-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-xs font-medium appearance-none">
                <option value="">Tất cả</option>
                <option value="Studio">Studio</option>
                <option value="1PN">1PN</option>
                <option value="2PN">2PN</option>
                <option value="3PN">3PN</option>
                <option value="Penthouse">Penthouse</option>
            </select>
        </x-ui.filter-item>

        <x-ui.filter-item label="Trạng thái">
            <select wire:model.live="filterStatus" 
                    class="w-full px-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-xs font-medium appearance-none">
                <option value="">Tất cả</option>
                <option value="active">Hoạt động</option>
                <option value="maintenance">Bảo trì</option>
            </select>
        </x-ui.filter-item>

        <x-ui.filter-item label="Khu vực / Toà nhà">
            <select wire:model.live="filterArea" 
                    class="w-full px-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-xs font-medium appearance-none">
                <option value="">Tất cả</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
        </x-ui.filter-item>
    </x-ui.filter-group>

    <x-ui.card class="p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Mã Phòng</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Loại</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Khu vực</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Đơn giá</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Tiền phòng</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-900 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($rooms as $room)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-[13px] font-bold text-gray-900">
                            {{ $room->code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[13px] text-gray-900">
                            {{ $room->type }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[13px] text-gray-900">
                            {{ $room->area->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[13px] font-bold text-gray-900">
                            {{ number_format($room->price_day, 0, ',', '.') }}đ
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[13px] font-bold text-gray-500">
                            {{ $room->price_hour ? number_format($room->price_hour, 0, ',', '.') . 'đ' : '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                    $statusVariant = match($room->status) {
                                    'active' => 'green',
                                    'maintenance' => 'red',
                                    default => 'gray', // Should catch old statuses temporarily
                                };
                                $statusLabel = match($room->status) {
                                    'active' => 'Hoạt động',
                                    'maintenance' => 'Bảo trì',
                                    // Fallback for old data if needed (optional)
                                    'available' => 'Hoạt động',
                                    'occupied' => 'Hoạt động', 
                                    'reserved' => 'Hoạt động',
                                    default => $room->status,
                                };
                            @endphp
                            <x-ui.badge :variant="$statusVariant">
                                {{ $statusLabel }}
                            </x-ui.badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                             <x-ui.button wire:click="edit({{ $room->id }})" variant="secondary" size="sm">
                                Sửa
                            </x-ui.button>
                            <x-ui.button 
                                wire:click="delete({{ $room->id }})" 
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
            {{ $rooms->links() }}
        </div>
    </x-ui.card>

    <!-- Create/Edit Modal -->
    <x-ui.modal name="showModal" :title="$editingRoomId ? 'Chỉnh sửa Phòng' : 'Thêm Phòng mới'">
        <form wire:submit="save" class="space-y-4 p-4 sm:p-0">


            <div class="grid grid-cols-3 gap-4">
                {{-- Col 1: Area & Status --}}
                <div class="space-y-4">
                     <div class="space-y-1">
                        <label for="area_id" class="block font-semibold text-gray-700 text-[11px] uppercase">Khu vực <span class="text-red-500">*</span></label>
                        <select id="area_id" wire:model="area_id" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Chọn --</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                        @error('area_id') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="status" class="block font-semibold text-gray-700 text-[11px] uppercase">Trạng thái <span class="text-red-500">*</span></label>
                        <select id="status" wire:model="status" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="active">Hoạt động</option>
                            <option value="maintenance">Bảo trì</option>
                        </select>
                        @error('status') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Col 2: Info --}}
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label for="code" class="block font-semibold text-gray-700 text-[11px] uppercase">Mã Phòng <span class="text-red-500">*</span></label>
                        <input type="text" id="code" wire:model="code" required
                               class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm font-bold focus:ring-blue-500 focus:border-blue-500">
                        @error('code') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="type" class="block font-semibold text-gray-700 text-[11px] uppercase">Loại phòng <span class="text-red-500">*</span></label>
                        <select id="type" wire:model="type" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="Studio">Studio</option>
                            <option value="1PN">1PN</option>
                            <option value="2PN">2PN</option>
                            <option value="Duplex">Duplex</option>
                        </select>
                        @error('type') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Col 3: Pricing --}}
                <div class="space-y-4">
                    <div class="space-y-1" x-data>
                        <label for="price_day" class="block font-semibold text-gray-700 text-[11px] uppercase">Đơn giá (VNĐ) <span class="text-red-500">*</span></label>
                        <input type="text" id="price_day" wire:model="price_day" required
                               class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm font-bold text-blue-600 focus:ring-blue-500 focus:border-blue-500"
                               x-on:input="$el.value = $el.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                        @error('price_day') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1" x-data>
                        <label for="price_hour" class="block font-semibold text-gray-700 text-[11px] uppercase">Tiền phòng (VNĐ)</label>
                        <input type="text" id="price_hour" wire:model="price_hour"
                               class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm font-bold text-gray-600 focus:ring-blue-500 focus:border-blue-500"
                               x-on:input="$el.value = $el.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                        @error('price_hour') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="space-y-1 mt-2">
                <label for="description" class="block font-semibold text-gray-700 text-[11px] uppercase">Mô tả</label>
                <textarea id="description" wire:model="description" rows="2" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                 @error('description') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
            </div>

            @if($editingRoomId && !empty($maintenances))
                <div class="mt-6 border-t pt-4 space-y-4 max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                    <div class="flex items-center gap-2 mb-2">
                        <x-icon name="heroicon-o-wrench-screwdriver" class="w-4 h-4 text-gray-400" />
                        <h3 class="text-[11px] font-bold uppercase tracking-widest text-gray-500">Lịch sử bảo dưỡng</h3>
                    </div>

                    @foreach(['new' => 'Sắp tới', 'current' => 'Hôm nay', 'old' => 'Quá khứ'] as $key => $label)
                        @if(isset($maintenances[$key]))
                            <div class="space-y-2">
                                <div class="text-[9px] font-black text-gray-400 uppercase tracking-tighter">{{ $label }}</div>
                                @foreach($maintenances[$key] as $m)
                                    <div class="p-3 bg-white border border-gray-100 rounded-xl shadow-sm flex items-center justify-between gap-3 relative overflow-hidden group">
                                        @php
                                            $borderClass = match($key) {
                                                'new' => 'bg-blue-500',
                                                'current' => 'bg-amber-500',
                                                'old' => 'bg-gray-300',
                                            };
                                            $badgeClass = match($key) {
                                                'new' => 'bg-blue-50 text-blue-600 border-blue-100',
                                                'current' => 'bg-amber-50 text-amber-600 border-amber-100',
                                                'old' => 'bg-gray-50 text-gray-500 border-gray-100',
                                            };
                                        @endphp
                                        <div class="absolute left-0 top-0 bottom-0 w-1 {{ $borderClass }}"></div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[12px] font-bold text-gray-800 truncate">{{ $m->task_name }}</span>
                                                <span class="px-1.5 py-0.5 rounded border text-[8px] font-black uppercase {{ $badgeClass }}">
                                                    {{ $key == 'new' ? 'Plan' : ($key == 'current' ? 'Now' : 'Done') }}
                                                </span>
                                            </div>
                                            <p class="text-[10px] text-gray-500 truncate mt-0.5">{{ $m->description ?: 'No detail.' }}</p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <div class="text-[10px] font-black text-blue-600">{{ number_format($m->cost, 0, '', '.') }}đ</div>
                                            <div class="text-[9px] text-gray-400">{{ $m->maintenance_date->format('d/m/Y') }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            <div class="flex justify-end pt-4 gap-3">
                <x-ui.button @click="show = false" variant="secondary" type="button">
                    Hủy bỏ
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    {{ $editingRoomId ? 'Cập nhật' : 'Lưu lại' }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
