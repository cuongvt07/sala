<div class="space-y-4">
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

    <!-- Header & Controls -->
    <div class="flex flex-wrap justify-between items-center bg-white p-4 rounded-xl shadow-sm border border-gray-100 gap-4">
                <h2 class="text-2xl font-bold text-gray-800">
                {{ \Carbon\Carbon::create($year, $month, 1)->locale('vi')->isoFormat('MMMM YYYY') }}
            </h2>

        <div class="flex gap-2">
            <button wire:click="prevMonth" class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                ‹ Tháng trước
            </button>
            <button wire:click="goToToday" class="px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                Hôm nay
            </button>
            <button wire:click="nextMonth" class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                Tháng sau ›
            </button>
        </div>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap gap-2">
        <span class="legend-badge bg-blue-500 text-white shadow-sm">Đã xác nhận</span>
        <span class="legend-badge bg-yellow-400 text-yellow-900 shadow-sm">Đang chờ</span>
        <span class="legend-badge bg-green-500 text-white shadow-sm">Đã nhận phòng</span>
        <span class="legend-badge bg-gray-500 text-white shadow-sm">Đã trả phòng</span>
        <span class="legend-badge bg-red-500 text-white shadow-sm">Đã hủy</span>
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

            {{-- Rooms & Bookings --}}
            @foreach($roomsData as $areaName => $rooms)
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
                            // Robustly parse CheckIn/CheckOut
                            $checkIn = is_a($booking->check_in, 'Carbon\Carbon') ? $booking->check_in : \Carbon\Carbon::parse($booking->check_in);
                            
                            $checkOut = null;
                            if ($booking->check_out) {
                                $checkOut = is_a($booking->check_out, 'Carbon\Carbon') ? $booking->check_out : \Carbon\Carbon::parse($booking->check_out);
                            } else {
                                // Long term: End of view range
                                $checkOut = $checkIn->copy()->addYears(1);
                            }
                            
                            $currentMonth = $this->month;
                            $currentYear = $this->year;
                            
                            $monthStart = \Carbon\Carbon::create($currentYear, $currentMonth, 1)->startOfDay();
                            $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();

                            // Clamp dates to the current month view
                            $displayStart = $checkIn->copy()->max($monthStart);
                            $displayEnd = $checkOut->copy()->min($monthEnd);
                            
                            // Column 1 is Room info, so Day 1 is Column 2
                            $startCol = (int)$displayStart->format('j') + 1;
                            
                            // Duration in days (inclusive)
                            $duration = (int)$displayStart->startOfDay()->diffInDays($displayEnd->startOfDay()) + 1;
                        @endphp

                        <div wire:click="editBooking({{ $booking->id }})"
                             class="booking-bar booking-{{ $booking->status }} group relative"
                             style="grid-column: {{ $startCol }} / span {{ $duration }}; grid-row: {{ $rowIndex }};"
                             title="{{ $booking->customer->name }} ({{ $duration }} ngày)">
                            <div class="sticky left-0 px-2 truncate w-full group-hover:overflow-visible group-hover:whitespace-normal group-hover:bg-inherit group-hover:z-50 py-0.5 flex flex-col leading-tight">
                                <span class="font-bold uppercase text-[10px]">{{ $booking->customer->name }}</span>
                                <span class="text-[8.5px] opacity-90 whitespace-nowrap">
                                    {{ $checkIn->format('h:i A d/m') }} - {{ $checkOut ? $checkOut->format('h:i A d/m') : '...' }}
                                </span>
                            </div>
                        </div>
                    @endforeach

                    @php $rowIndex++; @endphp
                @endforeach
            @endforeach
        </div>
    </div>

    {{-- Simplified Booking Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- Backdrop with Blur --}}
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="$set('showModal', false)"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            {{-- Modal Content --}}
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100 z-[110] relative">
                
                {{-- Header --}}
                <div class="bg-blue-600 px-6 py-4 flex justify-between items-center text-white">
                    <h3 class="text-lg font-bold">{{ $editingBookingId ? 'Chỉnh sửa đặt phòng' : 'Tạo đặt phòng mới' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-6 space-y-6">
                    {{-- Basic Info --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-normal text-gray-700 mb-1">Thời gian nhận</label>
                            <input type="datetime-local" wire:model.live="check_in" class="w-full rounded-lg border-gray-300">
                            @error('check_in') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-normal text-gray-700 mb-1">Thời gian trả</label>
                            <input type="datetime-local" wire:model.live="check_out" class="w-full rounded-lg border-gray-300">
                            @error('check_out') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Customer Selection --}}
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                        <div class="flex gap-4 mb-4">
                            <label class="inline-flex items-center">
                                <input type="radio" wire:model.live="activeTab" value="existing" class="text-blue-600">
                                <span class="ml-2 text-sm font-normal">Khách cũ</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" wire:model.live="activeTab" value="new" class="text-blue-600">
                                <span class="ml-2 text-sm font-normal">Khách mới</span>
                            </label>
                        </div>

                        @if($activeTab === 'existing')
                            <div>
                                <select wire:model.blur="customer_id" class="w-full rounded-lg border-gray-300">
                                    <option value="">-- Chọn khách hàng --</option>
                                    @foreach($customers as $c)
                                        @php
                                            $expiry = $c->visa_expiry ? \Carbon\Carbon::parse($c->visa_expiry) : null;
                                            $isExpiring = $expiry && $expiry->diffInDays(now(), false) > -30;
                                        @endphp
                                        <option value="{{ $c->id }}" class="{{ $isExpiring ? 'text-red-500 font-bold' : '' }}">
                                            {{ $c->name }} ({{ $c->phone }}) {{ $isExpiring ? '⚠️ Visa sắp hết hạn' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div class="space-y-3">
                                <input type="text" wire:model.blur="new_customer_name" placeholder="Tên khách hàng *" class="w-full rounded-lg border-gray-300">
                                @error('new_customer_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                <input type="text" wire:model.blur="new_customer_phone" placeholder="Số điện thoại" class="w-full rounded-lg border-gray-300">
                            </div>
                        @endif
                    </div>

                    {{-- Room Info --}}
                    <div>
                        <label class="block text-sm font-normal text-gray-700 mb-1">Phòng</label>
                        <select wire:model.live="room_id" class="w-full rounded-lg border-gray-300">
                            @foreach($all_rooms as $r)
                                <option value="{{ $r->id }}">{{ $r->code }} ({{ $r->area->name ?? '' }})</option>
                            @endforeach
                        </select>
                        @error('room_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Price & Notes --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-normal text-gray-700 mb-1">Tiền phòng</label>
                            <input type="text" wire:model.blur="price" class="w-full rounded-lg border-gray-300 font-bold text-blue-600">
                        </div>
                        <div>
                            <label class="block text-sm font-normal text-gray-700 mb-1">Trạng thái</label>
                            <select wire:model.blur="status" class="w-full rounded-lg border-gray-300">
                                <option value="pending">Đang chờ</option>
                                <option value="confirmed">Đã xác nhận</option>
                                <option value="checked_in">Đã nhận phòng</option>
                                <option value="checked_out">Đã trả phòng</option>
                                <option value="cancelled">Đã hủy</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 rounded-lg">Hủy</button>
                    <button wire:click="save" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 shadow-md">Lưu đặt phòng</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
