<div class="space-y-5">
    @php $money = fn($v) => number_format((float) $v, 0, ',', '.'); @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Dashboard</h1>
            <span class="text-sm text-gray-500">{{ now()->locale('vi')->isoFormat('dddd, DD/MM/YYYY') }}</span>
        </div>
        <div class="flex items-center gap-2 text-xs">
            <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 font-bold">Đang ở: {{ $stayingCount }}</span>
            <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-bold">Tổng phòng: {{ $vacancy['total'] }}</span>
        </div>
    </div>

    {{-- ===== PHÒNG TRỐNG HÔM NAY & 3 NGÀY TỚI ===== --}}
    <x-ui.card class="p-4">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-black text-gray-800 flex items-center gap-2">
                <x-icon name="heroicon-o-key" class="h-4 w-4 text-emerald-600" /> Phòng trống (theo đêm)
            </h3>
            <span class="text-[11px] text-gray-400">Bấm vào số phòng để đặt ngay</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
            @foreach($vacancy['days'] as $day)
                <div class="rounded-xl border {{ $loop->first ? 'border-emerald-300 bg-emerald-50/50' : 'border-gray-200 bg-gray-50/50' }} p-3">
                    <div class="flex items-baseline justify-between mb-2">
                        <div>
                            <span class="text-sm font-black {{ $loop->first ? 'text-emerald-700' : 'text-gray-700' }} capitalize">{{ $day['label'] }}</span>
                            <span class="text-[11px] text-gray-400 ml-1">{{ $day['date']->format('d/m') }}</span>
                        </div>
                        <span class="text-xl font-black {{ $day['count'] ? 'text-emerald-600' : 'text-red-500' }}">{{ $day['count'] }}</span>
                    </div>
                    @forelse($day['byArea'] as $areaName => $rooms)
                        <div class="mb-1.5">
                            @if(count($day['byArea']) > 1)<div class="text-[10px] font-bold text-gray-400 uppercase">{{ $areaName }}</div>@endif
                            <div class="flex flex-wrap gap-1">
                                @foreach($rooms as $room)
                                    <button type="button" wire:click="$dispatch('new-booking', { roomId: {{ $room->id }}, date: '{{ $day['date']->toDateString() }}' })"
                                            title="{{ $room->type }}"
                                            class="px-2 py-0.5 rounded-md bg-white border border-emerald-200 text-xs font-bold text-emerald-700 hover:bg-emerald-600 hover:text-white">
                                        {{ $room->code }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-red-500 font-bold">Hết phòng</p>
                    @endforelse
                </div>
            @endforeach
        </div>
    </x-ui.card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- ===== HỢP ĐỒNG ĐẾN HẠN ĐÓNG TIỀN ===== --}}
        <x-ui.card class="p-4 lg:col-span-1">
            <h3 class="text-sm font-black text-gray-800 mb-3 flex items-center gap-2">
                <x-icon name="heroicon-o-banknotes" class="h-4 w-4 text-red-500" /> Hợp đồng đến hạn thu tiền
                @if(count($contractDues))<span class="ml-auto text-[11px] font-black text-white bg-red-500 rounded-full px-2">{{ count($contractDues) }}</span>@endif
            </h3>
            <div class="space-y-2">
                @forelse($contractDues as $d)
                    <button type="button" wire:click="$dispatch('open-booking', { id: {{ $d['booking']->id }}, tab: 'payment' })"
                            class="w-full text-left flex items-center justify-between p-2 rounded-lg border {{ $d['days'] < 0 ? 'border-red-200 bg-red-50' : 'border-amber-200 bg-amber-50' }} hover:shadow-sm">
                        <div>
                            <div class="text-xs font-black text-gray-800">P.{{ $d['booking']->room->code ?? '-' }} · {{ $d['booking']->customer->name ?? '' }}</div>
                            <div class="text-[11px] text-gray-500">Hạn {{ $d['due']->format('d/m') }} · {{ $money($d['amount']) }}đ</div>
                        </div>
                        <span class="text-[10px] font-black px-2 py-0.5 rounded {{ $d['days'] < 0 ? 'bg-red-500 text-white' : ($d['days'] === 0 ? 'bg-orange-500 text-white' : 'bg-amber-200 text-amber-800') }}">
                            {{ $d['days'] < 0 ? 'Quá ' . abs($d['days']) . ' ngày' : ($d['days'] === 0 ? 'Hôm nay' : 'Còn ' . $d['days'] . ' ngày') }}
                        </span>
                    </button>
                @empty
                    <p class="text-xs text-gray-400 italic py-2">Không có kỳ nào cần thu trong 3 ngày tới.</p>
                @endforelse
            </div>
        </x-ui.card>

        {{-- ===== CHECK-IN / CHECK-OUT ===== --}}
        <x-ui.card class="p-4 lg:col-span-2">
            <h3 class="text-sm font-black text-gray-800 mb-3 flex items-center gap-2">
                <x-icon name="heroicon-o-clock" class="h-4 w-4 text-blue-500" /> Nhận / trả phòng 3 ngày tới
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Sắp nhận phòng</p>
                    @forelse($upcomingCheckins as $bk)
                        <button type="button" wire:click="$dispatch('open-booking', { id: {{ $bk->id }} })" class="w-full flex items-center justify-between p-2 hover:bg-gray-50 rounded border-b border-gray-100 text-left">
                            <span class="text-xs"><b>P.{{ $bk->room->code ?? '-' }}</b> · {{ $bk->customer->name ?? '' }}</span>
                            <span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-bold">{{ $bk->check_in->format('d/m') }}</span>
                        </button>
                    @empty
                        <p class="text-xs text-gray-400 italic py-2">Không có</p>
                    @endforelse
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-1">Sắp trả phòng</p>
                    @forelse($upcomingCheckouts as $bk)
                        <button type="button" wire:click="$dispatch('open-booking', { id: {{ $bk->id }} })" class="w-full flex items-center justify-between p-2 hover:bg-gray-50 rounded border-b border-gray-100 text-left">
                            <span class="text-xs"><b>P.{{ $bk->room->code ?? '-' }}</b> · {{ $bk->customer->name ?? '' }}</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded font-bold {{ $bk->check_out->lt(now()->startOfDay()) ? 'bg-red-500 text-white' : 'bg-orange-100 text-orange-700' }}">
                                {{ $bk->check_out->lt(now()->startOfDay()) ? 'Quá hạn ' : '' }}{{ $bk->check_out->format('d/m') }}
                            </span>
                        </button>
                    @empty
                        <p class="text-xs text-gray-400 italic py-2">Không có</p>
                    @endforelse
                </div>
            </div>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @if($finance)
            <x-ui.card class="p-4">
                <h3 class="text-sm font-black text-gray-800 mb-3 flex items-center gap-2">
                    <x-icon name="heroicon-o-chart-bar" class="h-4 w-4 text-emerald-600" /> Thu chi tháng {{ now()->format('m/Y') }}
                </h3>
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Tổng thu</span><b class="text-green-700">{{ $money($finance['income']) }}đ</b></div>
                    <div class="flex justify-between"><span class="text-gray-500">Tổng chi</span><b class="text-red-600">{{ $money($finance['expense']) }}đ</b></div>
                    <div class="flex justify-between border-t pt-1.5"><span class="font-bold">Còn lại</span><b>{{ $money($finance['income'] - $finance['expense']) }}đ</b></div>
                </div>
                <a href="{{ route('admin.finance.index') }}" class="block mt-3 text-xs font-bold text-blue-600 hover:underline">Xem sổ thu chi →</a>
            </x-ui.card>
        @endif

        <x-ui.card class="p-4">
            <h3 class="text-sm font-black text-gray-800 mb-3 flex items-center gap-2">
                <x-icon name="heroicon-o-shield-exclamation" class="h-4 w-4 text-red-500" /> Hết hạn visa (3 ngày tới)
            </h3>
            <div class="space-y-2">
                @forelse($visaExpiries as $cus)
                    <div class="flex justify-between items-center p-2 border border-gray-200 rounded-lg">
                        <div>
                            <p class="text-xs font-bold text-gray-800">{{ $cus->name }}</p>
                            <p class="text-[10px] text-red-600 font-semibold">Hết hạn: {{ $cus->visa_expiry->format('d/m/Y') }}</p>
                        </div>
                        <select wire:change="updateVisaStatus({{ $cus->id }}, $event.target.value)" class="text-[10px] border-gray-300 rounded px-1 py-0.5">
                            <option value="1" @selected($cus->visa_status == 1)>Sắp hết hạn</option>
                            <option value="2" @selected($cus->visa_status == 2)>Đã thông báo</option>
                            <option value="3" @selected($cus->visa_status == 3)>Đã gia hạn</option>
                        </select>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic py-2">Không có</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card class="p-4">
            <h3 class="text-sm font-black text-gray-800 mb-3 flex items-center gap-2">
                <x-icon name="heroicon-o-gift" class="h-4 w-4 text-pink-500" /> Sinh nhật hôm nay
            </h3>
            <div class="space-y-2">
                @forelse($birthdayCustomers as $cus)
                    <div class="flex items-center justify-between p-2 bg-pink-50 rounded-lg border border-pink-100">
                        <span class="text-xs font-bold text-gray-800">{{ $cus->name }} <span class="font-normal text-gray-500">{{ $cus->phone }}</span></span>
                        <span class="text-[10px] font-bold text-pink-600">{{ $cus->birthday->age }} tuổi</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic py-2">Không có</p>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    <livewire:admin.booking-form />
</div>
