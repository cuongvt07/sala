@props([
    'label' => '',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Select an option',
    'error' => null
])

<div x-data="{
    open: false,
    search: '',
    selected: @entangle($attributes->wire('model')),
    options: {{ json_encode($options) }},
    get filteredOptions() {
        if (this.search === '') {
            return this.options;
        }
        return this.options.filter(option => option.toLowerCase().includes(this.search.toLowerCase()));
    },
    select(option) {
        this.selected = option;
        this.open = false;
        this.search = '';
    },
    init() {
        if (this.selected && !this.options.includes(this.selected)) {
            // Handle case where selected value is not in options (manually entered or legacy)
            // Ideally we'd add it to options or just display it
        }
    }
}" class="relative w-full">
    
    @if($label)
        <label class="block text-[11px] font-normal text-gray-900 uppercase tracking-widest mb-1.5">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <button type="button" 
            @click="open = !open" 
            class="premium-select text-left relative"
            :class="{'border-red-500 bg-red-50': '{{ $error }}'}"
        >
            <span x-text="selected ? selected : '{{ $placeholder }}'" class="block truncate pr-8" :class="{'text-gray-400': !selected}"></span>
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <x-icon name="heroicon-m-chevron-up-down" class="h-4 w-4 text-gray-400" />
            </span>
        </button>

        @if($error)
            <span class="text-red-500 text-xs mt-1">{{ $error }}</span>
        @endif

        <div x-show="open" 
            @click.away="open = false"
            class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
            style="display: none;">
            
            <div class="px-2 py-2 sticky top-0 bg-white border-b border-gray-100">
                <input x-model="search" type="text" class="premium-input !rounded-lg !p-2 !text-[11px]" placeholder="Tìm kiếm...">
            </div>

            <ul class="max-h-50 overflow-y-auto">
                <template x-for="option in filteredOptions" :key="option">
                    <li @click="select(option)" 
                        class="relative cursor-default select-none py-2 pl-3 pr-9 hover:bg-indigo-50 text-gray-900"
                        :class="{'bg-indigo-50 text-indigo-600': selected === option}">
                        <span x-text="option" class="block truncate" :class="{'font-semibold': selected === option, 'font-normal': selected !== option}"></span>
                        
                        <span x-show="selected === option" class="absolute inset-y-0 right-0 flex items-center pr-4 text-indigo-600">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </li>
                </template>
                <li x-show="filteredOptions.length === 0" class="p-2 text-gray-500 text-xs text-center">Không tìm thấy kết quả</li>
            </ul>
        </div>
    </div>
</div>
