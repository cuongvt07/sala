<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Cập nhật Booking</h1>
        <a href="{{ route('admin.bookings.index') }}">
            <x-ui.button variant="secondary" size="sm" icon="←">
                Quay lại danh sách
            </x-ui.button>
        </a>
    </div>

    <x-ui.card class="p-6">
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Customer -->
                <div class="space-y-1.5">
                    <label for="customer_id" class="block text-sm font-semibold text-gray-700">Khách hàng <span class="text-red-500">*</span></label>
                    <select id="customer_id" wire:model="customer_id" class="block w-full rounded-lg border-gray-200 bg-gray-50 p-2.5 text-sm text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm">
                        <option value="">-- Chọn Khách hàng --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <!-- Room -->
                <div class="space-y-1.5">
                    <label for="room_id" class="block text-sm font-semibold text-gray-700">Phòng <span class="text-red-500">*</span></label>
                    <select id="room_id" wire:model="room_id" class="block w-full rounded-lg border-gray-200 bg-gray-50 p-2.5 text-sm text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm">
                        <option value="">-- Chọn Phòng --</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}">{{ $room->code }} - {{ $room->type }} ({{ $room->area->name ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                    @error('room_id') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <x-ui.input type="date" label="Ngày Check-in" id="check_in" wire:model="check_in" :error="$errors->first('check_in')" required />

                <x-ui.input type="date" label="Ngày Check-out" id="check_out" wire:model="check_out" :error="$errors->first('check_out')" required />

                <x-ui.input type="number" label="Tổng tiền (VNĐ)" id="price" wire:model="price" :error="$errors->first('price')" required />

                <div class="grid grid-cols-3 gap-4">
                    <x-ui.input type="number" label="Cọc Lần 1" id="deposit" wire:model="deposit" :error="$errors->first('deposit')" />
                    <x-ui.input type="number" label="Cọc Lần 2" id="deposit_2" wire:model="deposit_2" :error="$errors->first('deposit_2')" />
                    <x-ui.input type="number" label="Cọc Lần 3" id="deposit_3" wire:model="deposit_3" :error="$errors->first('deposit_3')" />
                </div>

                 <!-- Status -->
                <div class="space-y-1.5">
                    <label for="status" class="block text-sm font-semibold text-gray-700">Trạng thái <span class="text-red-500">*</span></label>
                    <select id="status" wire:model="status" class="block w-full rounded-lg border-gray-200 bg-gray-50 p-2.5 text-sm text-gray-900 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-sm">
                        <option value="pending">Chờ lấy phòng</option>

                        <option value="checked_in">Đã nhận phòng</option>
                        <option value="checked_out">Đã trả phòng</option>
                        <option value="cancelled">Đã hủy</option>
                    </select>
                    @error('status') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <x-ui.button type="submit" variant="primary">
                    Cập nhật
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
