<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Quản lý Khách hàng</h1>
        <x-ui.button wire:click="create" variant="primary" size="md">
            + Thêm khách hàng mới
        </x-ui.button>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-200 text-green-700 px-3 py-2 rounded-lg relative mb-6 shadow-sm flex items-center gap-2" role="alert">
            <x-icon name="heroicon-o-check-circle" class="h-5 w-5" />
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Advanced Filters -->
    <x-ui.filter-group title="Bộ lọc" icon="heroicon-o-funnel" :show="true">
        <x-ui.filter-item label="Tìm kiếm khách hàng">
            <div class="relative">
                <x-icon name="heroicon-o-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" />
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       placeholder="Họ tên, SĐT, CCCD..." 
                       class="w-full pl-9 pr-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-xs font-medium">
            </div>
        </x-ui.filter-item>

        <x-ui.filter-item label="Quốc tịch">
            <select wire:model.live="filterNationality" 
                    class="w-full px-3 py-2 bg-gray-50/50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all text-xs font-medium appearance-none">
                <option value="">Tất cả</option>
                @foreach($this->getCountries() as $code => $name)
                    <option value="{{ $code }} {{ $name }}">{{ $code }} - {{ $name }}</option>
                @endforeach
            </select>
        </x-ui.filter-item>
    </x-ui.filter-group>

    <x-ui.card class="p-0 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Họ và Tên</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Liên hệ</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Phòng & Ngày Check-in</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">CCCD / Visa / Passport</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Hạn Visa</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-900 uppercase tracking-wider">Quốc tịch</th>
                    <th class="px-6 py-4 text-right text-xs font-semibold text-gray-900 uppercase tracking-wider">Hành động</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($customers as $customer)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="text-[13px] font-bold text-gray-900">{{ $customer->name }}</div>
                                @if($customer->gender)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $customer->gender === 'male' ? 'bg-blue-50 text-blue-600 border border-blue-100' : ($customer->gender === 'female' ? 'bg-pink-50 text-pink-600 border border-pink-100' : 'bg-gray-50 text-gray-600 border border-gray-100') }}">
                                        {{ $customer->gender === 'male' ? 'Nam' : ($customer->gender === 'female' ? 'Nữ' : 'Khác') }}
                                    </span>
                                @endif
                                @if($customer->birthday && \Carbon\Carbon::parse($customer->birthday)->isBirthday())
                                    <span class="inline-flex items-center gap-1 bg-pink-100 text-pink-700 text-[10px] font-bold px-2 py-0.5 rounded-full border border-pink-200">
                                        🎂 Sinh nhật
                                    </span>
                                @endif
                            </div>
                            <div class="text-[11px] text-gray-500">
                                SN: {{ $customer->birthday ? \Carbon\Carbon::parse($customer->birthday)->format('d/m/Y') : '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-[13px] text-gray-900">{{ $customer->phone }}</div>
                            <div class="text-[11px] text-gray-500">{{ $customer->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($customer->bookings->first())
                                @php $activeBooking = $customer->bookings->first(); @endphp
                                <div class="text-[13px] font-black {{ $activeBooking->status === 'checked_in' ? 'text-green-600' : 'text-blue-600' }}">
                                    {{ $activeBooking->room->code ?? 'N/A' }}
                                </div>
                                <div class="text-[11px] text-gray-500 font-bold">
                                    In: {{ $activeBooking->check_in->format('d/m') }}
                                </div>
                            @else
                                <span class="text-gray-300 text-xs">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-[13px] font-bold text-gray-900">
                            {{ $customer->identity_id }}
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

                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($customer->nationality)
                                <div class="text-[11px] font-black text-indigo-600 uppercase tracking-tighter">🌍 {{ $customer->nationality }}</div>
                            @else
                                <span class="text-gray-300 text-xs">-</span>
                            @endif
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Col 1 --}}
                <div class="space-y-4">
                     <div class="space-y-1">
                        <label for="name" class="block font-semibold text-gray-700 text-[11px] uppercase">Họ và Tên <span class="text-red-500">*</span></label>
                        <input type="text" id="name" wire:model="name" required class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm font-bold focus:ring-blue-500 focus:border-blue-500">
                        @error('name') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="phone" class="block font-semibold text-gray-700 text-[11px] uppercase">Số điện thoại</label>
                        <input type="text" id="phone" wire:model="phone" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm font-bold focus:ring-blue-500 focus:border-blue-500" placeholder="Không bắt buộc">
                        @error('phone') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="space-y-1">
                         <label for="email" class="block font-semibold text-gray-700 text-[11px] uppercase">Email</label>
                        <input type="email" id="email" wire:model="email" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('email') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label for="gender" class="block font-semibold text-gray-700 text-[11px] uppercase">Giới tính</label>
                        <select id="gender" wire:model="gender" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm font-bold focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Chọn</option>
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                        @error('gender') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Col 2 --}}
                <div class="space-y-4">
                    <div class="space-y-1">
                        <label for="identity_id" class="block font-semibold text-gray-700 text-[11px] uppercase">CCCD / Visa / Passport</label>
                        <input type="text" id="identity_id" wire:model="identity_id" class="block w-full rounded border-gray-300 bg-gray-50 py-1.5 text-sm font-bold focus:ring-blue-500 focus:border-blue-500" placeholder="Không bắt buộc">
                        @error('identity_id') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block font-semibold text-gray-700 text-[11px] uppercase mb-1">Quốc tịch</label>
                        <x-ui.select-search 
                            wire:model.live="nationality" 
                            :options="$this->getFormattedCountries()"
                            placeholder="Tìm quốc tịch (VNM, USA...)"
                        />
                        @error('nationality') <p class="text-[10px] text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <x-ui.select-date wire:model="birthday" label="Ngày sinh" />
                        <x-ui.select-date wire:model="visa_expiry" label="Hạn Visa" />
                    </div>
                </div>
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
