<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Chỉnh sửa Khách hàng</h1>
        <a href="{{ route('admin.customers.index') }}">
            <x-ui.button variant="secondary" size="sm" icon="←">
                Quay lại danh sách
            </x-ui.button>
        </a>
    </div>

    <x-ui.card class="p-6">
        <form wire:submit="save" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-ui.input label="Họ và Tên" id="name" wire:model="name" :error="$errors->first('name')" required />

                <x-ui.input label="Số điện thoại" id="phone" wire:model="phone" :error="$errors->first('phone')" required />

                <x-ui.input label="CCCD / CMND" id="identity_id" wire:model="identity_id" :error="$errors->first('identity_id')" required />

                <x-ui.input type="email" label="Email" id="email" wire:model="email" :error="$errors->first('email')" />

                <x-ui.input type="date" label="Ngày sinh" id="birthday" wire:model="birthday" :error="$errors->first('birthday')" />

                <x-ui.input label="Quốc tịch" id="nationality" wire:model="nationality" :error="$errors->first('nationality')" />
            </div>

            <div class="flex justify-end pt-2">
                <x-ui.button type="submit" variant="primary">
                    Cập nhật
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
