<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Quản lý Dịch vụ</h1>
        <x-ui.button wire:click="create" variant="primary" size="md">
            + Thêm Dịch vụ mới
        </x-ui.button>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 px-3 py-2 rounded-lg relative mb-6 shadow-sm flex items-center gap-2" role="alert">
            <x-icon name="heroicon-o-check-circle" class="h-5 w-5" />
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <x-ui.card class="p-0 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Tên dịch vụ</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Loại</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Đơn giá</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Đơn vị</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Trạng thái</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-900 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($services as $service)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-[13px] font-bold text-gray-900">
                            {{ $service->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[13px] text-gray-900">
                            <x-ui.badge :variant="$service->type === 'meter' ? 'blue' : 'gray'">
                                {{ $service->type === 'meter' ? 'Theo số' : 'Cố định' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[13px] text-gray-900 font-bold">
                            {{ number_format($service->unit_price, 0, ',', '.') }}đ
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[13px] text-gray-900">
                            {{ $service->unit_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-ui.badge :variant="$service->is_active ? 'green' : 'red'">
                                {{ $service->is_active ? 'Hoạt động' : 'Tạm dừng' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <x-ui.button wire:click="edit({{ $service->id }})" variant="secondary" size="sm">
                                Sửa
                            </x-ui.button>
                            <x-ui.button 
                                wire:click="delete({{ $service->id }})" 
                                wire:confirm="Bạn có chắc chắn muốn xóa dịch vụ này không?" 
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
            {{ $services->links() }}
        </div>
    </x-ui.card>

    <!-- Create/Edit Modal -->
    <x-ui.modal name="showModal" :title="$editingServiceId ? 'Chỉnh sửa Dịch vụ' : 'Thêm Dịch vụ mới'">
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Col 1 --}}
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label for="name" class="block font-semibold text-gray-700 text-[11px] uppercase">Tên dịch vụ <span class="text-red-500">*</span></label>
                        <input type="text" id="name" wire:model="name" required placeholder="VD: Điện, Nước..." class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm font-bold focus:ring-blue-500 focus:border-blue-500">
                        @error('name') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="type" class="block font-semibold text-gray-700 text-[11px] uppercase">Loại dịch vụ <span class="text-red-500">*</span></label>
                        <select id="type" wire:model="type" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="fixed">Cố định (Tháng/Người)</option>
                            <option value="meter">Theo chỉ số (Điện/Nước)</option>
                        </select>
                        @error('type') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                         <label for="unit_name" class="block font-semibold text-gray-700 text-[11px] uppercase">Đơn vị tính</label>
                        <input type="text" id="unit_name" wire:model="unit_name" placeholder="VD: kWh, m3, Tháng..." class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm font-bold focus:ring-blue-500 focus:border-blue-500">
                        @error('unit_name') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Col 2 --}}
                <div class="space-y-4">
                    <div class="space-y-1" x-data>
                        <label for="unit_price" class="block font-semibold text-gray-700 text-[11px] uppercase">Đơn giá (VNĐ) <span class="text-red-500">*</span></label>
                        <input type="text" id="unit_price" wire:model="unit_price" required
                               class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm font-bold text-blue-600 focus:ring-blue-500 focus:border-blue-500"
                               x-on:input="$el.value = $el.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                        @error('unit_price') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="description" class="block font-semibold text-gray-700 text-[11px] uppercase">Mô tả</label>
                        <textarea id="description" wire:model="description" rows="3" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                         @error('description') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" id="is_active" wire:model="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_active" class="text-sm font-medium text-gray-700 select-none cursor-pointer">Đang hoạt động</label>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 gap-3 border-t border-gray-100">
                <x-ui.button @click="show = false" variant="secondary" type="button">
                    Hủy bỏ
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    {{ $editingServiceId ? 'Cập nhật' : 'Thêm mới' }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
