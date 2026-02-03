<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Chỉnh sửa Phòng</h1>
        <a href="{{ route('admin.rooms.index') }}">
            <x-ui.button variant="secondary" size="sm" icon="←">
                Quay lại danh sách
            </x-ui.button>
        </a>
    </div>

    <x-ui.card class="p-6">
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Area -->
                <div class="space-y-1.5">
                    <label for="area_id" class="block text-sm font-semibold text-gray-700">Khu vực <span class="text-red-500">*</span></label>
                    <select id="area_id" wire:model="area_id" class="block w-full rounded-lg border-gray-200 bg-gray-50 p-2.5 text-sm text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm">
                        <option value="">-- Chọn Khu vực --</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                        @endforeach
                    </select>
                    @error('area_id') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <x-ui.input label="Mã Phòng" id="code" wire:model="code" :error="$errors->first('code')" placeholder="VD: SAR-001" required />

                <div class="space-y-1.5">
                    <label for="type" class="block text-sm font-semibold text-gray-700">Loại Phòng <span class="text-red-500">*</span></label>
                    <select id="type" wire:model="type" class="block w-full rounded-lg border-gray-200 bg-gray-50 p-2.5 text-sm text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm">
                        <option value="Studio">Studio</option>
                        <option value="1PN">1PN</option>
                        <option value="2PN">2PN</option>
                        <option value="3PN">3PN</option>
                        <option value="Penthouse">Penthouse</option>
                    </select>
                    @error('type') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div x-data>
                         <label for="price_day" class="block text-sm font-semibold text-gray-700">Đơn giá (VNĐ) <span class="text-red-500">*</span></label>
                         <input type="text" id="price_day" wire:model="price_day" required
                                class="block w-full rounded-lg border-gray-200 bg-gray-50 p-2.5 text-sm text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm font-bold text-blue-600"
                                x-on:input="$el.value = $el.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                         @error('price_day') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
    
                    <div x-data>
                         <label for="price_hour" class="block text-sm font-semibold text-gray-700">Tiền phòng (VNĐ)</label>
                         <input type="text" id="price_hour" wire:model="price_hour"
                                class="block w-full rounded-lg border-gray-200 bg-gray-50 p-2.5 text-sm text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm font-bold text-gray-600"
                                x-on:input="$el.value = $el.value.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                         @error('price_hour') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="status" class="block text-sm font-semibold text-gray-700">Trạng thái <span class="text-red-500">*</span></label>
                    <select id="status" wire:model="status" class="block w-full rounded-lg border-gray-200 bg-gray-50 p-2.5 text-sm text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm">
                        <option value="active">Hoạt động</option>
                        <option value="maintenance">Bảo trì</option>
                    </select>
                    @error('status') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

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
