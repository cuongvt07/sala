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

        <span class="legend-badge bg-yellow-400 text-yellow-900 shadow-sm">Chờ lấy phòng</span>
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

            @foreach($roomsData as $areaName => $rooms)
                {{-- Area Header --}}
                <div class="area-header" style="grid-column: 1 / -1; grid-row: {{ $rowIndex }};">
                    {{ $areaName }}
                </div>
                @php $rowIndex++; @endphp

                @foreach($rooms as $room)
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
                        <span class="room-price">{{ number_format($room->price, 0, ',', '.') }}đ</span>
                    </div>

                    {{-- Day Cells Background --}}
                    @foreach($this->daysInMonth as $dayIndex => $day)
                        <div wire:click="createBooking({{ $room->id }}, '{{ $day->format('Y-m-d') }}')"
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

                            <div wire:click="editBooking({{ $booking->id }})"
                                 class="booking-bar {{ $bkClass }} group absolute pointer-events-auto shadow-sm hover:brightness-110 hover:shadow-md hover:scale-[1.01] transition-all duration-200 ease-in-out cursor-pointer z-10 hover:z-20"
                                 style="left: {{ $left }}px; width: {{ $width }}px; top: {{ $top }}px; height: 28px;"
                                 title="{{ $booking->customer->name }} - {{ \Carbon\Carbon::parse($booking->check_in)->format('d/m H:i') }} bis {{ \Carbon\Carbon::parse($booking->check_out)->format('d/m H:i') }}">
                                <div class="px-2 w-full flex flex-col leading-tight h-full justify-center text-white relative select-none">
                                    {{-- Name: Visible if space permits --}}
                                    <span class="font-bold uppercase text-[10px] truncate">{{ $booking->customer->name }}</span>
                                    
                                    {{-- Dates: Only if width > 60px --}}
                                    @if($width > 60)
                                        <span class="text-[8.5px] opacity-90 whitespace-nowrap truncate">
                                            {{ \Carbon\Carbon::parse($booking->check_in)->format('d/m') }} - {{ \Carbon\Carbon::parse($booking->check_out)->format('d/m') }}
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

    {{-- Simplified Booking Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" aria-hidden="true" wire:click="$set('showModal', false)"></div>
            
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full z-[110] relative">
                
                {{-- Header --}}
                <div class="bg-blue-600 px-5 py-3 flex justify-between items-center text-white">
                    <h3 class="text-lg font-bold">{{ $editingBookingId ? 'Chỉnh sửa đặt phòng' : 'Tạo đặt phòng mới' }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-white/90 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-5 py-4 space-y-4 max-h-[calc(100vh-180px)] overflow-y-auto bg-gray-50">
                    
                    {{-- Dates --}}
                    <div class="bg-white p-4 rounded border border-gray-200">
                        <h4 class="font-semibold text-gray-800 mb-3 text-sm">Thời gian</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Thời gian nhận</label>
                                <input type="datetime-local" wire:model.live="check_in" class="w-full px-3 py-2 text-sm rounded border-gray-300 border focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                @error('check_in') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Thời gian trả</label>
                                <input type="datetime-local" wire:model.live="check_out" class="w-full px-3 py-2 text-sm rounded border-gray-300 border focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                @error('check_out') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Customer --}}
                    <div class="bg-white p-4 rounded border border-gray-200">
                        <h4 class="font-semibold text-gray-800 mb-3 text-sm">Khách hàng</h4>
                        
                        <div class="flex gap-2 mb-3 p-1 bg-gray-100 rounded">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" wire:model.live="activeTab" value="existing" class="peer sr-only">
                                <div class="text-center py-1.5 px-3 rounded text-xs font-medium transition-all peer-checked:bg-blue-600 peer-checked:text-white text-gray-700">
                                    Khách cũ
                                </div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" wire:model.live="activeTab" value="new" class="peer sr-only">
                                <div class="text-center py-1.5 px-3 rounded text-xs font-medium transition-all peer-checked:bg-blue-600 peer-checked:text-white text-gray-700">
                                    Khách mới
                                </div>
                            </label>
                        </div>

                        @if($activeTab === 'existing')
                            <select wire:model.live="customer_id" class="w-full px-3 py-2 text-sm rounded border-gray-300 border focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                <option value="">-- Chọn khách hàng --</option>
                                @foreach($customers as $c)
                                    @php
                                        $expiry = $c->visa_expiry ? \Carbon\Carbon::parse($c->visa_expiry) : null;
                                        $isExpiring = $expiry && $expiry->diffInDays(now(), false) > -30;
                                    @endphp
                                    <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }}) {{ $isExpiring ? '⚠️' : '' }}</option>
                                @endforeach
                            </select>
                            @error('customer_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        @else
                            <div class="space-y-2">
                                <input type="text" wire:model.blur="new_customer_name" placeholder="Tên khách hàng *" class="w-full px-3 py-2 text-sm rounded border-gray-300 border focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                @error('new_customer_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                <input type="text" wire:model.blur="new_customer_phone" placeholder="Số điện thoại" class="w-full px-3 py-2 text-sm rounded border-gray-300 border focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                            </div>
                        @endif
                    </div>

                    {{-- Booking Details --}}
                    <div class="bg-white p-4 rounded border border-gray-200">
                        <h4 class="font-semibold text-gray-800 mb-3 text-sm">Chi tiết đặt phòng</h4>
                        
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Phòng</label>
                                <select wire:model.live="room_id" class="w-full px-3 py-2 text-sm rounded border-gray-300 border focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    @foreach($all_rooms as $r)
                                        <option value="{{ $r->id }}">{{ $r->code }} ({{ $r->area->name ?? '' }})</option>
                                    @endforeach
                                </select>
                                @error('room_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Loại giá</label>
                                <select wire:model.live="price_type" class="w-full px-3 py-2 text-sm rounded border-gray-300 border focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                    <option value="day">Theo ngày</option>
                                    <option value="month">Theo tháng</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Đơn giá</label>
                            <input type="text" wire:model.blur="unit_price" class="w-full px-3 py-2 text-sm rounded border-gray-300 border font-semibold focus:border-blue-500 focus:ring-1 focus:ring-blue-500" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                            @error('unit_price') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Trạng thái</label>
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 border-yellow-400 text-yellow-900',
                                        'checked_in' => 'bg-green-100 border-green-400 text-green-900',
                                        'checked_out' => 'bg-gray-100 border-gray-400 text-gray-900',
                                        'cancelled' => 'bg-red-100 border-red-400 text-red-900',
                                    ];
                                @endphp
                                <select wire:model.live="status" class="w-full px-3 py-2 text-sm rounded border-2 {{ $statusColors[$status] ?? 'border-gray-300' }} font-semibold focus:ring-1 focus:ring-blue-500">
                                    <option value="pending">Chờ lấy phòng</option>
                                    <option value="checked_in">Đã nhận phòng</option>
                                    <option value="checked_out">Đã trả phòng</option>
                                    <option value="cancelled">Đã hủy</option>
                                </select>
                            </div>

                            <div class="col-span-3">
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Ghi chú</label>
                                <input type="text" wire:model.blur="notes" class="w-full px-3 py-2 text-sm rounded border-gray-300 border focus:border-blue-500 focus:ring-1 focus:ring-blue-500" placeholder="Ghi chú...">
                            </div>
                        </div>
                    </div>

                    {{-- Check-in Info (Conditional) --}}
                    <div class="bg-green-50 p-4 rounded border-2 border-green-300" x-show="$wire.status === 'checked_in'" x-transition>
                        <h4 class="font-semibold text-green-800 mb-3 text-sm">✓ Thông tin nhận phòng (Bắt buộc)</h4>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">CMT/CCCD/Passport/Visa *</label>
                                <input type="text" wire:model.blur="customer_identity" class="w-full px-3 py-2 text-sm rounded border-gray-300 border bg-white focus:border-green-500 focus:ring-1 focus:ring-green-500">
                                @error('customer_identity') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Quốc tịch *</label>
                                <x-ui.select-search 
                                    wire:model="customer_nationality" 
                                    :options="$countries"
                                    :error="$errors->first('customer_nationality')"
                                    placeholder="Chọn quốc tịch"
                                    class="text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Hạn Visa</label>
                                <input type="date" wire:model.blur="customer_visa_expiry" class="w-full px-3 py-2 text-sm rounded border-gray-300 border bg-white focus:border-green-500 focus:ring-1 focus:ring-green-500">
                            </div>
                        </div>
                    </div>

                    {{-- Deposits --}}
                    <div class="bg-white p-4 rounded border border-gray-200">
                        <h4 class="font-semibold text-gray-800 mb-3 text-sm">Tiền cọc</h4>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Cọc Lần 1</label>
                                <input type="text" wire:model.blur="deposit" class="w-full px-3 py-2 text-sm rounded border-gray-300 border font-semibold text-indigo-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Cọc Lần 2</label>
                                <input type="text" wire:model.blur="deposit_2" class="w-full px-3 py-2 text-sm rounded border-gray-300 border font-semibold text-indigo-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Cọc Lần 3</label>
                                <input type="text" wire:model.blur="deposit_3" class="w-full px-3 py-2 text-sm rounded border-gray-300 border font-semibold text-indigo-600 focus:border-blue-500 focus:ring-1 focus:ring-blue-500" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="bg-gray-100 px-5 py-3 flex justify-end gap-2 border-t border-gray-300">
                    <button wire:click="$set('showModal', false)" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 border border-gray-300 rounded shadow-sm">
                        Hủy
                    </button>
                    <button wire:click="save" class="px-5 py-2 bg-blue-600 text-white rounded font-semibold hover:bg-blue-700 shadow-sm text-sm">
                        Lưu đặt phòng
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
