<div>
    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    <div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Nhật ký hoạt động</h1>
        <p class="text-sm text-gray-500">Theo dõi lịch sử chỉnh sửa dữ liệu của hệ thống.</p>
    </div>

    <!-- Filters -->
    <div class="mb-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <div>
                <label class="block text-xs font-medium uppercase text-gray-500">Tài khoản</label>
                <select wire:model.live="userId" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Tất cả</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium uppercase text-gray-500">Hành động</label>
                <select wire:model.live="action" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Tất cả</option>
                    <option value="created">Thêm mới</option>
                    <option value="updated">Cập nhật</option>
                    <option value="deleted">Xóa</option>
                </select>
            </div>
            <div x-data="{ 
                fp: null,
                init() {
                    this.fp = flatpickr($refs.dateFrom, {
                        altInput: true,
                        altFormat: 'd/m/Y',
                        dateFormat: 'Y-m-d',
                        onChange: (selectedDates, dateStr) => {
                            $wire.set('dateFrom', dateStr);
                        }
                    });
                    $watch('dateFrom', value => this.fp.setDate(value));
                }
            }">
                <label class="block text-xs font-medium uppercase text-gray-500">Từ ngày</label>
                <input x-ref="dateFrom" type="text" wire:model.live="dateFrom" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div x-data="{ 
                fp: null,
                init() {
                    this.fp = flatpickr($refs.dateTo, {
                        altInput: true,
                        altFormat: 'd/m/Y',
                        dateFormat: 'Y-m-d',
                        onChange: (selectedDates, dateStr) => {
                            $wire.set('dateTo', dateStr);
                        }
                    });
                    $watch('dateTo', value => this.fp.setDate(value));
                }
            }">
                <label class="block text-xs font-medium uppercase text-gray-500">Đến ngày</label>
                <input x-ref="dateTo" type="text" wire:model.live="dateTo" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex items-end">
                <button wire:click="resetFilters" class="w-full rounded-lg border border-gray-300 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Xóa lọc</button>
            </div>
        </div>
    </div>

    <!-- Logs List -->
    <div class="space-y-4 max-h-[calc(100vh-320px)] overflow-y-auto pr-2 custom-scrollbar">
        @forelse($logs as $log)
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200" x-data="{ open: false }">
                <div class="flex items-center justify-between px-6 py-4 cursor-pointer hover:bg-gray-50" @click="open = !open">
                    <div class="flex items-center gap-4">
                        <div @class([
                            'flex h-10 w-10 items-center justify-center rounded-full',
                            'bg-green-100 text-green-700' => $log->action === 'created',
                            'bg-blue-100 text-blue-700' => $log->action === 'updated',
                            'bg-red-100 text-red-700' => $log->action === 'deleted',
                        ])>
                            <x-icon name="heroicon-o-{{ $log->action === 'created' ? 'plus' : ($log->action === 'updated' ? 'pencil' : 'trash') }}" class="h-6 w-6" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900">{{ $log->user->name ?? 'Hệ thống' }}</span>
                                <span class="text-sm text-gray-500">đã {{ $log->action === 'created' ? 'thêm mới' : ($log->action === 'updated' ? 'cập nhật' : 'xóa') }}</span>
                                <span class="font-semibold text-blue-600">{{ str_replace('App\Models\\', '', $log->model_type) }} #{{ $log->model_id }}</span>
                            </div>
                            <div class="mt-1 flex items-center gap-4 text-xs text-gray-400">
                                <span><x-icon name="heroicon-o-clock" class="inline h-3 w-3 mr-1" />{{ $log->created_at->format('H:i:s d/m/Y') }}</span>
                                <span><x-icon name="heroicon-o-computer-desktop" class="inline h-3 w-3 mr-1" />{{ $log->ip_address }}</span>
                            </div>
                        </div>
                    </div>
                    <x-icon name="heroicon-o-chevron-down" class="h-5 w-5 text-gray-400 transition-transform" ::class="open ? 'rotate-180' : ''" />
                </div>

                <div x-show="open" x-collapse class="border-t border-gray-100 bg-gray-50 p-6">
                    @if($log->action === 'updated')
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div>
                                <h4 class="mb-2 text-xs font-bold uppercase text-gray-400">Giá trị cũ</h4>
                                <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
                                    @foreach($log->old_values ?? [] as $key => $val)
                                        <div class="mb-2 last:mb-0">
                                            <span class="text-xs font-medium text-gray-500">{{ $key }}:</span>
                                            <span class="text-sm text-gray-900">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <h4 class="mb-2 text-xs font-bold uppercase text-gray-400">Giá trị mới</h4>
                                <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
                                    @foreach($log->new_values ?? [] as $key => $val)
                                        <div class="mb-2 last:mb-0">
                                            <span class="text-xs font-medium text-gray-500">{{ $key }}:</span>
                                            <span @class([
                                                'text-sm text-gray-900',
                                                'font-bold text-blue-600' => isset($log->old_values[$key]) && $log->old_values[$key] != $val
                                            ])>{{ is_array($val) ? json_encode($val) : $val }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div>
                            <h4 class="mb-2 text-xs font-bold uppercase text-gray-400">Dữ liệu chi tiết</h4>
                            <div class="rounded-lg bg-white p-4 shadow-sm ring-1 ring-gray-200">
                                @foreach(($log->new_values ?: $log->old_values) ?? [] as $key => $val)
                                    <div class="mb-2 last:mb-0">
                                        <span class="text-xs font-medium text-gray-500">{{ $key }}:</span>
                                        <span class="text-sm text-gray-900">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="mt-4 text-[10px] text-gray-400 italic">
                        User Agent: {{ $log->user_agent }}
                    </div>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center rounded-xl bg-white py-12 shadow-sm ring-1 ring-gray-200">
                <x-icon name="heroicon-o-clipboard" class="h-12 w-12 text-gray-300" />
                <p class="mt-2 text-gray-500">Không có lịch sử hoạt động nào được tìm thấy.</p>
            </div>
        @endforelse

        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    </div>
</div>
</div>
