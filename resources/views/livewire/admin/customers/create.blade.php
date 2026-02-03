<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Thêm Khách hàng mới</h1>
        <a href="{{ route('admin.customers.index') }}">
            <x-ui.button variant="secondary" size="sm" icon="←">
                Quay lại danh sách
            </x-ui.button>
        </a>
    </div>

    <x-ui.card class="p-6">
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Col 1 --}}
                <div class="space-y-4">
                     <div class="space-y-1">
                        <label for="name" class="block font-semibold text-gray-700 text-[11px] uppercase">Họ và Tên <span class="text-red-500">*</span></label>
                        <input type="text" id="name" wire:model="name" required class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm font-bold focus:ring-blue-500 focus:border-blue-500">
                        @error('name') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="phone" class="block font-semibold text-gray-700 text-[11px] uppercase">Số điện thoại <span class="text-red-500">*</span></label>
                        <input type="text" id="phone" wire:model="phone" required class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm font-bold focus:ring-blue-500 focus:border-blue-500">
                        @error('phone') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="space-y-1">
                         <label for="email" class="block font-semibold text-gray-700 text-[11px] uppercase">Email</label>
                        <input type="email" id="email" wire:model="email" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('email') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Col 2 --}}
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label for="identity_id" class="block font-semibold text-gray-700 text-[11px] uppercase">CCCD / Visa / Passport <span class="text-red-500">*</span></label>
                        <input type="text" id="identity_id" wire:model="identity_id" required class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm font-bold focus:ring-blue-500 focus:border-blue-500">
                        @error('identity_id') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="nationality" class="block font-semibold text-gray-700 text-[11px] uppercase">Quốc tịch</label>
                         <div class="relative">
                             {{-- Using x-ui.select-search via slot or simple input if Select Search component is strict. Assuming Index uses it, we can use it here but styling might differ. Let's use standard for strict consistency or the component if flexible. Component is safer if used in Index. --}}
                             {{-- WAIT: In Index.php, countries is public. Here in Create.php, is it? Need to check. --}}
                             <input type="text" id="nationality" wire:model="nationality" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                             {{-- If Create.php doesn't have $countries populated, select-search will fail. Let's stick to simple input for safety unless I check Create.php --}}
                        </div>
                        @error('nationality') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="birthday" class="block font-semibold text-gray-700 text-[11px] uppercase">Ngày sinh</label>
                            <input type="date" id="birthday" wire:model="birthday" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('birthday') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                         <div class="space-y-1">
                            <label for="visa_expiry" class="block font-semibold text-gray-700 text-[11px] uppercase">Hạn Visa</label>
                            <input type="date" id="visa_expiry" wire:model="visa_expiry" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                             @error('visa_expiry') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
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
