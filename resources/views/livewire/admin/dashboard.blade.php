<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Dashboard</h1>
        <span class="text-sm font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Tổng quan hệ thống</span>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Areas -->
        <x-ui.card class="p-6 flex items-center justify-between border-l-4 border-l-blue-500">
            <div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Khu vực</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalAreas }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                <x-icon name="heroicon-o-map" class="h-6 w-6" />
            </div>
        </x-ui.card>

        <!-- Rooms -->
        <x-ui.card class="p-6 flex items-center justify-between border-l-4 border-l-indigo-500">
            <div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Phòng</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <p class="text-3xl font-bold text-gray-800">{{ $totalRooms }}</p>
                    <span class="text-xs text-green-600 font-medium bg-green-50 px-1.5 py-0.5 rounded">Tổng số</span>
                </div>
            </div>
            <div class="h-12 w-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                <x-icon name="heroicon-o-building-office" class="h-6 w-6" />
            </div>
        </x-ui.card>

        <!-- Customers -->
        <x-ui.card class="p-6 flex items-center justify-between border-l-4 border-l-emerald-500">
            <div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Khách hàng</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalCustomers }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                <x-icon name="heroicon-o-users" class="h-6 w-6" />
            </div>
        </x-ui.card>

        <!-- Bookings -->
        <x-ui.card class="p-6 flex items-center justify-between border-l-4 border-l-amber-500">
            <div>
                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Booking</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalBookings }}</p>
            </div>
            <div class="h-12 w-12 rounded-full bg-amber-50 flex items-center justify-center text-amber-600">
                <x-icon name="heroicon-o-calendar-days" class="h-6 w-6" />
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
        
        <!-- Placeholder for Recent Activity or Chart -->
        <x-ui.card class="md:col-span-2 p-6 flex items-center justify-center text-gray-400 border-dashed border-2 border-gray-200">
            <div class="text-center">
                <x-icon name="heroicon-o-chart-bar" class="h-12 w-12 mx-auto text-gray-300 mb-2" />
                <span class="font-medium">Biểu đồ thống kê doanh thu (Coming Soon)</span>
            </div>
        </x-ui.card>
    </div>
</div>
