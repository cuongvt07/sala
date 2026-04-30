<div class="space-y-4 relative">
    {{-- Hiệu ứng Loading toàn trang khi đổi khu hoặc thao tác --}}
    <div wire:loading wire:target="area-selected, refreshView, startDate, selectedArea, nextMonth, prevMonth, gotoToday" class="absolute inset-0 z-50 flex items-center justify-center bg-white/40 backdrop-blur-[2px] transition-all duration-300">
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
                                    <span class="font-bold uppercase text-[10px] truncate flex items-center gap-1">
                                        {{ $booking->customer->name }}
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
    @if($showModal)
    <div class="fixed inset-0 z-[100] overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="$set('showModal', false)"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full z-[110] relative border border-gray-100">
                {{-- Header --}}
                <div class="bg-gray-800 px-6 py-4 flex justify-between items-center text-white">
                    <div>
                        <h3 class="text-lg font-bold">{{ $editingBookingId ? 'Chi tiết đặt phòng' : 'Tạo mới' }}</h3>
                        @if($editingBookingId)
                            <p class="text-xs text-gray-400 mt-0.5">Mã: #{{ $editingBookingId }}</p>
                        @endif
                    </div>
                    <button wire:click="$set('showModal', false)" class="text-white hover:text-gray-300 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                {{-- Tabs --}}
                <div class="flex border-b border-gray-200 bg-white px-6">
                    @php
                        $tabs = [
                            'overview' => 'Tổng quan',
                            'services' => 'Dịch vụ',
                            'payments' => 'Thanh toán',
                            'invoice' => 'Hoá đơn'
                        ];
                    @endphp
                    @foreach($tabs as $key => $label)
                        <button wire:click="setTab('{{ $key }}')" 
                                class="px-5 py-3 text-sm font-medium transition-all relative {{ $activeModalTab === $key ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Body --}}
                <div class="px-6 py-6 max-h-[calc(100vh-250px)] overflow-y-auto bg-white">
                    
                    @if($activeModalTab === 'overview')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- LEFT COLUMN: Basic Info --}}
                            <div class="space-y-4">
                                <h4 class="text-sm font-bold text-gray-900 pb-2 border-b">Thông tin đặt phòng</h4>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Phòng</label>
                                        <select wire:model.live="room_id" class="w-full rounded border-gray-300 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                            @foreach($all_rooms as $r)
                                                <option value="{{ $r->id }}">{{ $r->code }} ({{ $r->area->name ?? '' }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Loại giá</label>
                                        <select wire:model.live="price_type" class="w-full rounded border-gray-300 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="day">Theo ngày</option>
                                            <option value="month">Thuê hợp đồng</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Đơn giá</label>
                                        <input type="text" wire:model.blur="unit_price" class="w-full rounded border-gray-300 py-2 text-sm font-bold focus:ring-blue-500 focus:border-blue-500" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4 pt-2">
                                    <x-ui.select-date wire:model="check_in" label="Ngày nhận" />
                                    <x-ui.select-date wire:model="check_out" label="Ngày trả" />
                                </div>
                            </div>

                            {{-- RIGHT COLUMN: Customer --}}
                            <div class="space-y-4">
                                <h4 class="text-sm font-bold text-gray-900 pb-2 border-b">Khách hàng</h4>
                                
                                <div class="flex border rounded overflow-hidden mb-4">
                                    <button wire:click="$set('activeTab', 'existing')" class="flex-1 py-1.5 text-xs font-medium {{ $activeTab === 'existing' ? 'bg-gray-100 text-blue-600' : 'bg-white text-gray-500' }}">Khách cũ</button>
                                    <button wire:click="$set('activeTab', 'new')" class="flex-1 py-1.5 text-xs font-medium {{ $activeTab === 'new' ? 'bg-gray-100 text-blue-600' : 'bg-white text-gray-500' }}">Khách mới</button>
                                </div>

                                @if($activeTab === 'existing')
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Chọn khách hàng</label>
                                        <select wire:model.live="customer_id" class="w-full rounded border-gray-300 py-2 text-sm focus:ring-blue-500 focus:border-blue-500 font-bold">
                                            <option value="">-- Chọn --</option>
                                            @foreach($customers as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @if($customer_id)
                                        <div class="grid grid-cols-2 gap-3 bg-blue-50/50 p-3 rounded-lg border border-blue-100">
                                            <div class="col-span-2">
                                                <label class="block text-[10px] font-bold text-blue-600 uppercase mb-1">Họ tên</label>
                                                <input type="text" wire:model.blur="customer_name" class="w-full rounded border-blue-200 py-1.5 text-xs">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-blue-600 uppercase mb-1">SĐT</label>
                                                <input type="text" wire:model.blur="customer_phone" class="w-full rounded border-blue-200 py-1.5 text-xs">
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-blue-600 uppercase mb-1">Giới tính</label>
                                                <select wire:model.blur="customer_gender" class="w-full rounded border-blue-200 py-1.5 text-xs">
                                                    <option value="">Chọn</option>
                                                    <option value="male">Nam</option>
                                                    <option value="female">Nữ</option>
                                                    <option value="other">Khác</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-blue-600 uppercase mb-1">CCCD/Passport *</label>
                                                <input type="text" wire:model.blur="customer_identity" class="w-full rounded border-blue-200 py-1.5 text-xs">
                                                @error('customer_identity') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <label class="block text-[10px] font-bold text-blue-600 uppercase mb-1">Quốc tịch *</label>
                                                <x-ui.select-search 
                                                    wire:model="customer_nationality" 
                                                    :options="$this->getFormattedCountries()"
                                                    placeholder="Quốc tịch"
                                                    class="text-xs"
                                                />
                                                @error('customer_nationality') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <x-ui.select-date wire:model="customer_birthday" label="Ngày sinh" />
                                            <x-ui.select-date wire:model="customer_visa_expiry" label="Hạn Visa" />
                                        </div>
                                    @endif
                                @else
                                    <div class="space-y-3">
                                        <input type="text" wire:model.blur="new_customer_name" placeholder="Họ và tên *" class="w-full rounded border-gray-300 py-2 text-sm">
                                        <div class="grid grid-cols-2 gap-3">
                                            <input type="text" wire:model.blur="new_customer_phone" placeholder="Số điện thoại" class="w-full rounded border-gray-300 py-2 text-sm">
                                                <select wire:model.blur="new_customer_gender" class="w-full rounded border-gray-300 py-2 text-sm">
                                                    <option value="">Giới tính</option>
                                                    <option value="male">Nam</option>
                                                    <option value="female">Nữ</option>
                                                    <option value="other">Khác</option>
                                                </select>
                                            </div>
                                            <div class="grid grid-cols-1 gap-3">
                                                <x-ui.select-date wire:model="new_customer_birthday" label="Ngày sinh" />
                                            </div>
                                            <div class="grid grid-cols-2 gap-3">
                                            <div>
                                                <input type="text" wire:model.blur="new_customer_identity" placeholder="CCCD/Passport" class="w-full rounded border-gray-300 py-2 text-sm">
                                                @error('new_customer_identity') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                            <div>
                                                <x-ui.select-search 
                                                    wire:model="new_customer_nationality" 
                                                    :options="$this->getFormattedCountries()"
                                                    placeholder="Quốc tịch"
                                                    class="text-sm"
                                                />
                                                @error('new_customer_nationality') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Ảnh hộ chiếu/CCCD (nếu có)</label>
                                            <input type="file" wire:model="new_customer_image" class="text-xs">
                                        </div>
                                    </div>
                                @endif

                                {{-- Additional Guests --}}
                                <div class="mt-4 p-3 bg-indigo-50/50 rounded border border-indigo-100">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-[10px] font-bold text-indigo-700 uppercase tracking-wider">Người ở cùng</h4>
                                        <button type="button" wire:click="addGuest" class="text-[10px] bg-indigo-600 text-white px-2 py-0.5 rounded font-bold hover:bg-indigo-700">+ Thêm</button>
                                    </div>
                                    <div class="space-y-2">
                                        @foreach($additional_guests as $index => $guest)
                                            <div class="flex items-center gap-2">
                                                <input type="text" wire:model="additional_guests.{{ $index }}.name" placeholder="Tên" class="flex-1 px-2 py-1 text-xs rounded border-gray-300 border">
                                                <input type="text" wire:model="additional_guests.{{ $index }}.identity" placeholder="CCCD/Passport" class="w-32 px-2 py-1 text-xs rounded border-gray-300 border">
                                                <button type="button" wire:click="removeGuest({{ $index }})" class="text-red-500 hover:text-red-700">
                                                    <x-icon name="heroicon-o-trash" class="h-4 w-4" />
                                                </button>
                                            </div>
                                        @endforeach
                                        @if(empty($additional_guests))
                                            <p class="text-[10px] text-gray-400 italic">Không có người ở cùng</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4 pt-2">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Trạng thái</label>
                                        <select wire:model.live="status" class="w-full rounded border-gray-300 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="pending">Chờ lấy phòng</option>
                                            <option value="checked_in">Đã nhận phòng</option>
                                            <option value="checked_out">Trả phòng</option>
                                            <option value="cancelled">Đã hủy</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Hình thức đặt</label>
                                        <select wire:model.live="source" class="w-full rounded border-gray-300 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">-- Chọn --</option>
                                            <option value="Airbnb">Airbnb</option>
                                            <option value="OTA">OTA</option>
                                            <option value="Facebook">Facebook</option>
                                            <option value="Hotline">Hotline</option>
                                            <option value="Khác">Khác</option>
                                        </select>
                                        @error('source') <span class="text-[10px] text-red-500">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-span-2">
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Ghi chú</label>
                                        <textarea wire:model.blur="notes" rows="2" class="w-full rounded border-gray-300 py-2 text-sm" placeholder="Ghi chú..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($activeModalTab === 'services')
                        <div class="space-y-6" wire:key="tab-services">
                            {{-- Chốt dịch vụ nhanh --}}
                            <div class="bg-gray-50 rounded border p-5">
                                <h5 class="text-xs font-bold text-gray-700 uppercase mb-4">Chốt chỉ số dịch vụ</h5>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($all_services as $service)
                                        @if($service->type === 'meter')
                                            <div class="bg-white border rounded p-3">
                                                <div class="text-xs font-bold mb-2">{{ $service->name }}</div>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <input type="text" wire:model.blur="service_inputs.{{ $service->id }}.start_index" placeholder="Số đầu" class="border rounded p-1.5 text-xs text-center">
                                                    <input type="text" wire:model.blur="service_inputs.{{ $service->id }}.end_index" placeholder="Số cuối" class="border rounded p-1.5 text-xs text-center font-bold">
                                                </div>
                                                <button wire:click="addServiceLog({{ $service->id }})" class="w-full mt-2 py-1 bg-blue-600 text-white rounded text-[10px] font-bold uppercase">Lưu số</button>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                
                                <div class="mt-4 flex justify-end">
                                    <button wire:click="addAllServiceLogs" class="text-blue-600 text-xs font-bold uppercase underline">
                                        Lưu tất cả đã nhập
                                    </button>
                                </div>
                            </div>

                            {{-- Bảng Tổng chi phí (Tương tự bên Index) --}}
                            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                                <div class="bg-slate-100 px-3 py-2 border-b border-gray-200">
                                    <h4 class="text-[10px] font-black text-slate-600 uppercase">Bảng Tổng Chi Phí (Tạm tính)</h4>
                                </div>
                                <table class="w-full text-xs">
                                    <thead class="bg-gray-50 text-gray-500">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-bold uppercase text-[10px]">Hạng mục</th>
                                            <th class="px-2 py-2 text-center font-bold uppercase text-[10px] w-20">Đơn giá</th>
                                            <th class="px-2 py-2 text-center font-bold uppercase text-[10px] w-28">Số liệu</th>
                                            <th class="px-3 py-2 text-right font-bold uppercase text-[10px] w-24">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        <!-- Tiền phòng -->
                                        @php
                                            $start = $check_in ? \Carbon\Carbon::parse($check_in) : null;
                                            $end = $check_out ? \Carbon\Carbon::parse($check_out) : null;
                                            $nightsCount = ($start && $end) ? $start->diffInDays($end) : 0;
                                            if($price_type === 'day') $nightsCount = max(1, $nightsCount);
                                            $uPrice = (float)str_replace(['.',','],'',$unit_price ?: 0);
                                        @endphp
                                        <tr class="bg-blue-50/50">
                                            <td class="px-3 py-2">
                                                <div class="font-bold text-gray-900">💰 {{ $price_type === 'month' ? 'Tiền phòng (Hợp đồng)' : 'Tiền phòng (Ngày)' }}</div>
                                                <div class="text-[10px] text-gray-500 font-medium italic">({{ $nightsCount }} đêm x {{ number_format($uPrice, 0, ',', '.') }}đ{{ $price_type === 'month' ? ' / 30' : '' }})</div>
                                            </td>
                                            <td class="px-2 py-2 text-center text-gray-400">-</td>
                                            <td class="px-2 py-2 text-center text-gray-400">-</td>
                                            <td class="px-3 py-2 text-right font-black text-blue-600">{{ number_format($basePrice, 0, ',', '.') }}đ</td>
                                        </tr>

                                        <!-- Tiền dịch vụ đã chốt -->
                                        @if($logTotal > 0)
                                        <tr class="bg-green-50/50 border-y border-green-100">
                                            <td class="px-3 py-2">
                                                <div class="font-bold text-green-700">✅ Dịch vụ đã chốt (Lịch sử)</div>
                                            </td>
                                            <td class="px-2 py-2 text-center text-gray-400">-</td>
                                            <td class="px-2 py-2 text-center text-gray-400">-</td>
                                            <td class="px-3 py-2 text-right font-black text-green-600">{{ number_format($logTotal, 0, ',', '.') }}đ</td>
                                        </tr>
                                        @endif
                                        
                                        <!-- Các dịch vụ đang chọn (chưa chốt) -->
                                        @foreach($all_services as $service)
                                            @if(!empty($selected_services[$service->id]['selected']) && isset($service_inputs[$service->id]))
                                                @php 
                                                    $inp = $service_inputs[$service->id] ?? [];
                                                    $up = (float)str_replace(['.',','],'', (string)($inp['unit_price'] ?? '0'));
                                                    
                                                    if($service->type === 'meter') {
                                                        $startIdx = (float)($inp['start_index'] ?? 0);
                                                        $endIdx = (float)($inp['end_index'] ?? 0);
                                                        $amount = max(0, $endIdx - $startIdx) * $up;
                                                    } else {
                                                        $amount = ((float)($inp['quantity'] ?? 1)) * $up;
                                                    }
                                                @endphp
                                                @if($amount > 0)
                                                <tr>
                                                    <td class="px-3 py-2">
                                                        <div class="font-semibold text-gray-800">⚡ {{ $service->name }} (Đang nhập)</div>
                                                    </td>
                                                    <td class="px-2 py-2 text-center text-gray-500">{{ number_format($up, 0, ',', '.') }}</td>
                                                    <td class="px-2 py-2 text-center text-gray-500">
                                                        @if($service->type === 'meter')
                                                            {{ $inp['start_index'] }}→{{ $inp['end_index'] }}
                                                        @else
                                                            x{{ $inp['quantity'] }}
                                                        @endif
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-bold text-indigo-600">{{ number_format($amount, 0, ',', '.') }}đ</td>
                                                </tr>
                                                @endif
                                            @endif
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-slate-800 text-white">
                                        <tr>
                                            <td colspan="3" class="px-3 py-2 text-right font-bold uppercase text-[11px]">TỔNG CỘNG:</td>
                                            <td class="px-3 py-2 text-right font-black text-lg">{{ number_format($grandTotal, 0, ',', '.') }}đ</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- Lịch sử sử dụng --}}
                            <div class="space-y-3">
                                <h4 class="text-xs font-bold text-gray-500 uppercase">Nhật ký sử dụng chi tiết</h4>
                                <div class="border rounded overflow-hidden">
                                    <table class="w-full text-left text-xs">
                                        <thead class="bg-gray-100 border-b">
                                            <tr>
                                                <th class="px-4 py-2 font-bold">Dịch vụ</th>
                                                <th class="px-4 py-2 font-bold">Chỉ số</th>
                                                <th class="px-4 py-2 font-bold">Số lượng</th>
                                                <th class="px-4 py-2 font-bold text-right">Thành tiền</th>
                                                <th class="px-4 py-2"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y">
                                            @forelse($usage_logs as $index => $log)
                                                <tr>
                                                    <td class="px-4 py-2 font-medium">{{ $log['service_name'] }}</td>
                                                    <td class="px-4 py-2 text-gray-500">
                                                        @if($log['type'] === 'meter')
                                                            {{ $log['start_index'] }} - {{ $log['end_index'] }}
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-2">{{ $log['quantity'] }} {{ $log['billing_unit'] }}</td>
                                                    <td class="px-4 py-2 text-right font-bold">{{ number_format($log['total_amount'], 0, ',', '.') }}đ</td>
                                                    <td class="px-4 py-2 text-right">
                                                        <button wire:click="removeUsageLog({{ $index }})" class="text-red-500">Xóa</button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-4 py-6 text-center text-gray-400">Chưa có dữ liệu</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($activeModalTab === 'payments')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8" wire:key="tab-payments">
                            <div class="space-y-4">
                                <h4 class="text-sm font-bold text-gray-900 pb-2 border-b">Tiền cọc</h4>
                                <div class="space-y-3">
                                    @foreach(['deposit' => 'Cọc lần 1', 'deposit_2' => 'Cọc lần 2', 'deposit_3' => 'Cọc lần 3'] as $field => $label)
                                        <div class="flex items-center justify-between p-3 border rounded">
                                            <span class="text-xs font-medium text-gray-500">{{ $label }}</span>
                                            <input type="text" wire:model.blur="{{ $field }}" class="text-right font-bold text-blue-600 border-none p-0 focus:ring-0 w-32" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="space-y-4">
                                <h4 class="text-sm font-bold text-gray-900 pb-2 border-b">Phụ thu & Giảm trừ</h4>
                                <div class="bg-gray-50 p-4 rounded border space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Số tiền</label>
                                        <input type="text" wire:model="manual_fee_amount" class="w-full rounded border-gray-300 p-2 text-sm font-bold text-blue-600" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" placeholder="0">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 mb-1">Lý do</label>
                                        <input type="text" wire:model="manual_fee_notes" class="w-full rounded border-gray-300 p-2 text-sm" placeholder="Nội dung...">
                                    </div>
                                    <button wire:click="addManualSurcharge" class="w-full py-2 bg-gray-800 text-white rounded text-xs font-bold uppercase">Ghi nhận phí</button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($activeModalTab === 'invoice')
                        <div class="space-y-6" wire:key="tab-invoice">
                            <div class="flex justify-between items-center border p-4 rounded bg-gray-50">
                                <div>
                                    <h4 class="text-sm font-bold">Xem hóa đơn tháng</h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Chọn tháng để xem trước và chốt số liệu</p>
                                </div>
                                <div class="flex gap-2">
                                    @php
                                        $periods = collect($usage_logs)->map(fn($l) => \Carbon\Carbon::parse($l['billing_date'])->format('m/Y'))->unique();
                                    @endphp
                                    @forelse($periods as $period)
                                        <button wire:click="viewPeriodInvoice('{{ $period }}')" class="px-3 py-1.5 bg-white border rounded text-xs font-bold text-blue-600 hover:border-blue-500">{{ $period }}</button>
                                    @empty
                                        <span class="text-xs text-gray-400">Chưa có dữ liệu</span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="border rounded p-8 flex flex-col items-center text-center">
                                <h4 class="text-lg font-bold mb-2">Gửi hóa đơn cho khách</h4>
                                <p class="text-sm text-gray-500 mb-6">Hệ thống sẽ gửi email hóa đơn tổng hợp phí phòng và dịch vụ cho khách.</p>
                                <button wire:click="exportInvoice" class="px-8 py-3 bg-blue-600 text-white rounded font-bold text-xs uppercase tracking-widest shadow-md">Gửi email hóa đơn</button>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Footer --}}
                <div class="bg-gray-50 px-6 py-4 flex justify-between items-center border-t">
                    <div class="flex flex-col">
                        <span class="text-[10px] text-gray-500 font-bold uppercase">Tổng cộng:</span>
                        <span class="text-lg font-bold text-gray-900">{{ $price }}đ</span>
                    </div>
                    <div class="flex gap-4">
                        @if($editingBookingId)
                            <button wire:click="deleteBooking(editingBookingId)" 
                                    wire:confirm="Bạn có chắc chắn muốn xóa đặt phòng này?"
                                    class="text-sm font-bold text-red-500 hover:text-red-700 mr-auto">
                                Xóa
                            </button>
                        @endif
                        <button wire:click="$set('showModal', false)" class="text-sm font-bold text-gray-400 hover:text-gray-600">Đóng</button>
                        @if($editingBookingId)
                            <button wire:click="viewConfirmation" class="px-4 py-2 bg-indigo-600 text-white rounded font-bold text-xs uppercase shadow-md hover:bg-indigo-700 flex items-center gap-1">
                                <x-icon name="heroicon-o-printer" class="h-4 w-4" /> In xác nhận
                            </button>
                        @endif
                        <button wire:click="saveBooking" wire:loading.attr="disabled" wire:target="saveBooking" class="px-8 py-2 bg-blue-600 text-white rounded font-bold text-xs uppercase shadow-md hover:bg-blue-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="saveBooking">Lưu lại</span>
                            <span wire:loading wire:target="saveBooking">Đang lưu...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- INVOICE PREVIEW MODAL --}}
    @if($showInvoiceModal && !empty($invoice_data))
        <div class="fixed inset-0 z-[120] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeInvoiceModal"></div>
                
                <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto p-10" style="font-family: 'Times New Roman', Times, serif;">
                    <button wire:click="closeInvoiceModal" class="absolute top-4 right-4 z-10 bg-gray-100 hover:bg-gray-200 rounded-full p-2">
                        <x-icon name="heroicon-o-x-mark" class="h-5 w-5" />
                    </button>

                    @php
                        $electricLog = collect($invoice_data['logs'])->first(fn($l) => str_contains(mb_strtolower($l['service_name'] ?? ''), 'điện'));
                        $waterLog = collect($invoice_data['logs'])->first(fn($l) => str_contains(mb_strtolower($l['service_name'] ?? ''), 'nước'));
                        $otherLogs = collect($invoice_data['logs'])->reject(fn($l) => str_contains(mb_strtolower($l['service_name'] ?? ''), 'điện') || str_contains(mb_strtolower($l['service_name'] ?? ''), 'nước'));
                    @endphp

                    {{-- Bill Content --}}
                    <div class="border-b-2 border-gray-800 pb-6 mb-6 flex justify-between items-start">
                        <div class="flex items-center gap-4">
                            <img src="{{ asset('logo.jpg') }}" class="w-20 h-20 border-2 border-gray-800 rounded-full object-cover">
                            <div>
                                <h2 class="text-2xl font-bold uppercase">Sala Appartment</h2>
                                <p class="text-sm italic">688/57/16/22 Le Duc Tho Street, Ward 15, Go Vap District, HCM City</p>
                                <p class="text-sm font-bold">Hotline: 0941.567.568</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <h1 class="text-3xl font-black uppercase mb-1">Hoá đơn</h1>
                            <p class="text-sm font-bold">Tháng {{ $invoice_data['period'] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8 mb-8">
                        <div class="space-y-1">
                            <p class="text-sm"><span class="font-bold uppercase w-32 inline-block">Khách hàng:</span> {{ $invoice_data['booking']['customer_name'] }}</p>
                            <p class="text-sm"><span class="font-bold uppercase w-32 inline-block">Phòng:</span> {{ $invoice_data['booking']['room_code'] }}</p>
                            <p class="text-sm"><span class="font-bold uppercase w-32 inline-block">Ngày vào:</span> {{ $invoice_data['booking']['check_in'] }}</p>
                        </div>
                        <div class="flex justify-end">
                            <img src="{{ asset('qr.jpg') }}" class="w-32 h-32 border border-gray-200 p-2">
                        </div>
                    </div>

                    <table class="w-full border-collapse border border-gray-800 mb-8">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-800 p-2 text-xs font-bold text-center">Dịch vụ / Services</th>
                                <th class="border border-gray-800 p-2 text-xs font-bold text-center">Đơn vị / Unit</th>
                                <th class="border border-gray-800 p-2 text-xs font-bold text-center">Số lượng / Qty</th>
                                <th class="border border-gray-800 p-2 text-xs font-bold text-center">Thành tiền / Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-gray-800 p-2 text-sm font-bold">Tiền phòng / Room Rent</td>
                                <td class="border border-gray-800 p-2 text-sm text-center">Đêm</td>
                                <td class="border border-gray-800 p-2 text-sm text-center">1</td>
                                <td class="border border-gray-800 p-2 text-sm text-right font-bold">{{ number_format($invoice_data['room_price'], 0, ',', '.') }}</td>
                            </tr>
                            @foreach($otherLogs as $log)
                                <tr>
                                    <td class="border border-gray-800 p-2 text-sm font-bold">{{ $log['service_name'] }}</td>
                                    <td class="border border-gray-800 p-2 text-sm text-center">{{ $log['billing_unit'] }}</td>
                                    <td class="border border-gray-800 p-2 text-sm text-center">{{ $log['quantity'] }}</td>
                                    <td class="border border-gray-800 p-2 text-sm text-right font-bold">{{ number_format($log['total_amount'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            @if($electricLog)
                                <tr>
                                    <td class="border border-gray-800 p-2 text-sm font-bold">Tiền điện / Electricity</td>
                                    <td class="border border-gray-800 p-2 text-sm text-center">{{ $electricLog['billing_unit'] }}</td>
                                    <td class="border border-gray-800 p-2 text-sm text-center">{{ $electricLog['end_index'] - $electricLog['start_index'] }}</td>
                                    <td class="border border-gray-800 p-2 text-sm text-right font-bold">{{ number_format($electricLog['total_amount'], 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            @if($waterLog)
                                <tr>
                                    <td class="border border-gray-800 p-2 text-sm font-bold">Tiền nước / Water</td>
                                    <td class="border border-gray-800 p-2 text-sm text-center">{{ $waterLog['billing_unit'] }}</td>
                                    <td class="border border-gray-800 p-2 text-sm text-center">{{ $waterLog['end_index'] - $waterLog['start_index'] }}</td>
                                    <td class="border border-gray-800 p-2 text-sm text-right font-bold">{{ number_format($waterLog['total_amount'], 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            <tr class="bg-gray-100">
                                <td colspan="3" class="border border-gray-800 p-3 text-lg font-black text-right uppercase">Tổng cộng / Grand Total</td>
                                <td class="border border-gray-800 p-3 text-lg font-black text-right">{{ number_format($invoice_data['total'], 0, ',', '.') }}đ</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="flex justify-between items-end mt-12">
                        <div class="text-sm italic">
                            * Vui lòng thanh toán hoá đơn trước ngày 05 hàng tháng.<br>
                            * Please settle the bill before the 5th of each month.
                        </div>
                        <div class="text-center w-64">
                            <p class="font-bold mb-20 uppercase">Quản lý / Manager</p>
                            <p class="font-bold">SALA APPARTMENT</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- BOOKING CONFIRMATION PREVIEW MODAL --}}
    @if($showConfirmationModal && !empty($confirmation_data))
        <div class="fixed inset-0 z-[120] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm print:hidden" wire:click="closeConfirmationModal"></div>
                
                <div class="relative bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[95vh] overflow-y-auto p-0" style="font-family: 'Source Serif 4', Georgia, serif;">
                    <button wire:click="closeConfirmationModal" class="absolute top-4 right-4 z-10 bg-gray-100 hover:bg-gray-200 rounded-full p-2 print:hidden">
                        <x-icon name="heroicon-o-x-mark" class="h-5 w-5" />
                    </button>

                    <button onclick="printBookingConfirmation('confirmationBillContent')" class="absolute top-4 right-16 z-10 bg-blue-600 hover:bg-blue-700 text-white rounded-full p-2">
                        <x-icon name="heroicon-o-printer" class="h-5 w-5" />
                    </button>

                    <style>
                        :root {
                          --gold: #b8975a;
                          --dark: #1a1a1a;
                          --mid: #444;
                          --light: #f8f5f0;
                          --border: #d4c9b0;
                          --accent: #8b7340;
                        }

                        .booking-confirmation-page {
                          background: #fff;
                          width: 100%;
                          padding: 80px 100px;
                          position: relative;
                          overflow: hidden;
                          color: var(--dark);
                        }

                        /* Watermark */
                        .booking-confirmation-page::before {
                          content: "S";
                          position: absolute;
                          font-family: 'Playfair Display', serif;
                          font-size: 400px;
                          color: rgba(184,151,90,0.04);
                          top: 50%;
                          left: 50%;
                          transform: translate(-50%, -50%);
                          pointer-events: none;
                          line-height: 1;
                        }



                        .header-lux {
                          display: flex;
                          align-items: center;
                          justify-content: space-between;
                          margin-bottom: 32px;
                          gap: 20px;
                        }

                        .logo-area {
                          display: flex;
                          align-items: center;
                          gap: 14px;
                          flex-shrink: 0;
                        }

                        .logo-img {
                          width: 100px;
                          height: auto;
                          object-fit: contain;
                        }

                        .hotel-info {
                          text-align: center;
                          flex: 1;
                        }

                        .hotel-name {
                          font-family: 'Playfair Display', serif;
                          font-size: 20px;
                          font-weight: 700;
                          color: var(--dark);
                          letter-spacing: 0.05em;
                          text-transform: uppercase;
                        }

                        .hotel-contact {
                          font-size: 12.5px;
                          color: var(--mid);
                          line-height: 2;
                          margin-top: 4px;
                        }

                        .qr-box-lux {
                          width: 64px;
                          height: 64px;
                          border: 1.5px solid var(--border);
                          flex-shrink: 0;
                          display: flex;
                          align-items: center;
                          justify-content: center;
                          overflow: hidden;
                        }

                        .divider-lux {
                          display: flex;
                          align-items: center;
                          gap: 12px;
                          margin: 4px 0 28px;
                        }
                        .divider-line { flex: 1; height: 1px; background: var(--border); }
                        .divider-diamond {
                          width: 8px; height: 8px;
                          background: var(--gold);
                          transform: rotate(45deg);
                          flex-shrink: 0;
                        }

                        .title-block {
                          text-align: center;
                          margin-bottom: 28px;
                        }

                        .title-label {
                          font-family: 'Playfair Display', serif;
                          font-size: 26px;
                          font-weight: 700;
                          letter-spacing: 0.15em;
                          text-transform: uppercase;
                          color: var(--dark);
                        }

                        .title-sub {
                          font-size: 12.5px;
                          color: var(--mid);
                          letter-spacing: 0.08em;
                          margin-top: 4px;
                          font-style: italic;
                        }

                        .intro {
                          font-size: 13.5px;
                          line-height: 1.8;
                          text-align: center;
                          color: var(--mid);
                          margin-bottom: 28px;
                          font-style: italic;
                        }

                        .details-table {
                          width: 100%;
                          border-collapse: collapse;
                          font-size: 13.5px;
                          margin-bottom: 28px;
                        }

                        .details-table td {
                          padding: 14px 18px;
                          border: 1px solid var(--border);
                          vertical-align: top;
                          line-height: 1.6;
                        }

                        .details-table .label {
                          color: var(--mid);
                          font-weight: 300;
                          white-space: nowrap;
                          width: 25%;
                        }

                        .details-table .value {
                          font-weight: 600;
                          color: var(--dark);
                          letter-spacing: 0.02em;
                        }

                        .price-note {
                          font-size: 11.5px;
                          color: var(--mid);
                          font-weight: 300;
                          font-style: italic;
                          margin-top: 4px;
                        }

                        .paid-badge {
                          display: inline-block;
                          background: #e8f4ec;
                          color: #2e7d4f;
                          border: 1px solid #a8d5b5;
                          padding: 1px 10px;
                          border-radius: 20px;
                          font-size: 11px;
                          letter-spacing: 0.06em;
                          text-transform: uppercase;
                          font-weight: 600;
                          margin-left: 8px;
                          vertical-align: middle;
                          font-style: normal;
                        }

                        .signature-lux {
                          text-align: right;
                          margin-top: 32px;
                          padding-top: 20px;
                        }

                        .sig-title {
                          font-size: 12px;
                          letter-spacing: 0.12em;
                          text-transform: uppercase;
                          color: var(--mid);
                          font-weight: 300;
                        }

                        .sig-name {
                          font-family: 'Playfair Display', serif;
                          font-size: 16px;
                          color: var(--dark);
                          margin-top: 4px;
                          font-style: italic;
                        }

                        .sig-company {
                          font-size: 12px;
                          color: var(--gold);
                          letter-spacing: 0.1em;
                          text-transform: uppercase;
                          margin-top: 2px;
                        }

                        .sig-line {
                          width: 160px;
                          height: 1px;
                          background: var(--border);
                          margin: 14px 0 8px auto;
                        }

                    </style>

                    <div class="booking-confirmation-page" id="confirmationBillContent">


                        <!-- Header -->
                        <div class="header-lux">
                            <div class="logo-area">
                                <img src="{{ asset('logo.jpg') }}" alt="Sala Logo" class="logo-img">
                            </div>

                            <div class="hotel-info">
                                <div class="hotel-name">Sala Apartment and Hotel Da Nang</div>
                                <div class="hotel-contact">
                                    Hotline: +84 84 424 4567<br>
                                    Address: 16 Ly Nhat Quang, Son Tra district, Da Nang city
                                </div>
                            </div>

                            <div class="qr-box-lux">
                                <img src="{{ asset('qr.jpg') }}" alt="QR" class="w-full h-full object-contain">
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="divider-lux">
                            <div class="divider-line"></div>
                            <div class="divider-diamond"></div>
                            <div class="divider-line"></div>
                        </div>

                        <!-- Title -->
                        <div class="title-block">
                            <div class="title-label">Booking Confirmation</div>
                        </div>

                        <!-- Intro -->
                        <p class="intro">
                            Thank you for choosing and using services at Sala Apartment and Hotel for your trip.<br>
                            Sala Apartment and Hotel would like to confirm your booking:
                        </p>

                        <!-- Details Table -->
                        <table class="details-table">
                            <tr>
                                <td class="label">Names of the guest</td>
                                <td class="value">
                                    <div class="font-black">{{ $confirmation_data['customer_name'] }}</div>
                                    @if(!empty($confirmation_data['additional_guests']))
                                        <div class="text-[11px] text-gray-500 font-medium mt-1">
                                            Stay with: {{ collect($confirmation_data['additional_guests'])->pluck('name')->join(', ') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="label">Phone number</td>
                                <td class="value">{{ $confirmation_data['customer_phone'] }}</td>
                             </tr>
                             <tr>
                                <td class="label">Room</td>
                                <td class="value">{{ $confirmation_data['room_code'] }}</td>
                                <td class="label">Number of guests</td>
                                <td class="value">{{ sprintf('%02d', count($confirmation_data['additional_guests'] ?? []) + 1) }}</td>
                             </tr>
                            <tr>
                                <td class="label">Arrival date</td>
                                <td class="value">{{ $confirmation_data['check_in'] }}</td>
                                <td class="label">Term of stay</td>
                                <td class="value">{{ $confirmation_data['term_of_stay'] }}</td>
                            </tr>
                            <tr class="full-row">
                                <td class="label">Price of the room</td>
                                <td colspan="3" class="value">
                                    {{ $confirmation_data['unit_price'] }} VND / {{ $confirmation_data['price_type'] }}
                                    <div class="price-note">(not included deposit fee, electric fee and water fee)</div>
                                </td>
                            </tr>
                            <tr class="full-row">
                                <td class="label">Deposit money</td>
                                <td colspan="3" class="value">
                                    {{ number_format($confirmation_data['total_deposit'], 0, ',', '.') }} VND
                                    @if($confirmation_data['total_deposit'] > 0)
                                        <span class="paid-badge">✓ Paid</span>
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <!-- Divider -->
                        <div class="divider-lux">
                            <div class="divider-line"></div>
                            <div class="divider-diamond"></div>
                            <div class="divider-line"></div>
                        </div>

                        <!-- Signature -->
                        <div class="signature-lux">
                            <div class="sig-title">Apartment Confirmation</div>
                            <div class="sig-line"></div>
                            <div class="sig-name">Ngo Thi Phuong Thao</div>
                            <div class="sig-company">Sala Apartment</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
