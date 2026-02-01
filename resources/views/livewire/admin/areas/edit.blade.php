<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Chỉnh sửa Khu vực</h1>
        <a href="{{ route('admin.areas.index') }}">
            <x-ui.button variant="secondary" size="sm" icon="←">
                Quay lại danh sách
            </x-ui.button>
        </a>
    </div>

    <x-ui.card class="p-6">
        <form wire:submit="save" class="space-y-6">
            <x-ui.input label="Tên Khu vực" id="name" wire:model="name" :error="$errors->first('name')" required />

            <x-ui.input label="Địa chỉ" id="address" wire:model="address" :error="$errors->first('address')" />

            <div class="space-y-1.5">
                <label for="description" class="block text-sm font-semibold text-gray-700">Mô tả</label>
                <textarea id="description" wire:model="description" rows="3" class="block w-full rounded-lg border-gray-200 bg-gray-50 p-2.5 text-sm text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm"></textarea>
                @error('description') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end pt-2">
                <x-ui.button type="submit" variant="primary">
                    Cập nhật
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
