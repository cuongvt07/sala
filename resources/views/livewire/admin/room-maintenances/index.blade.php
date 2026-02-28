<div class="space-y-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Quản lý Bảo dưỡng Phòng</h2>
        <button wire:click="create" class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg shadow-sm hover:bg-blue-700 transition">
            <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Thêm mới
        </button>
    </div>

    @if (session()->has('message'))
        <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                    <tr>
                        <th scope="col" class="px-6 py-3">Phòng / Tòa nhà</th>
                        <th scope="col" class="px-6 py-3">Hạng mục</th>
                        <th scope="col" class="px-6 py-3">Ngày làm</th>
                        <th scope="col" class="px-6 py-3">Chi phí</th>
                        <th scope="col" class="px-6 py-3">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($maintenances as $item)
                        <tr class="bg-white border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $item->room->code ?? '' }}
                                <span class="block text-xs text-gray-500 mt-1">{{ $item->room->area->name ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-800">{{ $item->task_name }}</div>
                                @if($item->description)
                                    <div class="text-xs text-gray-500 mt-1 truncate max-w-xs">{{ $item->description }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $item->maintenance_date->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 font-medium text-blue-600">
                                {{ number_format($item->cost, 0, ',', '.') }}đ
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="edit({{ $item->id }})" class="text-blue-600 hover:text-blue-900 mr-3 font-medium">Sửa</button>
                                <button wire:click="delete({{ $item->id }})" onclick="confirm('Bạn có chắc chắn muốn xóa không?') || event.stopImmediatePropagation()" class="text-red-600 hover:text-red-900 font-medium">Xóa</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Không có dữ liệu bảo dưỡng nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($maintenances->hasPages())
            <div class="p-4 border-t">
                {{ $maintenances->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Thêm/Sửa --}}
    <x-ui.modal name="showModal" :title="$editingId ? 'Sửa lịch bảo dưỡng' : 'Thêm mới lịch bảo dưỡng'">
        <form wire:submit="save" class="space-y-4 p-4 sm:p-0">
            <div>
                <label class="block font-semibold text-gray-700 text-[11px] uppercase mb-1">Phòng <span class="text-red-500">*</span></label>
                
                <div x-data="{
                    open: false,
                    search: '',
                    selectedId: @entangle('room_id'),
                    options: [
                        @foreach($rooms as $room)
                        { id: {{ $room->id }}, label: '{{ $room->code }} ({{ $room->area->name ?? '' }})' },
                        @endforeach
                    ],
                    get filteredOptions() {
                        if (this.search === '') return this.options;
                        return this.options.filter(o => o.label.toLowerCase().includes(this.search.toLowerCase()));
                    },
                    get selectedLabel() {
                        const option = this.options.find(o => o.id == this.selectedId);
                        return option ? option.label : '-- Chọn phòng --';
                    }
                }" class="relative w-full">
                    <button type="button" @click="open = !open" class="relative w-full rounded border-gray-300 bg-gray-50 py-1.5 pl-3 pr-10 text-left text-sm font-bold shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <span x-text="selectedLabel" class="block truncate" :class="!selectedId ? 'text-gray-500 font-normal' : ''"></span>
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>

                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                        <div class="px-2 py-2 sticky top-0 bg-white border-b border-gray-100">
                            <input x-model="search" type="text" class="w-full rounded-md border-gray-300 py-1.5 text-xs text-gray-700" placeholder="Tìm kiếm phòng...">
                        </div>
                        <ul class="max-h-48 overflow-y-auto custom-scrollbar-hide">
                            <template x-for="option in filteredOptions" :key="option.id">
                                <li @click="selectedId = option.id; open = false;" class="relative cursor-pointer select-none py-2 pl-3 pr-9 hover:bg-blue-50">
                                    <span x-text="option.label" class="block truncate text-[13px]" :class="selectedId == option.id ? 'font-bold text-blue-600' : 'text-gray-700'"></span>
                                </li>
                            </template>
                            <li x-show="filteredOptions.length === 0" class="p-2 text-gray-500 text-xs text-center">Không tìm thấy phòng</li>
                        </ul>
                    </div>
                </div>

                @error('room_id') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block font-semibold text-gray-700 text-[11px] uppercase mb-1">Ngày làm <span class="text-red-500">*</span></label>
                    <input type="date" wire:model.blur="maintenance_date" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('maintenance_date') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 text-[11px] uppercase mb-1">Chi phí</label>
                    <input type="text" wire:model.blur="cost" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" class="block w-full rounded border-gray-300 font-bold text-indigo-600 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="0">
                    @error('cost') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block font-semibold text-gray-700 text-[11px] uppercase mb-1">Hạng mục (Tên công việc) <span class="text-red-500">*</span></label>
                <input type="text" wire:model.blur="task_name" placeholder="Vệ sinh máy lạnh..." class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                @error('task_name') <span class="text-red-500 text-[10px] mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block font-semibold text-gray-700 text-[11px] uppercase mb-1">Mô tả thêm</label>
                <textarea wire:model.blur="description" rows="3" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Chi tiết..."></textarea>
            </div>

            <div class="flex justify-end pt-4 gap-3">
                <x-ui.button wire:click="$set('showModal', false)" variant="secondary" type="button">
                    Hủy bỏ
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    {{ $editingId ? 'Cập nhật' : 'Lưu lại' }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
