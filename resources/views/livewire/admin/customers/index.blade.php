<div>
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Quản lý Khách hàng</h1>
        <div class="flex flex-col md:flex-row gap-4 items-center w-full md:w-auto">
             <div class="w-full md:w-64">
                <x-ui.input wire:model.live="search" placeholder="Tìm kiếm tên, SĐT, CCCD..." />
             </div>
            <x-ui.button wire:click="create" variant="primary" size="md" class="w-full md:w-auto">
                + Thêm mới
            </x-ui.button>
        </div>
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
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Họ và Tên</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Liên hệ</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">CCCD / CMND</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Số Visa</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Hạn Visa</th>

                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Quốc tịch</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-900 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($customers as $customer)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-[13px] font-bold text-gray-900">{{ $customer->name }}</div>
                            <div class="text-[11px] text-gray-500">SN: {{ $customer->birthday }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-[13px] text-gray-900">{{ $customer->phone }}</div>
                            <div class="text-[11px] text-gray-500">{{ $customer->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[13px] font-bold text-gray-900">
                            {{ $customer->identity_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-[13px] text-gray-900">{{ $customer->visa_number ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($customer->visa_expiry)
                                @php
                                    $expiry = \Carbon\Carbon::parse($customer->visa_expiry);
                                    $isExpiring = $expiry->diffInDays(now(), false) > -30;
                                    $isExpired = $expiry->isPast();
                                @endphp
                                <div class="text-[13px] {{ $isExpired || $isExpiring ? 'text-red-600 font-bold' : 'text-gray-900' }}">
                                    {{ $expiry->format('d/m/Y') }}
                                    @if($isExpired) <span class="text-[10px] text-red-600 block">(Đã hết)</span>
                                    @elseif($isExpiring) <span class="text-[10px] text-red-600 block">(Sắp hết)</span> @endif
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-[13px] text-gray-900">
                            {{ $customer->nationality }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                             <x-ui.button wire:click="edit({{ $customer->id }})" variant="secondary" size="sm">
                                Sửa
                            </x-ui.button>
                            <x-ui.button 
                                wire:click="delete({{ $customer->id }})" 
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
            {{ $customers->links() }}
        </div>
    </x-ui.card>

    <!-- Create/Edit Modal -->
    <x-ui.modal name="showModal" :title="$editingCustomerId ? 'Chỉnh sửa Khách hàng' : 'Thêm Khách hàng mới'">
        <form wire:submit="save" class="space-y-4 p-4 sm:p-0">
            <x-ui.input 
                label="Họ và Tên" 
                id="name" 
                wire:model="name" 
                :error="$errors->first('name')" 
                required 
                class="text-[12px]"
            />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input 
                    label="Số điện thoại" 
                    id="phone" 
                    wire:model="phone" 
                    :error="$errors->first('phone')" 
                    required 
                    class="text-[12px]"
                />
                
                <x-ui.input 
                    label="Email" 
                    type="email"
                    id="email" 
                    wire:model="email" 
                    :error="$errors->first('email')" 
                    class="text-[12px]"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input 
                    label="CCCD / CMND" 
                    id="identity_id" 
                    wire:model="identity_id" 
                    :error="$errors->first('identity_id')" 
                    required 
                    class="text-[12px]"
                />
                
                <x-ui.input 
                    label="Ngày sinh" 
                    type="date"
                    id="birthday" 
                    wire:model="birthday" 
                    :error="$errors->first('birthday')" 
                    class="text-[12px]"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.select-search 
                    label="Quốc tịch" 
                    wire:model="nationality" 
                    :options="$countries"
                    :error="$errors->first('nationality')"
                    placeholder="Chọn quốc tịch"
                />

                 <x-ui.input 
                    label="Số Visa" 
                    id="visa_number" 
                    wire:model="visa_number" 
                    :error="$errors->first('visa_number')" 
                    placeholder="Số Visa (nếu có)"
                    class="text-[12px]"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <x-ui.input 
                    type="date" 
                    label="Hạn Visa" 
                    id="visa_expiry" 
                    wire:model="visa_expiry" 
                    :error="$errors->first('visa_expiry')" 
                    class="text-[12px]"
                />
            </div>

            <div class="flex justify-end pt-4 gap-3">
                <x-ui.button @click="show = false" variant="secondary" type="button">
                    Hủy bỏ
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    {{ $editingCustomerId ? 'Cập nhật' : 'Lưu lại' }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
