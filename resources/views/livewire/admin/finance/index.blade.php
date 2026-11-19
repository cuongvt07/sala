<div class="space-y-4">
    @php
        $money = fn($v) => number_format((float) $v, 0, ',', '.');
        $inp = 'rounded-lg border-gray-300 py-1.5 px-2 text-sm focus:ring-blue-500 focus:border-blue-500';
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Sổ thu chi</h1>
            <p class="text-xs text-gray-500">Chỉ ghi các khoản đã xác nhận thanh toán. Thu tiền phòng/cọc ghi ở form đặt phòng; chi phí khác ghi tại đây.</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="openForm('income')" class="px-3 py-2 rounded-lg text-xs font-black text-white bg-green-600 hover:bg-green-700">+ Phiếu thu</button>
            <button wire:click="openForm('expense')" class="px-3 py-2 rounded-lg text-xs font-black text-white bg-red-600 hover:bg-red-700">+ Phiếu chi</button>
            <button wire:click="export" class="px-3 py-2 rounded-lg text-xs font-black text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">⬇ Xuất Excel</button>
        </div>
    </div>

    {{-- Bộ lọc --}}
    <div class="bg-white rounded-xl border border-gray-200 p-3 flex flex-wrap items-center gap-2">
        <input type="date" wire:model.live="from" class="{{ $inp }}">
        <span class="text-gray-400">→</span>
        <input type="date" wire:model.live="to" class="{{ $inp }}">
        <div class="flex gap-1">
            <button wire:click="setRange('today')" class="px-2 py-1 text-[11px] font-bold rounded bg-gray-100 hover:bg-gray-200">Hôm nay</button>
            <button wire:click="setRange('month')" class="px-2 py-1 text-[11px] font-bold rounded bg-gray-100 hover:bg-gray-200">Tháng này</button>
            <button wire:click="setRange('last_month')" class="px-2 py-1 text-[11px] font-bold rounded bg-gray-100 hover:bg-gray-200">Tháng trước</button>
            <button wire:click="setRange('year')" class="px-2 py-1 text-[11px] font-bold rounded bg-gray-100 hover:bg-gray-200">Năm nay</button>
        </div>
        <select wire:model.live="type" class="{{ $inp }}">
            <option value="">Thu + Chi</option>
            <option value="income">Chỉ thu</option>
            <option value="expense">Chỉ chi</option>
        </select>
        <select wire:model.live="method" class="{{ $inp }}">
            <option value="">Mọi hình thức</option>
            <option value="cash">Tiền mặt</option>
            <option value="transfer">Chuyển khoản</option>
        </select>
        @if($areas->isNotEmpty())
            <select wire:model.live="area" class="{{ $inp }}">
                <option value="">Tất cả toà</option>
                @foreach($areas as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
            </select>
        @endif
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Tìm phòng, khách, nội dung..." class="{{ $inp }} flex-1 min-w-[160px]">
        <div wire:loading class="text-xs text-blue-600 font-bold">Đang tải...</div>
    </div>

    {{-- Tổng kết --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="bg-white rounded-xl border-2 border-green-200 p-4">
            <p class="text-[10px] font-black text-green-700 uppercase">Tổng thu</p>
            <p class="text-2xl font-black text-green-700">{{ $money($summary['income']) }}đ</p>
            <p class="text-[11px] text-gray-500 mt-1">Tiền mặt {{ $money($summary['income_cash']) }} · CK {{ $money($summary['income_transfer']) }}</p>
        </div>
        <div class="bg-white rounded-xl border-2 border-red-200 p-4">
            <p class="text-[10px] font-black text-red-700 uppercase">Tổng chi</p>
            <p class="text-2xl font-black text-red-600">{{ $money($summary['expense']) }}đ</p>
            <p class="text-[11px] text-gray-500 mt-1">Tiền mặt {{ $money($summary['expense_cash']) }} · CK {{ $money($summary['expense_transfer']) }}</p>
        </div>
        @php $net = $summary['income'] - $summary['expense']; @endphp
        <div class="rounded-xl p-4 {{ $net >= 0 ? 'bg-slate-800' : 'bg-red-700' }} text-white">
            <p class="text-[10px] font-black uppercase opacity-80">Còn lại (thu − chi)</p>
            <p class="text-2xl font-black">{{ $money($net) }}đ</p>
            <p class="text-[11px] opacity-80 mt-1">Tiền mặt {{ $money($summary['income_cash'] - $summary['expense_cash']) }} · CK {{ $money($summary['income_transfer'] - $summary['expense_transfer']) }}</p>
        </div>
    </div>

    @if($byCategory->isNotEmpty())
        <div class="flex flex-wrap gap-2">
            @foreach($byCategory as $c)
                <span class="text-[11px] px-2 py-1 rounded-full border {{ $c->type === 'income' ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-700' }}">
                    {{ $c->category }}: <b>{{ $money($c->total) }}</b>
                </span>
            @endforeach
        </div>
    @endif

    {{-- Bảng thu chi --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase">
                <tr>
                    <th class="px-3 py-2.5 text-left">Ngày</th>
                    <th class="px-3 py-2.5 text-left">Nội dung</th>
                    <th class="px-3 py-2.5 text-left">Hình thức</th>
                    <th class="px-3 py-2.5 text-right text-green-700">Thu</th>
                    <th class="px-3 py-2.5 text-right text-red-600">Chi</th>
                    <th class="px-3 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rows as $t)
                    <tr wire:key="t-{{ $t->id }}" class="hover:bg-gray-50">
                        <td class="px-3 py-2 whitespace-nowrap text-xs">{{ $t->txn_date->format('d/m/Y') }}</td>
                        <td class="px-3 py-2">
                            <div class="font-bold text-gray-800 text-xs">{{ $t->category }}
                                @if($t->room_code)<span class="font-normal text-gray-500">· P.{{ $t->room_code }}</span>@endif
                                @if($t->customer_name)<span class="font-normal text-gray-500">· {{ $t->customer_name }}</span>@endif
                            </div>
                            <div class="text-[11px] text-gray-500">{{ $t->description }} @if($t->user) <span class="text-gray-300">· {{ $t->user->name }}</span>@endif</div>
                        </td>
                        <td class="px-3 py-2 text-xs whitespace-nowrap">{{ $t->method_label }}</td>
                        <td class="px-3 py-2 text-right font-bold text-green-700 whitespace-nowrap">{{ $t->type === 'income' ? $money($t->amount) : '' }}</td>
                        <td class="px-3 py-2 text-right font-bold text-red-600 whitespace-nowrap">{{ $t->type === 'expense' ? $money($t->amount) : '' }}</td>
                        <td class="px-3 py-2 text-right">
                            @if(auth()->user()->isAdminLevel())
                                <button wire:click="deleteTxn({{ $t->id }})" wire:confirm="Xoá phiếu này khỏi sổ thu chi?" class="text-[11px] font-bold text-red-400 hover:text-red-600">Xoá</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-3 py-10 text-center text-gray-400">Không có phiếu nào trong khoảng này</td></tr>
                @endforelse
            </tbody>
            @if($rows->isNotEmpty())
                <tfoot class="bg-gray-50 font-black">
                    <tr>
                        <td colspan="3" class="px-3 py-2.5 text-right text-xs uppercase">Tổng ({{ $summary['count'] }} phiếu)</td>
                        <td class="px-3 py-2.5 text-right text-green-700">{{ $money($summary['income']) }}</td>
                        <td class="px-3 py-2.5 text-right text-red-600">{{ $money($summary['expense']) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $rows->links() }}</div>
    </div>

    {{-- Form thêm phiếu --}}
    @if($showForm)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-900/60" wire:click="$set('showForm', false)"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-5 space-y-3">
                <h3 class="text-lg font-black {{ $f_type === 'income' ? 'text-green-700' : 'text-red-700' }}">{{ $f_type === 'income' ? 'Phiếu thu' : 'Phiếu chi' }}</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Loại</label>
                        <select wire:model.live="f_type" class="{{ $inp }} w-full">
                            <option value="income">Thu</option>
                            <option value="expense">Chi</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Khoản</label>
                        <select wire:model="f_category" class="{{ $inp }} w-full">
                            @foreach($f_type === 'income' ? \App\Models\Transaction::INCOME_CATEGORIES : \App\Models\Transaction::EXPENSE_CATEGORIES as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Số tiền *</label>
                        <input type="text" wire:model="f_amount" x-on:input="$el.value = $el.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')" class="{{ $inp }} w-full text-right font-black">
                        @error('f_amount')<span class="text-[11px] text-red-500">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Hình thức</label>
                        <select wire:model="f_method" class="{{ $inp }} w-full">
                            <option value="cash">Tiền mặt</option>
                            <option value="transfer">Chuyển khoản</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Ngày</label>
                        <input type="date" wire:model="f_date" class="{{ $inp }} w-full">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Phòng (nếu có)</label>
                        <input type="text" wire:model="f_room_code" class="{{ $inp }} w-full">
                    </div>
                    @if($areas->isNotEmpty())
                        <div class="col-span-2">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Toà nhà</label>
                            <select wire:model="f_area_id" class="{{ $inp }} w-full">
                                <option value="">Chung</option>
                                @foreach($areas as $a)<option value="{{ $a->id }}">{{ $a->name }}</option>@endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-span-2">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1">Nội dung</label>
                        <input type="text" wire:model="f_description" class="{{ $inp }} w-full" placeholder="VD: Sửa máy lạnh P.201, tiền điện tháng 11...">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button wire:click="$set('showForm', false)" class="text-sm font-bold text-gray-500">Huỷ</button>
                    <button wire:click="saveForm" class="px-5 py-2 rounded-lg text-sm font-black text-white {{ $f_type === 'income' ? 'bg-green-600' : 'bg-red-600' }}">Lưu phiếu</button>
                </div>
            </div>
        </div>
    @endif
</div>
