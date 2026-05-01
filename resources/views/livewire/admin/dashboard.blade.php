<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Dashboard</h1>
            <span class="text-sm font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Tổng quan hệ thống</span>
        </div>
        
        <!-- Tabs -->
        <div class="flex p-1 bg-gray-100 rounded-lg">
            <button wire:click="setTab('general')" class="px-4 py-1.5 text-sm font-medium rounded-md transition-all {{ $activeTab === 'general' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                Vận hành
            </button>
            <button wire:click="setTab('finance')" class="px-4 py-1.5 text-sm font-medium rounded-md transition-all {{ $activeTab === 'finance' ? 'bg-white text-blue-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                Tài chính
            </button>
        </div>
    </div>

    @if($activeTab === 'general')
        <!-- TỔNG QUAN HỆ SINH THÁI -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Areas -->
            <x-ui.card class="p-6 flex items-center justify-between border-l-4 border-l-blue-500">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tòa nhà</p>
                    <p class="text-2xl font-black text-gray-800 mt-2">{{ $totalAreas }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                    <x-icon name="heroicon-o-map" class="h-6 w-6" />
                </div>
            </x-ui.card>

            <!-- Rooms -->
            <x-ui.card class="p-6 flex items-center justify-between border-l-4 border-l-indigo-500">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Phòng</p>
                    <p class="text-2xl font-black text-gray-800 mt-2">{{ $totalRooms }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                    <x-icon name="heroicon-o-building-office" class="h-6 w-6" />
                </div>
            </x-ui.card>

            <!-- Customers -->
            <x-ui.card class="p-6 flex items-center justify-between border-l-4 border-l-emerald-500">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Khách hàng</p>
                    <p class="text-2xl font-black text-gray-800 mt-2">{{ $totalCustomers }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                    <x-icon name="heroicon-o-users" class="h-6 w-6" />
                </div>
            </x-ui.card>
        </div>

        <!-- Booking Status Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-ui.card class="p-6">
                <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <x-icon name="heroicon-o-chart-pie" class="h-4 w-4 text-gray-500" />
                    Trạng thái Booking hiện tại
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg border border-green-100">
                        <span class="text-sm font-medium text-green-700">Đang ở (Checked-in)</span>
                        <span class="font-bold text-green-800 bg-white px-2 py-0.5 rounded shadow-sm text-xs">{{ $activeBookings }}</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg border border-yellow-100">
                        <span class="text-sm font-medium text-yellow-700">Đang chờ (Pending)</span>
                        <span class="font-bold text-yellow-800 bg-white px-2 py-0.5 rounded shadow-sm text-xs">{{ $pendingBookings }}</span>
                    </div>
                </div>
            </x-ui.card>

            <!-- Upcoming Events (3 Days) -->
            <x-ui.card class="md:col-span-2 p-6">
                <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <x-icon name="heroicon-o-clock" class="h-4 w-4 text-blue-500" />
                    Hoạt động trong 3 ngày tới
                </h3>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Check-ins -->
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Sắp nhận phòng (Check-in)</p>
                        @forelse($upcomingCheckins as $bk)
                            <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded border-b border-gray-100 last:border-0">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-800">{{ $bk->customer->name }}</span>
                                    <span class="text-[10px] text-gray-500">Phòng: {{ $bk->room->code }} | {{ $bk->check_in->format('d/m') }}</span>
                                </div>
                                <span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded font-bold">IN</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic py-2">Không có check-in</p>
                        @endforelse
                    </div>

                    <!-- Check-outs -->
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Sắp trả phòng (Check-out)</p>
                        @forelse($upcomingCheckouts as $bk)
                            <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded border-b border-gray-100 last:border-0">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-gray-800">{{ $bk->customer->name }}</span>
                                    <span class="text-[10px] text-gray-500">Phòng: {{ $bk->room->code }} | {{ $bk->check_out->format('d/m') }}</span>
                                </div>
                                <span class="text-[10px] bg-orange-100 text-orange-700 px-1.5 py-0.5 rounded font-bold">OUT</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic py-2">Không có check-out</p>
                        @endforelse
                    </div>
                </div>
            </x-ui.card>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Visa Expiry Alert -->
            <x-ui.card class="p-6">
                <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <x-icon name="heroicon-o-shield-exclamation" class="h-4 w-4 text-red-500" />
                    Hết hạn Visa (3 ngày tới)
                </h3>
                <div class="space-y-3">
                    @forelse($visaExpiries as $cus)
                        <div class="p-3 bg-white border border-gray-200 rounded-lg shadow-sm">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="text-xs font-bold text-gray-800">{{ $cus->name }}</p>
                                    <p class="text-[10px] text-red-600 font-semibold">Hết hạn: {{ $cus->visa_expiry->format('d/m/Y') }}</p>
                                </div>
                                <select wire:change="updateVisaStatus({{ $cus->id }}, $event.target.value)" class="text-[10px] border-gray-300 rounded px-1 py-0.5 bg-gray-50">
                                    <option value="1" {{ $cus->visa_status == 1 ? 'selected' : '' }}>1- Sắp hết hạn</option>
                                    <option value="2" {{ $cus->visa_status == 2 ? 'selected' : '' }}>2- Đã thông báo</option>
                                    <option value="3" {{ $cus->visa_status == 3 ? 'selected' : '' }}>3- Đã gia hạn</option>
                                </select>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic text-center py-4">Không có khách nào sắp hết hạn Visa</p>
                    @endforelse
                </div>
            </x-ui.card>

            <!-- Birthday Widget -->
            <x-ui.card class="p-6">
                <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <x-icon name="heroicon-o-gift" class="h-4 w-4 text-pink-500" />
                    Sinh nhật hôm nay
                </h3>
                <div class="space-y-3">
                    @forelse($birthdayCustomers as $cus)
                        <div class="flex items-center justify-between p-3 bg-pink-50 rounded-lg border border-pink-100">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold text-xs">
                                    {{ substr($cus->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-800">{{ $cus->name }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $cus->phone ?? '-' }}</p>
                                </div>
                            </div>
                            <span class="text-[10px] font-bold text-pink-600 bg-white px-2 py-0.5 rounded-full border border-pink-100">
                                {{ \Carbon\Carbon::parse($cus->birthday)->age }} tuổi
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic text-center py-4">Hôm nay không có sinh nhật</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    @else
        <!-- TÀI CHÍNH TAB -->
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold text-gray-800">Kết Quả Kinh Doanh</h2>
                <div class="flex items-center gap-2">
                    <select wire:model.live="filterMonth" class="text-sm border-gray-300 rounded-md py-1">
                        @for($i = 1; $i <= 12; $i++) <option value="{{ $i }}">Tháng {{ $i }}</option> @endfor
                    </select>
                    <select wire:model.live="filterYear" class="text-sm border-gray-300 rounded-md py-1">
                        @for($i = date('Y') - 2; $i <= date('Y') + 1; $i++) <option value="{{ $i }}">Năm {{ $i }}</option> @endfor
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Revenue -->
                <div class="p-6 rounded-xl border-2 border-rose-100 bg-rose-50/30">
                    <p class="text-xs font-bold text-rose-600 uppercase tracking-widest mb-2">Doanh thu dự kiến</p>
                    <p class="text-3xl font-black text-rose-700">{{ number_format($revenue, 0, ',', '.') }}đ</p>
                </div>

                <!-- Collected -->
                <div class="p-6 rounded-xl border-2 border-emerald-100 bg-emerald-50/30">
                    <p class="text-xs font-bold text-emerald-600 uppercase tracking-widest mb-2">Thực thu (Cọc/Thanh toán)</p>
                    <p class="text-3xl font-black text-emerald-700">{{ number_format($totalCollected, 0, ',', '.') }}đ</p>
                </div>

                <!-- Bookings -->
                <div class="p-6 rounded-xl border-2 border-amber-100 bg-amber-50/30">
                    <p class="text-xs font-bold text-amber-600 uppercase tracking-widest mb-2">Lượt Booking</p>
                    <p class="text-3xl font-black text-amber-700">{{ $totalBookings }}</p>
                </div>
            </div>
        </div>
    @endif
</div>
