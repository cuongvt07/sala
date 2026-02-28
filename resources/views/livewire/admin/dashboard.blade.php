<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Dashboard</h1>
            <span class="text-sm font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Tổng quan hệ thống</span>
        </div>
        <div class="flex items-center gap-2 bg-white p-2 rounded-lg shadow-sm border border-gray-100">
            <span class="text-xs font-semibold text-gray-500 uppercase hidden sm:block">Bộ lọc kinh doanh:</span>
            <select wire:model.live="filterMonth" class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 py-1.5 pl-3 pr-8 bg-gray-50 font-medium">
                @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}">Tháng {{ $i }}</option>
                @endfor
            </select>
            <select wire:model.live="filterYear" class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 py-1.5 pl-3 pr-8 bg-gray-50 font-medium">
                @for($i = date('Y') - 5; $i <= date('Y') + 2; $i++)
                    <option value="{{ $i }}">Năm {{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>

    <!-- TỔNG QUAN KINH DOANH TRONG THÁNG -->
    <h2 class="text-lg font-bold text-gray-700 mt-2 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
        Kết Quả Kinh Doanh (Tháng {{ $filterMonth }}/{{ $filterYear }})
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Revenue -->
        <x-ui.card class="p-6 flex items-center justify-between border-l-4 border-l-rose-500 bg-gradient-to-br from-white to-rose-50/30">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Doanh thu phòng ước tính</p>
                <p class="text-2xl font-black text-rose-600 mt-2">{{ number_format($revenue, 0, ',', '.') }}đ</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </x-ui.card>

        <!-- Collected -->
        <x-ui.card class="p-6 flex items-center justify-between border-l-4 border-l-emerald-500 bg-gradient-to-br from-white to-emerald-50/30">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Thực thu (Đã thanh toán/cọc)</p>
                <p class="text-2xl font-black text-emerald-600 mt-2">{{ number_format($totalCollected, 0, ',', '.') }}đ</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </x-ui.card>

        <!-- Bookings -->
        <x-ui.card class="p-6 flex items-center justify-between border-l-4 border-l-amber-500 bg-gradient-to-br from-white to-amber-50/30">
            <div>
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Lượt Booking Mới</p>
                <p class="text-2xl font-black text-amber-600 mt-2">{{ $totalBookings }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 shadow-sm">
                <x-icon name="heroicon-o-calendar-days" class="h-6 w-6" />
            </div>
        </x-ui.card>
    </div>

    <!-- TỔNG QUAN HỆ SINH THÁI -->
    <h2 class="text-lg font-bold text-gray-700 mt-6 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        Hệ Sinh Thái Hệ Thống
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Areas -->
        <x-ui.card class="p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Tòa nhà</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalAreas }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                <x-icon name="heroicon-o-map" class="h-6 w-6" />
            </div>
        </x-ui.card>

        <!-- Rooms -->
        <x-ui.card class="p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Phòng</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <p class="text-3xl font-bold text-gray-800">{{ $totalRooms }}</p>
                    <span class="text-xs text-green-600 font-medium bg-green-50 px-1.5 py-0.5 rounded border border-green-100">Tổng số</span>
                </div>
            </div>
            <div class="h-12 w-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                <x-icon name="heroicon-o-building-office" class="h-6 w-6" />
            </div>
        </x-ui.card>

        <!-- Customers -->
        <x-ui.card class="p-6 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Khách hàng</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalCustomers }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                <x-icon name="heroicon-o-users" class="h-6 w-6" />
            </div>
        </x-ui.card>
    </div>

    <!-- Booking Status Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-ui.card class="p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                <x-icon name="heroicon-o-chart-pie" class="h-5 w-5 text-gray-500" />
                Trạng thái Booking
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="flex items-center gap-3 text-sm font-medium text-gray-700">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500 ring-4 ring-green-100"></span>
                        Đang ở (Checked-in)
                    </span>
                    <span class="font-bold text-gray-900 bg-white px-2 py-1 rounded shadow-sm">{{ $activeBookings }}</span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <span class="flex items-center gap-3 text-sm font-medium text-gray-700">
                        <span class="w-2.5 h-2.5 rounded-full bg-yellow-500 ring-4 ring-yellow-100"></span>
                        Đang chờ (Pending)
                    </span>
                    <span class="font-bold text-gray-900 bg-white px-2 py-1 rounded shadow-sm">{{ $pendingBookings }}</span>
                </div>
            </div>
        </x-ui.card>
        
        <!-- Birthday Widget -->
        <x-ui.card class="md:col-span-2 p-6 flex flex-col">
            <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 text-pink-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"></path></svg>
                Sinh nhật Khách hàng hôm nay
            </h3>
            
            @if($birthdayCustomers->count() > 0)
                <div class="space-y-3 flex-1 overflow-y-auto pr-2">
                    @foreach($birthdayCustomers as $cus)
                        <div class="flex items-center justify-between p-3 bg-pink-50/50 border border-pink-100 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold">
                                    {{ substr($cus->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $cus->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $cus->phone ?? 'Không có SĐT' }}</p>
                                </div>
                            </div>
                            <div class="text-sm font-medium text-pink-600 bg-white px-3 py-1 rounded-full shadow-sm border border-pink-50">
                                Tuổi: {{ \Carbon\Carbon::parse($cus->birthday)->age }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-gray-400 py-6">
                    <svg class="w-12 h-12 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="font-medium text-sm">Hôm nay không có khách hàng nào sinh nhật</span>
                </div>
            @endif
        </x-ui.card>
    </div>
</div>
