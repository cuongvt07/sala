<div class="space-y-6 relative">
    {{-- Hiệu ứng Loading toàn trang khi đổi khu hoặc thao tác --}}
    <div wire:loading wire:target="handleAreaSelected, selectRoom, clearRoom, delete, save, edit" class="absolute inset-0 z-50 flex items-center justify-center bg-white/40 backdrop-blur-[2px] transition-all duration-300">
        <div class="flex flex-col items-center">
            <div class="w-10 h-10 border-4 border-blue-600/20 border-t-blue-600 rounded-full animate-spin"></div>
            <span class="mt-2 text-xs font-bold text-blue-600 uppercase tracking-widest animate-pulse">Đang tải dữ liệu...</span>
        </div>
    </div>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">
                @if($selectedRoomId)
                    <button wire:click="clearRoom" class="mr-2 text-gray-400 hover:text-gray-600 transition-colors">
                        <x-icon name="heroicon-o-chevron-left" class="w-6 h-6 inline" />
                    </button>
                    Bảo dưỡng: {{ $selectedRoom->code }}
                @else
                    Quản lý Bảo dưỡng Phòng
                @endif
            </h2>
            <p class="text-xs text-gray-500 mt-1">
                @if($selectedRoomId)
                    Xem lịch sử và chi phí bảo dưỡng chi tiết cho phòng {{ $selectedRoom->code }}
                @else
                    Danh sách các phòng và tổng quan tình trạng bảo dưỡng
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Ô tìm kiếm mã phòng --}}
            @if(!$selectedRoomId)
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-icon name="heroicon-o-magnifying-glass" class="h-4 w-4 text-gray-400 group-focus-within:text-blue-500 transition-colors" />
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Tìm mã phòng..." 
                    class="block w-64 pl-10 pr-3 py-2 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-400 focus:outline-none focus:placeholder-gray-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 sm:text-sm transition-all duration-300"
                >
                @if($search)
                    <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <x-icon name="heroicon-o-x-mark" class="h-4 w-4" />
                    </button>
                @endif
            </div>
            @endif

            <x-ui.button wire:click="create" variant="primary" size="md">
                + Thêm lịch bảo dưỡng
            </x-ui.button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="p-3 mb-4 text-sm text-green-700 bg-green-50 border border-green-100 rounded-xl flex items-center gap-2 shadow-sm" role="alert">
            <x-icon name="heroicon-o-check-circle" class="h-5 w-5" />
            <span class="font-medium">{{ session('message') }}</span>
        </div>
    @endif

    @if(!$selectedRoomId)
        {{-- ROOM LIST GRID --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($rooms as $room)
                <div wire:click="selectRoom({{ $room->id }})" class="group cursor-pointer bg-white rounded-2xl border border-gray-100 p-4 hover:shadow-xl hover:border-blue-200 transition-all duration-300 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:opacity-20 transition-opacity">
                        <x-icon name="heroicon-o-wrench-screwdriver" class="w-12 h-12 text-blue-600" />
                    </div>
                    
                    <div class="relative z-10">
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ $room->area->name ?? 'Khu vực' }}</div>
                        <div class="text-xl font-black text-gray-900 mb-4">{{ $room->code }}</div>
                        
                        <div class="flex justify-between items-end">
                            <div>
                                <div class="text-[10px] text-gray-500 font-bold uppercase">Số lần</div>
                                <div class="text-sm font-black text-blue-600">{{ $room->maintenances_count }} lần</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] text-gray-500 font-bold uppercase">Tổng phí</div>
                                <div class="text-sm font-black text-gray-900">{{ number_format($room->maintenances_sum_cost ?: 0, 0, ',', '.') }}đ</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-t border-gray-50 flex items-center justify-center gap-1 text-[10px] font-bold text-blue-500 opacity-0 group-hover:opacity-100 transition-opacity uppercase">
                        Xem lịch sử <x-icon name="heroicon-o-arrow-right" class="w-3 h-3" />
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- ROOM HISTORY VIEW --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="bg-blue-600 text-white px-3 py-1 rounded-lg text-sm font-black">{{ $selectedRoom->code }}</div>
                    <div class="h-4 w-px bg-gray-300"></div>
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-tighter">Tổng cộng: <span class="text-blue-600">{{ number_format($maintenances->total(), 0) }} bản ghi</span></div>
                </div>
                <button wire:click="clearRoom" class="text-xs font-bold text-gray-400 hover:text-red-500 transition-colors uppercase">Đóng tab</button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-[10px] text-gray-400 uppercase tracking-widest bg-white border-b border-gray-50">
                        <tr>
                            <th class="px-6 py-4 font-black">Ngày thực hiện</th>
                            <th class="px-6 py-4 font-black">Hạng mục bảo dưỡng</th>
                            <th class="px-6 py-4 font-black text-right">Chi phí</th>
                            <th class="px-6 py-4 font-black text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse ($maintenances as $item)
                            <tr class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-[13px] font-bold text-gray-900">{{ $item->maintenance_date->format('d/m/Y') }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $item->maintenance_date->diffForHumans() }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-[13px] font-black text-gray-800">{{ $item->task_name }}</div>
                                    @if($item->description)
                                        <div class="text-[11px] text-gray-500 mt-0.5">{{ $item->description }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="text-[13px] font-black text-blue-600">{{ number_format($item->cost, 0, ',', '.') }}đ</div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="edit({{ $item->id }})" class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Sửa">
                                            <x-icon name="heroicon-o-pencil-square" class="h-4 w-4" />
                                        </button>
                                        <button wire:click="delete({{ $item->id }})" wire:confirm="Bạn có chắc chắn muốn xóa không?" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Xóa">
                                            <x-icon name="heroicon-o-trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <x-icon name="heroicon-o-clipboard-document-list" class="w-12 h-12 text-gray-200" />
                                        <div class="text-sm text-gray-400 font-medium">Chưa có lịch sử bảo dưỡng cho phòng này</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($maintenances && $maintenances->hasPages())
                <div class="px-6 py-4 border-t border-gray-50 bg-gray-50/30">
                    {{ $maintenances->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- MODAL THÊM/SỬA --}}
    <x-ui.modal name="showModal" :title="$editingId ? 'Chỉnh sửa bảo dưỡng' : 'Thêm lịch bảo dưỡng mới'" width="max-w-xl">
        <form wire:submit="save" class="space-y-4">
            <div class="grid grid-cols-1 gap-4">
                {{-- Room selection (only if not selected in main view) --}}
                @if(!$selectedRoomId)
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Phòng cần bảo dưỡng</label>
                        <select wire:model="room_id" class="w-full rounded-xl border-gray-200 bg-gray-50 p-2.5 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">-- Chọn phòng --</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->code }} ({{ $room->area->name ?? '' }})</option>
                            @endforeach
                        </select>
                        @error('room_id') <span class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</span> @enderror
                    </div>
                @else
                    <div class="bg-blue-50 border border-blue-100 p-3 rounded-xl flex items-center justify-between">
                        <div>
                            <div class="text-[9px] text-blue-600 font-black uppercase tracking-widest">Đang thực hiện cho phòng</div>
                            <div class="text-lg font-black text-blue-900">{{ $selectedRoom->code }}</div>
                        </div>
                        <x-icon name="heroicon-o-wrench" class="w-8 h-8 text-blue-200" />
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Ngày thực hiện</label>
                        <input type="date" wire:model="maintenance_date" class="w-full rounded-xl border-gray-200 bg-gray-50 p-2.5 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @error('maintenance_date') <span class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Chi phí (VNĐ)</label>
                        <input type="text" wire:model="cost" 
                               x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                               class="w-full rounded-xl border-gray-200 bg-gray-50 p-2.5 text-sm font-black text-blue-600 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" 
                               placeholder="0">
                        @error('cost') <span class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Hạng mục bảo dưỡng</label>
                    <input type="text" wire:model="task_name" placeholder="Ví dụ: Vệ sinh máy lạnh, Thay vòi nước..." class="w-full rounded-xl border-gray-200 bg-gray-50 p-2.5 text-sm font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    @error('task_name') <span class="text-red-500 text-[10px] font-bold mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Mô tả chi tiết</label>
                    <textarea wire:model="description" rows="3" class="w-full rounded-xl border-gray-200 bg-gray-50 p-2.5 text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" placeholder="Mô tả cụ thể công việc đã thực hiện..."></textarea>
                </div>
            </div>

            <div class="flex justify-end pt-4 gap-3">
                <x-ui.button wire:click="$set('showModal', false)" variant="secondary" type="button">Hủy bỏ</x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    {{ $editingId ? 'Cập nhật' : 'Lưu lại' }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
