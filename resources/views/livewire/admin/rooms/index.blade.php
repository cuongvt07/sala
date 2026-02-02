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
                                    'available' => 'green',
                                    'occupied' => 'red',
                                    'maintenance' => 'yellow',
                                    'reserved' => 'blue',
                                    default => 'gray',
                                };
                            @endphp
                            <x-ui.badge :variant="$statusVariant">
                                {{ $room->status }}
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
            <x-ui.input 
                label="Mã Phòng" 
                id="code" 
                wire:model="code" 
                :error="$errors->first('code')" 
                required 
                class="font-bold text-[12px]"
            />

            <!-- Area Select -->
            <div class="space-y-1.5">
                <label for="area_id" class="block font-normal text-gray-900 uppercase tracking-widest text-[11px] mb-1.5">Khu vực <span class="text-red-500">*</span></label>
                <select id="area_id" wire:model="area_id" class="block w-full rounded-lg border-gray-200 bg-white p-2 text-[12px] font-bold text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm">
                    <option value="">-- Chọn Khu vực --</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
                @error('area_id') <p class="text-[11px] text-red-500">{{ $message }}</p> @enderror
            </div>

            <!-- Type -->
            <div class="space-y-1.5">
                <label for="type" class="block font-normal text-gray-900 uppercase tracking-widest text-[11px] mb-1.5">Loại phòng <span class="text-red-500">*</span></label>
                <select id="type" wire:model="type" class="block w-full rounded-lg border-gray-200 bg-white p-2 text-[12px] font-bold text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm">
                    <option value="Studio">Studio</option>
                    <option value="1PN">1 Phòng ngủ</option>
                    <option value="2PN">2 Phòng ngủ</option>
                    <option value="Duplex">Duplex</option>
                </select>
                @error('type') <p class="text-[11px] text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input 
                    type="number" 
                    label="Đơn giá (VNĐ)" 
                    id="price_day" 
                    wire:model="price_day" 
                    :error="$errors->first('price_day')" 
                    required 
                    class="font-bold text-[12px]"
                />

                <x-ui.input 
                    type="number" 
                    label="Tiền phòng (VNĐ)" 
                    id="price_hour" 
                    wire:model="price_hour" 
                    :error="$errors->first('price_hour')" 
                    class="font-bold text-[12px]"
                />
            </div>

            <!-- Status -->
            <div class="space-y-1.5">
                <label for="status" class="block font-normal text-gray-900 uppercase tracking-widest text-[11px] mb-1.5">Trạng thái <span class="text-red-500">*</span></label>
                <select id="status" wire:model="status" class="block w-full rounded-lg border-gray-200 bg-white p-2 text-[12px] font-bold text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm">
                    <option value="available">Trống</option>
                    <option value="occupied">Đang có khách</option>
                    <option value="reserved">Đã đặt</option>
                    <option value="maintenance">Bảo trì</option>
                </select>
                @error('status') <p class="text-[11px] text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <label for="description" class="block font-normal text-gray-900 uppercase tracking-widest text-[11px] mb-1.5">Mô tả</label>
                <textarea 
                    id="description" 
                    wire:model="description"
                    class="block w-full rounded-lg border-gray-200 bg-white p-2 text-[12px] font-bold text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm"
                    rows="3"
                ></textarea>
                @error('description') <p class="text-[11px] text-red-500">{{ $message }}</p> @enderror
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
