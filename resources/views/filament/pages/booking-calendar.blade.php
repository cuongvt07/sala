<x-filament-panels::page>
    <style>
        /* Custom styles for Booking Calendar Grid */
        .booking-calendar-container {
            position: relative;
            overflow: auto;
            max-height: calc(100vh - 200px);
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
        .booking-confirmed { background: #3b82f6; color: #fff; }
        .booking-pending { background: #fbbf24; color: #78350f; }
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
        .status-available { background: #22c55e; }
        .status-occupied { background: #ef4444; }
        .status-maintenance { background: #f59e0b; }
        .status-reserved { background: #3b82f6; }

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

    <div class="flex flex-col gap-3">
        {{-- Header Controls --}}
        <div class="flex flex-wrap justify-between items-center bg-white p-3 rounded-lg shadow-sm border gap-3">
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-bold text-gray-800">
                    {{ \Carbon\Carbon::create($year, $month, 1)->locale('vi')->isoFormat('MMMM YYYY') }}
                </h2>
                <select wire:model.live="selectedArea" 
                        class="rounded-md border-gray-300 py-1.5 px-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Tất cả khu vực</option>
                    @foreach(\App\Models\Area::all() as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-1">
                <x-filament::button wire:click="prevMonth" color="gray" size="xs">‹ Trước</x-filament::button>
                <x-filament::button wire:click="$set('month', {{ now()->month }}); $set('year', {{ now()->year }})" color="primary" size="xs">Hôm nay</x-filament::button>
                <x-filament::button wire:click="nextMonth" color="gray" size="xs">Sau ›</x-filament::button>
            </div>
        </div>

        {{-- Legend with colored badges --}}
        <div class="flex flex-wrap gap-2 text-xs">
            <span class="legend-badge bg-blue-500 text-white">Đã xác nhận</span>
            <span class="legend-badge bg-yellow-400 text-yellow-900">Đang chờ</span>
            <span class="legend-badge bg-green-500 text-white">Đã nhận phòng</span>
            <span class="legend-badge bg-gray-500 text-white">Đã trả phòng</span>
            <span class="legend-badge bg-red-500 text-white">Đã hủy</span>
        </div>

        {{-- Grid --}}
        <div class="booking-calendar-container">
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

                {{-- Rooms & Bookings --}}
                @foreach($this->rooms as $areaName => $rooms)
                    {{-- Area Header --}}
                    <div class="area-header" style="grid-column: 1 / -1; grid-row: {{ $rowIndex }};">
                        {{ $areaName }}
                    </div>
                    @php $rowIndex++; @endphp

                    @foreach($rooms as $room)
                        {{-- Room Info Cell --}}
                        <div class="room-cell room-info" style="grid-column: 1; grid-row: {{ $rowIndex }};">
                            <div class="flex items-center gap-1">
                                <span class="status-dot status-{{ $room->status }}"></span>
                                <span class="room-code">{{ $room->code }}</span>
                            </div>
                            <span class="room-type">{{ $room->type }}</span>
                            <span class="room-price">{{ number_format($room->price, 0, ',', '.') }}đ</span>
                        </div>

                        {{-- Day Cells --}}
                        @foreach($this->daysInMonth as $dayIndex => $day)
                            <div wire:click="createBooking({{ $room->id }}, '{{ $day->format('Y-m-d') }}')"
                                 class="day-cell {{ $day->isWeekend() ? 'weekend' : '' }} {{ $day->isToday() ? 'today' : '' }}"
                                 style="grid-column: {{ $dayIndex + 2 }}; grid-row: {{ $rowIndex }};">
                            </div>
                        @endforeach

                        {{-- Booking Bars --}}
                        @foreach($room->bookings as $booking)
                            @php
                                $checkIn = \Carbon\Carbon::parse($booking->check_in);
                                $checkOut = \Carbon\Carbon::parse($booking->check_out);
                                
                                $monthStart = \Carbon\Carbon::create($year, $month, 1);
                                $monthEnd = $monthStart->copy()->endOfMonth();

                                $displayStart = $checkIn->max($monthStart);
                                $displayEnd = $checkOut->min($monthEnd);
                                
                                $startCol = $displayStart->day + 1;
                                $duration = max(1, ceil($displayEnd->diffInDays($displayStart)));
                            @endphp

                            <div wire:click="editBooking({{ $booking->id }})"
                                 class="booking-bar booking-{{ $booking->status }}"
                                 style="grid-column: {{ $startCol }} / span {{ $duration }}; grid-row: {{ $rowIndex }};"
                                 title="{{ $booking->customer->name }}">
                                <span class="truncate">{{ $booking->customer->name }}</span>
                            </div>
                        @endforeach

                        @php $rowIndex++; @endphp
                    @endforeach
                @endforeach
            </div>
        </div>

        <p class="text-[10px] text-gray-400 text-center">Kéo ngang để xem thêm • Click ô trống để đặt phòng</p>
    </div>
</x-filament-panels::page>
