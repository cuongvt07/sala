@props([
    'label' => '',
    'error' => null
])

<div x-data="{
    dateValue: @entangle($attributes->wire('model')),
    day: '',
    month: '',
    year: '',
    init() {
        this.parseDate();
        this.$watch('dateValue', value => {
            // Chỉ parse lại nếu giá trị dateValue khác với giá trị hiện tại của 3 ô chọn
            let currentInternal = (this.day && this.month && this.year) 
                ? `${this.year}-${String(this.month).padStart(2, '0')}-${String(this.day).padStart(2, '0')}`
                : null;
            if (value !== currentInternal) {
                this.parseDate();
            }
        });
    },
    parseDate() {
        if (this.dateValue) {
            let d = new Date(this.dateValue);
            if (!isNaN(d.getTime())) {
                this.day = d.getDate();
                this.month = d.getMonth() + 1;
                this.year = d.getFullYear();
            }
        } else {
            this.day = '';
            this.month = '';
            this.year = '';
        }
    },
    update() {
        if (this.day && this.month && this.year) {
            this.dateValue = `${this.year}-${String(this.month).padStart(2, '0')}-${String(this.day).padStart(2, '0')}`;
        } else if (!this.day || !this.month || !this.year) {
            // Nếu một trong 3 ô trống thì xóa giá trị dateValue (hoặc giữ nguyên tùy logic, nhưng thường là set null để tránh lỗi định dạng)
            this.dateValue = null;
        }
    }
}" class="w-full">
    @if($label)
        <label class="block text-[11px] font-bold text-gray-600 uppercase tracking-widest mb-1.5">
            {{ $label }}
        </label>
    @endif

    <div class="flex gap-1.5">
        {{-- Day --}}
        <div class="flex-1">
            <select x-model="day" @change="update()" class="w-full rounded-lg border-gray-200 bg-gray-50 p-2 text-xs font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 appearance-none cursor-pointer">
                <option value="">D</option>
                @for($i=1; $i<=31; $i++)
                    <option value="{{ $i }}">{{ sprintf('%02d', $i) }}</option>
                @endfor
            </select>
        </div>

        {{-- Month --}}
        <div class="flex-1">
            <select x-model="month" @change="update()" class="w-full rounded-lg border-gray-200 bg-gray-50 p-2 text-xs font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 appearance-none cursor-pointer">
                <option value="">M</option>
                @for($i=1; $i<=12; $i++)
                    <option value="{{ $i }}">{{ sprintf('%02d', $i) }}</option>
                @endfor
            </select>
        </div>

        {{-- Year --}}
        <div class="flex-[1.5]">
            <select x-model="year" @change="update()" class="w-full rounded-lg border-gray-200 bg-gray-50 p-2 text-xs font-bold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 appearance-none cursor-pointer">
                <option value="">Y</option>
                @php $currentYear = date('Y'); @endphp
                @for($i=$currentYear + 10; $i>=$currentYear - 80; $i--)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>
    
    @if($error)
        <p class="text-[10px] text-red-500 mt-1 font-medium">{{ $error }}</p>
    @endif
</div>
