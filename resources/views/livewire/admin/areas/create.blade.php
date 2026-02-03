<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Thêm Khu vực mới</h1>
        <a href="{{ route('admin.areas.index') }}">
            <x-ui.button variant="secondary" size="sm" icon="←">
                Quay lại danh sách
            </x-ui.button>
        </a>
    </div>

    <x-ui.card class="p-6">
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label for="name" class="block font-semibold text-gray-700 text-[11px] uppercase">Tên Khu vực <span class="text-red-500">*</span></label>
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

            <div class="flex justify-end pt-2">
                <x-ui.button type="submit" variant="primary">
                    Lưu lại
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
