<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Quản lý Tòa nhà</h1>
        <x-ui.button wire:click="create" variant="primary" size="md">
            + Thêm Tòa nhà
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
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Tên Tòa nhà</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Mô tả</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Số phòng</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-900 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($areas as $area)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-[13px] font-bold text-gray-900">
                            {{ $area->name }}
                        </td>
                        <td class="px-6 py-4 text-[13px] text-gray-900 max-w-xs truncate">
                            {{ $area->description }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[13px] text-gray-900">
                            {{ $area->rooms_count ?? 0 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <x-ui.button wire:click="edit({{ $area->id }})" variant="secondary" size="sm">
                                Sửa
                            </x-ui.button>
                            <x-ui.button 
                                wire:click="delete({{ $area->id }})" 
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
            {{ $areas->links() }}
        </div>
    </x-ui.card>

    <!-- Create/Edit Modal -->
    <x-ui.modal name="showModal" :title="$editingAreaId ? 'Chỉnh sửa Tòa nhà' : 'Thêm Tòa nhà mới'">
        <form wire:submit="save" class="space-y-4 p-4 sm:p-0">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label for="name" class="block font-semibold text-gray-700 text-[11px] uppercase">Tên Tòa nhà <span class="text-red-500">*</span></label>
                        <input type="text" id="name" wire:model="name" required class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm font-bold focus:ring-blue-500 focus:border-blue-500">
                        @error('name') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="address" class="block font-semibold text-gray-700 text-[11px] uppercase">Địa chỉ</label>
                        <input type="text" id="address" wire:model="address" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('address') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-1">
                    <label for="description" class="block font-semibold text-gray-700 text-[11px] uppercase">Mô tả</label>
                    <textarea id="description" wire:model="description" rows="4" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    @error('description') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end pt-4 gap-3">
                <x-ui.button @click="show = false" variant="secondary" type="button">
                    Hủy bỏ
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    {{ $editingAreaId ? 'Cập nhật' : 'Lưu lại' }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
