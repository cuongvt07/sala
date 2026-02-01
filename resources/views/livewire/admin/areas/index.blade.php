<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Quản lý Khu vực</h1>
        <x-ui.button wire:click="create" variant="primary" size="md">
            + Thêm Khu vực
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
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tên Khu vực</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mô tả</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Số phòng</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($areas as $area)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $area->name }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                            {{ $area->description }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
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
    <x-ui.modal name="showModal" :title="$editingAreaId ? 'Chỉnh sửa Khu vực' : 'Thêm Khu vực mới'">
        <form wire:submit="save" class="space-y-4 p-4 sm:p-0">
            <x-ui.input 
                label="Tên Khu vực" 
                id="name" 
                wire:model="name" 
                :error="$errors->first('name')" 
                required 
            />

            <x-ui.input 
                label="Địa chỉ" 
                id="address" 
                wire:model="address" 
                :error="$errors->first('address')" 
            />

            <div class="space-y-1.5">
                <label for="description" class="block text-sm font-semibold text-gray-700">Mô tả</label>
                <textarea 
                    id="description" 
                    wire:model="description"
                    class="block w-full rounded-lg border-gray-200 bg-gray-50 p-2.5 text-sm text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm"
                    rows="3"
                ></textarea>
                @error('description') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
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
