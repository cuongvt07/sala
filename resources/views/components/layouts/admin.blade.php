<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Hệ thống vận hành Sala Apartment' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
        .flatpickr-calendar { font-size: 13px !important; }
    </style>
</head>

<body class="bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: true }">
    {{-- Thanh tiến trình tải toàn cục: phản hồi tức thì cho mọi thao tác Livewire (kể cả mở popup) --}}
    <div id="global-loading-bar"
         style="position:fixed;top:0;left:0;height:3px;width:0;opacity:0;z-index:9999;
                background:linear-gradient(90deg,#3b82f6,#6366f1,#8b5cf6);box-shadow:0 0 10px rgba(59,130,246,.6);
                transition:width .25s ease,opacity .3s ease;pointer-events:none;"></div>
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 bg-slate-900 transition-all duration-300 ease-in-out"
        :class="sidebarOpen ? 'w-64' : 'w-16'">

        <!-- Logo -->
        <div class="flex h-16 items-center justify-center bg-slate-950 px-4 shadow-sm space-x-2">
            <img src="{{ asset('sala.png') }}" alt="Sala Logo" class="h-8 w-8 transition-all duration-300"
                :class="sidebarOpen ? '' : 'mx-auto'">
            <span
                class="text-sm tracking-wide font-black text-white transition-opacity duration-200 leading-tight uppercase text-center"
                x-show="sidebarOpen">
                Hệ thống vận hành<br>SALA APARTMENT
            </span>
        </div>

        <!-- Navigation -->
        <nav class="mt-4 flex flex-col gap-1 px-2" x-data="{ openGroups: [] }">
            @php
                $navItems = [
                    ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
                    ['route' => 'admin.booking-calendar', 'label' => 'Lịch đặt phòng', 'icon' => 'calendar-days'],
                    ['route' => 'admin.bookings.index', 'label' => 'Quản lý đặt phòng', 'icon' => 'list-bullet'],
                    ['route' => 'admin.customers.index', 'label' => 'Quản lý khách hàng', 'icon' => 'users'],
                    [
                        'label' => 'Cấu hình quản trị',
                        'icon' => 'cog-6-tooth',
                        'children' => [
                            ['route' => 'admin.services.index', 'label' => 'Dịch vụ', 'icon' => 'wrench-screwdriver'],
                            ['route' => 'admin.areas.index', 'label' => 'Tòa nhà', 'icon' => 'map'],
                            ['route' => 'admin.rooms.index', 'label' => 'Phòng', 'icon' => 'building-office'],
                            ['route' => 'admin.room-maintenances.index', 'label' => 'Bảo dưỡng phòng', 'icon' => 'clipboard-document-check'],
                            ['route' => 'admin.settings.index', 'label' => 'Cài đặt hệ thống', 'icon' => 'cog-8-tooth'],
                        ]
                    ],
                    [
                        'label' => 'Nhân sự & Bảo mật',
                        'icon' => 'shield-check',
                        'children' => [
                            ['route' => 'admin.staff.index', 'label' => 'Quản lý nhân sự', 'icon' => 'user-group'],
                            ['route' => 'admin.activity-logs.index', 'label' => 'Nhật ký hoạt động', 'icon' => 'clipboard-document-list'],
                        ]
                    ],
                ];
            @endphp

            @foreach($navItems as $item)
                @if(isset($item['children']))
                    @php
                        // Filter children by permission
                        $visibleChildren = array_filter($item['children'], function($child) {
                            return auth()->user()->hasPermission($child['route']);
                        });

                        if (empty($visibleChildren)) continue;

                        // Check if any child is active
                        $isActiveGroup = false;
                        foreach ($visibleChildren as $child) {
                            if (request()->routeIs($child['route'])) {
                                $isActiveGroup = true;
                                break;
                            }
                        }
                    @endphp
                    <div x-data="{ expanded: {{ $isActiveGroup ? 'true' : 'false' }} }">
                        <button @click="expanded = !expanded; if(!sidebarOpen) sidebarOpen = true"
                            class="w-full group flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors text-slate-300 hover:bg-slate-800 hover:text-white justify-between">
                            <div class="flex items-center overflow-hidden">
                                <x-icon name="heroicon-o-{{ $item['icon'] }}"
                                    class="h-6 w-6 shrink-0 transition-colors {{ $isActiveGroup ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" />
                                <span class="ml-3 truncate transition-all duration-300"
                                    :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">
                                    {{ $item['label'] }}
                                </span>
                            </div>
                            <x-icon name="heroicon-o-chevron-down" class="h-4 w-4 transition-transform duration-200"
                                ::class="expanded ? 'rotate-180' : ''" x-show="sidebarOpen" />
                        </button>

                        {{-- Children --}}
                        <div x-show="expanded && sidebarOpen" x-collapse class="pl-4 mt-1 space-y-1">
                            @foreach($visibleChildren as $child)
                                <a href="{{ route($child['route']) }}"
                                    class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors
                                                      {{ request()->routeIs($child['route']) ? 'bg-blue-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}"
                                    title="{{ $child['label'] }}">
                                    <x-icon name="heroicon-o-{{ $child['icon'] }}" class="h-5 w-5 shrink-0" />
                                    <span class="ml-3 truncate">{{ $child['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- Single Item --}}
                    @if(auth()->user()->hasPermission($item['route']))
                        <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
                            class="group flex items-center rounded-lg px-3 py-2 text-sm font-medium transition-colors
                                          {{ request()->routeIs($item['route']) ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}" title="{{ $item['label'] }}">

                            <x-icon name="heroicon-o-{{ $item['icon'] }}"
                                class="h-6 w-6 shrink-0 transition-colors {{ request()->routeIs($item['route']) ? 'text-white' : 'text-slate-400 group-hover:text-white' }}" />

                            <span class="ml-3 truncate transition-all duration-300"
                                :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0 overflow-hidden'">
                                {{ $item['label'] }}
                            </span>
                        </a>
                    @endif
                @endif
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
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition-all hover:bg-red-50 hover:text-red-600 group"
                        title="Đăng xuất">
                        <x-icon name="heroicon-o-arrow-right-on-rectangle"
                            class="h-5 w-5 text-gray-400 group-hover:text-red-500" />
                        <span class="hidden md:inline">Đăng xuất</span>
                    </button>
                </form>

                <div class="h-6 w-px bg-gray-200 mx-2"></div>

                <div class="flex items-center gap-3">
                    <div class="text-sm font-semibold text-gray-700 hidden sm:block">Admin User</div>
                    <div
                        class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md ring-2 ring-white ring-offset-2">
                        A
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
    <x-ui.toast />
    <script>
        // Global loading bar cho mọi request Livewire (mở modal, lưu, lọc, phân trang...)
        document.addEventListener('livewire:init', () => {
            const bar = document.getElementById('global-loading-bar');
            if (!bar || typeof Livewire === 'undefined') return;
            let hideTimer;
            const start = () => {
                clearTimeout(hideTimer);
                bar.style.opacity = '1';
                bar.style.width = '0';
                requestAnimationFrame(() => { bar.style.width = '80%'; });
            };
            const done = () => {
                bar.style.width = '100%';
                hideTimer = setTimeout(() => { bar.style.opacity = '0'; bar.style.width = '0'; }, 250);
            };
            Livewire.hook('commit', ({ respond }) => { start(); respond(() => done()); });
        });

        window.onerror = function (message, source, lineno, colno, error) {
            console.error('JS Error Captured:', message, 'at', source, ':', lineno, ':', colno);
            console.error(error);
        };

        document.addEventListener('DOMContentLoaded', () => {
            @if(session('success'))
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: "{{ session('success') }}", type: 'success' } }));
            @endif
            @if(session('error'))
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: "{{ session('error') }}", type: 'error' } }));
            @endif
            @if(session('message'))
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: "{{ session('message') }}", type: 'success' } }));
            @endif
        });

        window.printBookingConfirmation = function (elementId) {
            var el = document.getElementById(elementId);
            if (!el) { alert('Không tìm thấy nội dung để in.'); return; }
            var billContent = el.innerHTML;
            var styles = ':root{--gold:#b8975a;--dark:#1a1a1a;--mid:#444;--light:#f8f5f0;--border:#d4c9b0;--accent:#8b7340}*{box-sizing:border-box;margin:0;padding:0}body{background:#fff;font-family:"Source Serif 4",Georgia,serif;color:#1a1a1a;-webkit-print-color-adjust:exact;print-color-adjust:exact}.booking-confirmation-page{background:#fff;width:100%;padding:80px 100px;position:relative;overflow:hidden;color:#1a1a1a}.booking-confirmation-page::before{content:"S";position:absolute;font-family:"Playfair Display",serif;font-size:400px;color:rgba(184,151,90,.04);top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none;line-height:1}.header-lux{display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;gap:20px}.logo-area{display:flex;align-items:center;gap:14px;flex-shrink:0}.logo-img{width:100px ;height:auto;object-fit:contain}.hotel-info{text-align:center;flex:1}.hotel-name{font-family:"Playfair Display",serif;font-size:20px;font-weight:700;color:#1a1a1a;letter-spacing:.05em;text-transform:uppercase}.hotel-contact{font-size:12.5px;color:#444;line-height:2;margin-top:4px}.qr-box-lux{width:64px;height:64px;border:1.5px solid #d4c9b0;flex-shrink:0;display:flex;align-items:center;justify-content:center;overflow:hidden}.qr-box-lux img{width:100%;height:100%;object-fit:contain}.divider-lux{display:flex;align-items:center;gap:12px;margin:4px 0 28px}.divider-line{flex:1;height:1px;background:#d4c9b0}.divider-diamond{width:8px;height:8px;background:#b8975a;transform:rotate(45deg);flex-shrink:0}.title-block{text-align:center;margin-bottom:28px}.title-label{font-family:"Playfair Display",serif;font-size:26px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:#1a1a1a}.title-sub{font-size:12.5px;color:#444;letter-spacing:.08em;margin-top:4px;font-style:italic}.intro{font-size:13.5px;line-height:1.8;text-align:center;color:#444;margin-bottom:28px;font-style:italic}.details-table{width:100%;border-collapse:collapse;font-size:13.5px;margin-bottom:28px}.details-table td{padding:14px 18px;border:1px solid #d4c9b0;vertical-align:top;line-height:1.6}.details-table .label{color:#444;font-weight:300;white-space:nowrap;width:25%}.details-table .value{font-weight:600;color:#1a1a1a;letter-spacing:.02em}.price-note{font-size:11.5px;color:#444;font-weight:300;font-style:italic;margin-top:4px}.paid-badge{display:inline-block;background:#e8f4ec;color:#2e7d4f;border:1px solid #a8d5b5;padding:1px 10px;border-radius:20px;font-size:11px;letter-spacing:.06em;text-transform:uppercase;font-weight:600;margin-left:8px;vertical-align:middle;font-style:normal}.signature-lux{text-align:right;margin-top:32px;padding-top:20px}.sig-title{font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#444;font-weight:300}.sig-name{font-family:"Playfair Display",serif;font-size:16px;color:#1a1a1a;margin-top:4px;font-style:italic}.sig-company{font-size:12px;color:#b8975a;letter-spacing:.1em;text-transform:uppercase;margin-top:2px}.sig-line{width:160px;height:1px;background:#d4c9b0;margin:14px 0 8px auto}@media print{@page{margin:1cm}body{margin:0}}';
            var pw = window.open('', '_blank', 'width=800,height=900');
            pw.document.write('<html><head><meta charset="utf-8"><title>Booking Confirmation</title><link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Source+Serif+4:ital,wght@0,300;0,400;0,600;1,300&display=swap" rel="stylesheet"><style>' + styles + '</style></head><body>' + billContent + '</body></html>');
            pw.document.close();
            pw.onload = function () { setTimeout(function () { pw.focus(); pw.print(); pw.close(); }, 500); };
        };
    </script>
</body>

</html>