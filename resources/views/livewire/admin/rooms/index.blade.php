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
