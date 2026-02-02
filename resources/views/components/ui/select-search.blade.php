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
            class="relative w-full rounded-lg border border-gray-200 bg-white p-2.5 text-left text-[12px] text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
            :class="{'border-red-500 bg-red-50': '{{ $error }}'}"
        >
            <span x-text="selected ? selected : '{{ $placeholder }}'" class="block truncate" :class="{'text-gray-500': !selected}"></span>
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </span>
        </button>

        @if($error)
            <span class="text-red-500 text-xs mt-1">{{ $error }}</span>
        @endif

        <div x-show="open" 
            @click.away="open = false"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
            style="display: none;">
            
            <div class="px-2 py-2 sticky top-0 bg-white border-b border-gray-100">
                <input x-model="search" type="text" class="w-full rounded-md border-gray-300 py-1.5 text-xs text-gray-700 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Tìm kiếm...">
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
