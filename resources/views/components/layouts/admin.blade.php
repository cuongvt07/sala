<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Sala Admin' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: true }">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 bg-slate-900 transition-all duration-300 ease-in-out"
           :class="sidebarOpen ? 'w-64' : 'w-16'">
        
        <!-- Logo -->
        <div class="flex h-16 items-center justify-center bg-slate-950 px-4 shadow-sm space-x-2">
            <img src="{{ asset('sala.png') }}" alt="Sala Logo" class="h-8 w-8 transition-all duration-300" :class="sidebarOpen ? '' : 'mx-auto'">
            <span class="text-xl font-bold text-white transition-opacity duration-200"
                  x-show="sidebarOpen">
                SALA ADMIN
            </span>
        </div>

        <!-- Navigation -->
        <nav class="mt-4 flex flex-col gap-1 px-2">
            @php
                $navItems = [
                    ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
                    ['route' => 'admin.booking-calendar', 'label' => 'Lịch đặt phòng', 'icon' => 'calendar-days'],
                    ['route' => 'admin.bookings.index', 'label' => 'Danh sách Booking', 'icon' => 'list-bullet'],
                    ['route' => 'admin.areas.index', 'label' => 'Khu vực', 'icon' => 'map'],
                    ['route' => 'admin.rooms.index', 'label' => 'Phòng', 'icon' => 'building-office'],
                    ['route' => 'admin.customers.index', 'label' => 'Khách hàng', 'icon' => 'users'],
                    ['route' => 'admin.services.index', 'label' => 'Dịch vụ', 'icon' => 'wrench-screwdriver'],
                ];

                // Temporary helper for checking active route
                function isActive($route) {
                    return request()->routeIs($route);
                }
            @endphp

            @foreach($navItems as $item)
                <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                   class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors
                          {{ isActive($item['route']) ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                   title="{{ $item['label'] }}">
                    
                    <x-icon name="heroicon-o-{{ $item['icon'] }}" 
                            class="h-6 w-6 shrink-0 transition-colors {{ isActive($item['route']) ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" />
                    
                    <span class="ml-3 truncate transition-all duration-300"
                          :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">
                        {{ $item['label'] }}
                    </span>
                </a>
            @endforeach
        </nav>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="flex flex-col min-h-screen transition-all duration-300 ease-in-out"
         :class="sidebarOpen ? 'ml-64' : 'ml-16'">
        
        <!-- Topbar -->
        <header class="flex h-16 items-center justify-between bg-white px-6 shadow-sm sticky top-0 z-40">
            <!-- Sidebar Toggle -->
            <button @click="sidebarOpen = !sidebarOpen" 
                    class="rounded-md p-2 text-gray-500 hover:bg-gray-100 focus:outline-none">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Center: Global Area Selector -->
            <div class="flex-1 flex justify-center">
                @livewire('admin.partials.global-area-selector')
            </div>

            <!-- Right Actions -->
            <div class="flex items-center gap-4">
                <div class="text-sm font-medium text-gray-700">Admin User</div>
                <div class="h-8 w-8 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold">
                    A
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
