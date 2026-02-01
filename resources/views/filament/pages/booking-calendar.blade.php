<x-filament-panels::page>
    <div class="flex flex-col gap-4">
        {{-- Header Controls --}}
        <div class="flex justify-between items-center bg-white p-4 rounded-lg shadow sticky top-0 z-50">
            <div class="flex items-center gap-4">
                <h2 class="text-2xl font-bold text-gray-800">{{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</h2>
                <div class="w-64">
                    <select wire:model.live="selectedArea" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option value="">-- All Areas --</option>
                        @foreach(\App\Models\Area::all() as $area)
                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-2">
                <x-filament::button icon="heroicon-m-chevron-left" wire:click="prevMonth" color="gray">
                    Previous
                </x-filament::button>
                <x-filament::button icon="heroicon-m-chevron-right" wire:click="nextMonth" color="gray">
                    Next
                </x-filament::button>
            </div>
        </div>

        {{-- Grid Container --}}
        <div class="overflow-x-auto border rounded-lg bg-white shadow" style="max-height: calc(100vh - 200px);">
            @php
                $daysCount = count($this->daysInMonth);
                $gridTemplate = "12rem repeat($daysCount, minmax(3.5rem, 1fr))";
                $rowIndex = 1;
            @endphp

            <div class="grid" style="grid-template-columns: {{ $gridTemplate }}; min-width: max-content;">
                
                {{-- Header Row (Days) --}}
                <div class="sticky top-0 left-0 z-40 bg-gray-50 border-b border-r p-2 font-bold text-gray-700 flex items-center justify-center shadow-[2px_2px_5px_rgba(0,0,0,0.05)]" 
                     style="grid-column: 1; grid-row: 1;">
                    Room
                </div>
                
                @foreach($this->daysInMonth as $index => $day)
                    <div class="sticky top-0 z-30 bg-white border-b border-r p-1 text-center {{ $day->isToday() ? 'bg-blue-50' : '' }}" 
                         style="grid-column: {{ $index + 2 }}; grid-row: 1;">
                        <div class="font-bold text-sm">{{ $day->format('d') }}</div>
                        <div class="text-xs text-gray-500">{{ $day->format('D') }}</div>
                    </div>
                @endforeach

                @php $rowIndex = 2; @endphp

                {{-- Rooms & Bookings --}}
                @foreach($this->rooms as $areaName => $rooms)
                    {{-- Area Header --}}
                    <div class="sticky left-0 z-20 bg-gray-100 border-b p-2 font-bold text-gray-600 uppercase text-xs tracking-wider" 
                         style="grid-column: 1 / -1; grid-row: {{ $rowIndex }};">
                        {{ $areaName }}
                    </div>
                    @php $rowIndex++; @endphp

                    @foreach($rooms as $room)
                        {{-- Room Info (Sticky Left) --}}
                        <div class="sticky left-0 z-20 bg-white border-b border-r p-3 flex flex-col justify-center group hover:bg-gray-50 transition-colors shadow-[2px_0_5px_rgba(0,0,0,0.05)]" 
                             style="grid-column: 1; grid-row: {{ $rowIndex }}; height: 4rem;">
                            <span class="font-medium text-gray-800 text-sm truncate">{{ $room->code }}</span>
                            <span class="text-xs text-gray-500 truncate">{{ $room->type }}</span>
                            <span class="text-xs text-gray-400 truncate">${{ number_format($room->price) }}</span>
                        </div>

                        {{-- Background Cells (Clickable) --}}
                        @foreach($this->daysInMonth as $dayIndex => $day)
                            <div wire:click="createBooking({{ $room->id }}, '{{ $day->format('Y-m-d') }}')"
                                 class="border-b border-r cursor-pointer hover:bg-blue-50 transition-colors {{ $day->isWeekend() ? 'bg-gray-50/50' : '' }}"
                                 style="grid-column: {{ $dayIndex + 2 }}; grid-row: {{ $rowIndex }};"
                                 title="Book {{ $room->code }} on {{ $day->format('d/m') }}">
                            </div>
                        @endforeach

                        {{-- Bookings (Overlays) --}}
                        @foreach($room->bookings as $booking)
                            @php
                                $checkIn = \Carbon\Carbon::parse($booking->check_in);
                                $checkOut = \Carbon\Carbon::parse($booking->check_out);
                                
                                // Current month boundaries
                                $monthStart = \Carbon\Carbon::create($year, $month, 1);
                                $monthEnd = $monthStart->copy()->endOfMonth();

                                // Clamp dates
                                $displayStart = $checkIn->max($monthStart);
                                $displayEnd = $checkOut->min($monthEnd);
                                
                                // Calculate grid position
                                // Day 1 is index 0 in array -> Grid Column 2
                                $startCol = $displayStart->day + 1;
                                
                                // Duration
                                $duration = $displayEnd->diffInDays($displayStart);
                                if ($duration < 1 && $displayStart->isSameDay($displayEnd)) $duration = 0.9; // Visual width for same day? 
                                // Actually, grid span must be integer. 
                                // If checkin != checkout, diff is >= 1.
                                // If checkin == checkout (hourly?), we might treat as 1 day for grid.
                                $duration = ceil($duration); 
                                if ($duration < 1) $duration = 1;

                                // Adjust for end of month clamp?
                                // If starts on 31st (Col 32), span 1.
                                
                                $statusColors = [
                                    'confirmed' => 'bg-blue-500 border-blue-600 text-white',
                                    'pending' => 'bg-yellow-400 border-yellow-500 text-yellow-900',
                                    'checked_in' => 'bg-green-500 border-green-600 text-white',
                                    'checked_out' => 'bg-gray-400 border-gray-500 text-white',
                                    'cancelled' => 'bg-red-400 border-red-500 text-white',
                                ];
                                $colorClass = $statusColors[$booking->status] ?? 'bg-gray-500 text-white';
                            @endphp

                            <div wire:click="editBooking({{ $booking->id }})"
                                 class="m-1 rounded text-xs p-1 shadow-sm border cursor-pointer hover:brightness-110 z-10 flex items-center overflow-hidden whitespace-nowrap {{ $colorClass }}"
                                 style="grid-column: {{ $startCol }} / span {{ $duration }}; grid-row: {{ $rowIndex }};"
                                 title="{{ $booking->customer->name }}">
                                <span class="truncate font-semibold px-1">{{ $booking->customer->name }}</span>
                            </div>
                        @endforeach

                        @php $rowIndex++; @endphp
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
