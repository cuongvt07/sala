<div class="space-y-4 relative">
    {{-- Hiệu ứng Loading toàn trang khi đổi khu hoặc thao tác --}}
    <div wire:loading wire:target="nextMonth, prevMonth, goToToday, filter_areas, filter_start_date, filter_end_date" class="absolute inset-0 z-50 flex items-center justify-center bg-white/40 backdrop-blur-[2px] transition-all duration-300">
        <div class="flex flex-col items-center">
            <div class="w-10 h-10 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div>
            <span class="mt-2 text-xs font-bold text-blue-600 uppercase tracking-widest animate-pulse">Đang tải dữ liệu...</span>
        </div>
    </div>
    <style>
        /* Custom styles for Booking Calendar Grid */
        .booking-calendar-container {
            position: relative;
            overflow: auto;
            max-height: calc(100vh - 180px);
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background: #fff;
        }

        .booking-grid {
            display: grid;
            min-width: max-content;
        }

        /* Sticky Room Column */
        .room-cell {
            position: sticky;
            left: 0;
            z-index: 20;
            background: #fff;
            border-right: 2px solid #9ca3af;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Sticky Header Row */
        .header-cell {
            position: sticky;
            top: 0;
            z-index: 30;
            background: #f3f4f6;
            border-bottom: 2px solid #9ca3af;
            border-right: 1px solid #e5e7eb;
        }

        /* Corner Cell */
        .corner-cell {
            position: sticky;
            top: 0;
            left: 0;
            z-index: 40;
            background: #e5e7eb;
            border-right: 2px solid #9ca3af;
            border-bottom: 2px solid #9ca3af;
        }

        /* Day Cells */
        .day-cell {
            border-right: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            min-height: 52px;
            transition: background-color 0.1s ease;
        }

        .day-cell:hover {
            background-color: #dbeafe !important;
        }

        .day-cell.weekend {
            background-color: #fef3c7;
        }

        .day-cell.today {
            background-color: #bfdbfe;
        }

        /* Area Header */
        .area-header {
            background: #1e3a5f;
            color: #fff;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.7rem;
            padding: 6px 12px;
        }

        /* Booking Bar */
        .booking-bar {
            margin: 3px 2px;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.1s ease;
            display: flex;
            align-items: center;
            overflow: hidden;
            white-space: nowrap;
            border: 1px solid rgba(0,0,0,0.1);
        }

        .booking-bar:hover {
            transform: scale(1.02);
            z-index: 25;
        }

        /* Status Colors */

        .booking-pending { background: #facc15; color: #713f12; }
        .booking-checked_in { background: #22c55e; color: #fff; }
        .booking-checked_out { background: #6b7280; color: #fff; }
        .booking-cancelled { background: #ef4444; color: #fff; }

        /* Room Info */
        .room-info {
            padding: 6px 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 52px;
            background: #fafafa;
        }

        .room-code { font-weight: 700; color: #111827; font-size: 0.8rem; }
        .room-type { font-size: 0.65rem; color: #6b7280; }
        .room-price { font-size: 0.6rem; color: #9ca3af; }

        .status-dot {
            width: 6px; height: 6px; border-radius: 50%;
            display: inline-block; margin-right: 4px;
        }

        /* Legend Badge */
        .legend-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 500;
        }

    </style>

    <!-- Header & Controls -->
    <div class="flex flex-col gap-4">
        <div class="flex flex-wrap justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100 gap-4">
                    <h2 class="text-2xl font-bold text-gray-800">
                    Từ {{ \Carbon\Carbon::parse($startDate ?? now())->format('d/m/Y') }} đến {{ \Carbon\Carbon::parse($startDate ?? now())->addDays(29)->format('d/m/Y') }}
                </h2>

            <div class="flex gap-2">
                <button wire:click="prevMonth" class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                    ‹ 30 ngày trước
                </button>
                <button wire:click="goToToday" class="px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                    Hôm nay
                </button>
                <button wire:click="nextMonth" class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                    30 ngày sau ›
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-8 items-start md:items-center transition-all duration-300 hover:shadow-md">
            <!-- Khu vực/Toà nhà -->
            <div class="flex-1 space-y-3">
                <div class="flex items-center gap-2 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-10V4m0 10V4m-4 10h4m-4 4h4m-4 4h4m-4 0V4"></path></svg>
                    <label class="text-[10px] font-black uppercase tracking-widest">Lọc theo toà nhà</label>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($areas as $area)
                        <label class="relative flex items-center group cursor-pointer select-none">
                            <input type="checkbox" wire:model.live="filter_areas" value="{{ $area->id }}" class="sr-only peer">
                            <div class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 border
                                peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600 peer-checked:shadow-lg peer-checked:shadow-blue-200
                                bg-gray-50 text-gray-500 border-gray-100 group-hover:bg-gray-100 group-hover:border-gray-200
                                flex items-center gap-2">
                                <span class="hidden peer-checked:inline-block">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </span>
                                {{ $area->name }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Dải phân cách (Desktop) -->
            <div class="hidden md:block w-px h-12 bg-gray-100"></div>

            <!-- Tìm phòng trống -->
            <div class="flex flex-wrap items-center gap-4">
                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <label class="text-[10px] font-black uppercase tracking-widest text-blue-600">Tìm phòng trống theo ngày</label>
                    </div>
                    <div class="flex items-center gap-2 bg-gray-50 p-1 rounded-xl border border-gray-100">
                        <input type="text" inputmode="numeric" wire:model.blur="filter_start_date" placeholder="dd/mm/yyyy"
                               x-data
                               x-on:input="let v=$el.value.replace(/\D/g,'').slice(0,8); $el.value = v.length>=5 ? v.slice(0,2)+'/'+v.slice(2,4)+'/'+v.slice(4) : (v.length>=3 ? v.slice(0,2)+'/'+v.slice(2) : v)"
                               class="bg-transparent border-none text-xs font-bold text-gray-700 focus:ring-0 w-[110px] cursor-pointer text-center"
                               title="Từ ngày (ngày/tháng/năm)">
                        <span class="text-gray-300 text-xs">→</span>
                        <input type="text" inputmode="numeric" wire:model.blur="filter_end_date" placeholder="dd/mm/yyyy"
                               x-data
                               x-on:input="let v=$el.value.replace(/\D/g,'').slice(0,8); $el.value = v.length>=5 ? v.slice(0,2)+'/'+v.slice(2,4)+'/'+v.slice(4) : (v.length>=3 ? v.slice(0,2)+'/'+v.slice(2) : v)"
                               class="bg-transparent border-none text-xs font-bold text-gray-700 focus:ring-0 w-[110px] cursor-pointer text-center"
                               title="Đến ngày (ngày/tháng/năm)">
                    </div>
                </div>
                
                <!-- Xoá lọc -->
                @if(!empty($filter_areas) || !empty($filter_start_date) || !empty($filter_end_date))
                    <button wire:click="$set('filter_areas', []); $set('filter_start_date', ''); $set('filter_end_date', '');"
                            class="mt-6 p-2 text-red-500 hover:bg-red-50 rounded-xl transition-all duration-200 group"
                            title="Xoá tất cả bộ lọc">
                        <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                @endif
            </div>

            {{-- ===== GỢI Ý NỐI PHÒNG (khi không có phòng trống suốt kỳ) ===== --}}
            @if(!empty($roomChain) && !$roomChain['single'] && count($roomChain['segments']) > 0)
                <div class="mt-3 rounded-xl border p-3 {{ $roomChain['possible'] ? 'bg-amber-50 border-amber-200' : 'bg-red-50 border-red-200' }}">
                    @if($roomChain['possible'])
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-[11px] font-black uppercase tracking-widest text-amber-700">🔗 Gợi ý nối phòng</span>
                            <span class="text-[10px] text-amber-600">Không có phòng trống suốt kỳ, nhưng có thể ghép:</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach($roomChain['segments'] as $i => $seg)
                                @if($i > 0)<span class="text-amber-400 font-black">→</span>@endif
                                <button type="button" wire:click="$dispatch('new-booking', { roomId: {{ $seg['room_id'] }}, date: '{{ $seg['from'] }}' })"
                                        class="px-3 py-1.5 bg-white border border-amber-300 rounded-lg hover:bg-amber-100 transition-all shadow-sm text-left"
                                        title="Đặt phòng {{ $seg['room_code'] }} cho đoạn này">
                                    <span class="block text-xs font-black text-gray-800">Phòng {{ $seg['room_code'] }}</span>
                                    <span class="block text-[10px] text-gray-500">{{ $seg['from_label'] }} → {{ $seg['to_label'] }} · {{ $seg['nights'] }} đêm @if($seg['area']) · {{ $seg['area'] }}@endif</span>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <span class="text-[11px] font-bold text-red-600">Không tìm được cách ghép phòng phủ kín khoảng ngày này (kể cả nối phòng).</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap gap-2">

        <span class="legend-badge bg-yellow-400 text-yellow-900 shadow-sm">Chờ lấy phòng</span>
        <span class="legend-badge bg-green-500 text-white shadow-sm">Đã nhận phòng</span>
        <span class="text-[11px] text-gray-400 self-center">Bấm ô trống để đặt phòng · bấm thanh booking để xem/sửa/thu tiền</span>
    </div>

    <!-- Calendar Grid -->
    <div class="booking-calendar-container shadow-sm">
        @php
            $daysCount = count($this->daysInMonth);
            $gridTemplate = "150px repeat({$daysCount}, 48px)";
            $rowIndex = 1;
        @endphp

        <div class="booking-grid" style="grid-template-columns: {{ $gridTemplate }};">
            
            {{-- Corner Cell --}}
            <div class="corner-cell flex items-center justify-center font-bold text-gray-600 text-xs"
                 style="grid-column: 1; grid-row: 1; height: 50px;">
                Phòng
            </div>
            
            {{-- Header Row (Days) --}}
            @foreach($this->daysInMonth as $index => $day)
                @php
                    $isToday = $day->isToday();
                    $isWeekend = $day->isWeekend();
                @endphp
                <div class="header-cell flex flex-col items-center justify-center 
                            {{ $isToday ? 'bg-blue-200' : '' }} 
                            {{ $isWeekend && !$isToday ? 'bg-amber-100' : '' }}"
                     style="grid-column: {{ $index + 2 }}; grid-row: 1; height: 50px;">
                    <span class="text-lg font-black {{ $isToday ? 'text-blue-700' : ($isWeekend ? 'text-orange-600' : 'text-gray-900') }}">
                        {{ $day->format('d') }}
                    </span>
                    <span class="text-[10px] uppercase tracking-wide {{ $isWeekend ? 'text-orange-500 font-semibold' : 'text-gray-400' }}">
                        {{ $day->locale('vi')->isoFormat('ddd') }}
                    </span>
                </div>
            @endforeach

            @php $rowIndex = 2; @endphp

            @foreach($roomsData as $areaName => $rooms)
                <div wire:key="area-{{ Str::slug($areaName) }}" class="contents">
                {{-- Area Header --}}
                <div class="area-header" style="grid-column: 1 / -1; grid-row: {{ $rowIndex }};">
                    {{ $areaName }}
                </div>
                @php $rowIndex++; @endphp

                @foreach($rooms as $room)
                    <div wire:key="room-row-{{ $room->id }}" class="contents group">
                    @php
                        // Single row layout, fixed height
                        $rowHeight = 52; 
                    @endphp

                    {{-- Room Info Cell --}}
                    <div class="room-cell room-info" style="grid-column: 1; grid-row: {{ $rowIndex }}; height: {{ $rowHeight }}px;">
                        <div class="flex items-center gap-1">
                            @php
                                $statusColor = match($room->status) {
                                    'active' => 'bg-green-500',
                                    'maintenance' => 'bg-red-500',
                                    'available' => 'bg-green-500', // Fallback
                                    'occupied' => 'bg-green-500', // Fallback
                                    default => 'bg-gray-400'
                                };
                            @endphp
                            <span class="w-2 h-2 rounded-full {{ $statusColor }}"></span>
                            <span class="room-code">{{ $room->code }}</span>
                        </div>
                        <span class="room-type">{{ $room->type }}</span>
                        <span class="room-price">{{ number_format($room->price_day, 0, ',', '.') }}đ</span>
                    </div>

                    {{-- Day Cells Background --}}
                    @foreach($this->daysInMonth as $dayIndex => $day)
                        <div wire:click="$dispatch('new-booking', { roomId: {{ $room->id }}, date: '{{ $day->format('Y-m-d') }}' })"
                             class="day-cell {{ $day->isWeekend() ? 'weekend' : '' }}"
                             style="grid-column: {{ $dayIndex + 2 }}; grid-row: {{ $rowIndex }}; height: {{ $rowHeight }}px;">
                        </div>
                    @endforeach

                    {{-- Bookings Overlay Container --}}
                    <div class="relative w-full h-full pointer-events-none custom-scrollbar-hide overflow-hidden" 
                         style="grid-column: 2 / -1; grid-row: {{ $rowIndex }}; height: {{ $rowHeight }}px;">
                        
                        @foreach($room->bookings as $booking)
                            @php
                                $dayWidth = 48; // px
                                $left = $booking->visual_start * $dayWidth;
                                $width = max(12, $booking->visual_days * $dayWidth); // Min 12px
                                $top = 10; // Centered vertically in 52px (height 28 -> margin 12 top/bottom)

                                $statusClasses = [
                                    'pending' => 'bg-yellow-400 text-yellow-900',
                                    'checked_in' => 'bg-green-500 text-white',
                                    'checked_out' => 'bg-gray-500 text-white',
                                    'cancelled' => 'bg-red-500 text-white',
                                ];
                                $bkClass = $statusClasses[$booking->status] ?? 'bg-gray-400 text-white';
                            @endphp

                            <div wire:key="booking-bar-{{ $booking->id }}" wire:click="$dispatch('open-booking', { id: {{ $booking->id }} })"
                                 class="booking-bar {{ $bkClass }} group absolute pointer-events-auto shadow-sm hover:brightness-110 hover:shadow-md hover:scale-[1.01] transition-all duration-200 ease-in-out cursor-pointer z-10 hover:z-20"
                                 style="left: {{ $left }}px; width: {{ $width }}px; top: {{ $top }}px; height: 28px;"
                                 title="{{ $booking->customer->name ?? '' }} · {{ $booking->check_in->format('d/m') }} → {{ $booking->check_out?->format('d/m') ?? 'HĐ' }}">
                                <div class="px-2 w-full flex flex-col leading-tight h-full justify-center text-white relative select-none">
                                    {{-- Name: Visible if space permits --}}
                                    <span class="font-bold uppercase text-[10px] truncate flex items-center gap-1">
                                        {{ $booking->customer->name ?? '' }}
                                        @if(!empty($booking->additional_guests))
                                            <span class="bg-white/20 px-1 rounded text-[8px]">+{{ count($booking->additional_guests) }}</span>
                                        @endif
                                        @if($booking->source)
                                            ({{ $booking->source }})
                                        @endif
                                    </span>
                                    
                                    {{-- Dates: Only if width > 60px --}}
                                    @if($width > 60)
                                        <span class="text-[8.5px] opacity-90 whitespace-nowrap truncate">
                                            {{ $booking->check_in->format('d/m') }} - {{ $booking->check_out?->format('d/m') ?? 'HĐ' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @php $rowIndex++; @endphp
                @endforeach
            @endforeach
        </div>
    </div>

    {{-- Form đặt phòng dùng chung (giống hệt trang Quản lý đặt phòng) --}}
    <livewire:admin.booking-form />
</div>
