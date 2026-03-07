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

    <!-- Maintenance History Section -->
    <div class="mt-8 space-y-6">
        <div class="flex items-center gap-3">
            <x-icon name="heroicon-o-wrench-screwdriver" class="w-6 h-6 text-gray-500" />
            <h2 class="text-xl font-bold text-gray-800 tracking-tight">Lịch sử & Kế hoạch bảo dưỡng</h2>
        </div>

        @if(isset($maintenances) && $maintenances->count() > 0)
            <div class="space-y-4">
                @foreach(['new' => 'Kế hoạch sắp tới', 'current' => 'Đang thực hiện', 'old' => 'Lịch sử bảo dưỡng'] as $key => $title)
                    @if($maintenances->has($key))
                        <div class="space-y-3">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400 mt-6">{{ $title }}</h3>
                            <div class="grid grid-cols-1 gap-4">
                                @foreach($maintenances->get($key) as $m)
                                    <div class="bg-white border rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                                        @php
                                            $borderClass = match($key) {
                                                'new' => 'border-l-4 border-l-blue-500',
                                                'current' => 'border-l-4 border-l-amber-500 bg-amber-50/30',
                                                'old' => 'border-l-4 border-l-gray-300 opacity-75',
                                            };
                                            $badgeClass = match($key) {
                                                'new' => 'bg-blue-100 text-blue-700',
                                                'current' => 'bg-amber-100 text-amber-700 animate-pulse',
                                                'old' => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <div class="absolute inset-0 {{ $borderClass }} pointer-events-none"></div>
                                        
                                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3 mb-1">
                                                    <span class="text-sm font-bold text-gray-900">{{ $m->task_name }}</span>
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-tighter {{ $badgeClass }}">
                                                        {{ $key == 'new' ? 'Sắp tới' : ($key == 'current' ? 'Hôm nay' : 'Đã xong') }}
                                                    </span>
                                                </div>
                                                <p class="text-xs text-gray-500 leading-relaxed">{{ $m->description ?: 'Không có mô tả chi tiết.' }}</p>
                                            </div>
                                            
                                            <div class="flex items-center gap-6 text-right shrink-0">
                                                <div class="space-y-0.5">
                                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Ngày thực hiện</div>
                                                    <div class="text-sm font-medium text-gray-700">{{ $m->maintenance_date->format('d/m/Y') }}</div>
                                                </div>
                                                <div class="space-y-0.5">
                                                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Chi phí</div>
                                                    <div class="text-sm font-black text-blue-600">{{ number_format($m->cost, 0, '', '.') }}đ</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-3xl p-12 text-center">
                <div class="w-16 h-16 bg-white rounded-full shadow-sm flex items-center justify-center mx-auto mb-4 border border-gray-100">
                    <x-icon name="heroicon-o-clipboard-document-list" class="w-8 h-8 text-gray-300" />
                </div>
                <h3 class="text-gray-900 font-bold">Chưa có dữ liệu bảo dưỡng</h3>
                <p class="text-gray-500 text-sm mt-1 max-w-xs mx-auto">Phòng này hiện chưa ghi nhận bất kỳ thông tin bảo dưỡng nào trong hệ thống.</p>
            </div>
        @endif
    </div>
</div>
