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
            <x-ui.input label="Tên dịch vụ" id="name" wire:model="name" :error="$errors->first('name')" required placeholder="VD: Điện, Nước, Wifi..." class="font-bold text-[12px]" />
            
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block text-[11px] font-normal text-gray-900 uppercase tracking-widest">Đơn giá (VNĐ)</label>
                    <input 
                        type="text" 
                        wire:model="unit_price" 
                        class="block w-full rounded-lg border-gray-200 bg-white p-2 text-[12px] font-bold text-gray-900 focus:border-blue-500 focus:ring-blue-500 shadow-sm"
                        x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                        required
                    >
                </div>
            </div>

            <x-ui.input label="Tên đơn vị" id="unit_name" wire:model="unit_name" :error="$errors->first('unit_name')" placeholder="VD: kWh, m3, Tháng, Lần..." class="font-bold text-[12px]" />

            <x-ui.input label="Mô tả" id="description" wire:model="description" :error="$errors->first('description')" class="font-bold text-[12px]" />

            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_active" wire:model="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="is_active" class="text-sm font-medium text-gray-700">Đang hoạt động</label>
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
