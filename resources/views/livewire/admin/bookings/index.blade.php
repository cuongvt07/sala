<div class="relative space-y-4">
    <div class="flex flex-wrap justify-between items-center gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Quản lý đặt phòng</h1>
            <p class="text-xs text-gray-500">Khách sắp tới & đang ở. Khách trả phòng sẽ tự xoá khỏi hệ thống (sổ thu chi vẫn giữ).</p>
        </div>
        <x-ui.button wire:click="$dispatch('new-booking')" variant="primary" size="md">+ Đặt phòng mới</x-ui.button>
    </div>

    {{-- Bộ lọc --}}
    <div class="bg-white rounded-xl border border-gray-200 p-3 flex flex-wrap gap-2 items-center">
        <div class="relative flex-1 min-w-[200px]">
            <x-icon name="heroicon-o-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Tìm số phòng, tên khách, SĐT..."
                   class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
        <select wire:model.live="filterStatus" class="py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm">
            <option value="">Tất cả trạng thái</option>
            <option value="checked_in">Đang ở</option>
            <option value="pending">Sắp tới (chờ nhận)</option>
        </select>
        <select wire:model.live="filterType" class="py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm">
            <option value="">Ngày + Hợp đồng</option>
            <option value="day">Thuê ngày</option>
            <option value="month">Hợp đồng tháng</option>
        </select>
        @if($areas->isNotEmpty())
            <select wire:model.live="filterArea" class="py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm">
                <option value="">Tất cả toà</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
        @endif
        @if($search || $filterStatus || $filterType || $filterArea)
            <button wire:click="resetFilters" class="text-xs font-bold text-red-500 px-2">Xoá lọc</button>
        @endif
        <div wire:loading class="text-xs text-blue-600 font-bold">Đang tải...</div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr class="text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                    <th class="px-3 py-3 text-left">Phòng</th>
                    <th class="px-3 py-3 text-left">Khách</th>
                    <th class="px-3 py-3 text-left">Thời gian</th>
                    <th class="px-3 py-3 text-right">Giá</th>
                    <th class="px-3 py-3 text-left">Thanh toán</th>
                    <th class="px-3 py-3 text-left">Trạng thái</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($bookings as $b)
                    <tr wire:key="bk-{{ $b->id }}" wire:click="$dispatch('open-booking', { id: {{ $b->id }} })" class="hover:bg-blue-50/40 cursor-pointer">
                        <td class="px-3 py-2.5 whitespace-nowrap">
                            <div class="font-black text-gray-900">{{ $b->room->code ?? '-' }}</div>
                            <div class="text-[10px] text-gray-400">{{ $b->room->area->name ?? '' }}</div>
                        </td>
                        <td class="px-3 py-2.5">
                            <div class="font-bold text-gray-900 leading-tight">{{ $b->customer->name ?? '-' }}</div>
                            <div class="text-[11px] text-gray-500">{{ $b->customer->phone ?? '' }} @if($b->customer?->nationality) · {{ $b->customer->nationality }} @endif</div>
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap text-xs">
                            <div>{{ $b->check_in->format('d/m/Y') }} → {{ $b->check_out?->format('d/m/Y') ?? 'không thời hạn' }}</div>
                            <div class="text-[10px] font-bold uppercase {{ $b->isContract() ? 'text-blue-600' : 'text-gray-400' }}">{{ $b->isContract() ? 'Hợp đồng · đóng ngày ' . $b->check_in->format('d') : 'Thuê ngày' }}</div>
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap text-right">
                            <div class="font-bold">{{ number_format($b->isContract() ? $b->unit_price : $b->price, 0, ',', '.') }}đ</div>
                            <div class="text-[10px] text-gray-400">{{ $b->isContract() ? '/ tháng' : 'tổng tiền phòng' }}</div>
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap">
                            @php $c = $b->pay_status['color']; @endphp
                            <span class="text-[11px] font-bold px-2 py-0.5 rounded
                                {{ $c === 'green' ? 'bg-green-100 text-green-700' : ($c === 'red' ? 'bg-red-100 text-red-700' : ($c === 'amber' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600')) }}">
                                {{ $b->pay_status['text'] }}
                            </span>
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap">
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded {{ $b->status === 'checked_in' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $b->status === 'checked_in' ? 'Đang ở' : 'Sắp tới' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-10 text-center text-gray-400">Không có đặt phòng nào</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $bookings->links() }}</div>
    </div>

    <livewire:admin.booking-form />
</div>
