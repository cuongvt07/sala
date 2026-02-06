@props(['searchPlaceholder' => 'Tìm kiếm...', 'searchModel' => 'search', 'filters' => []])

<div class="mb-6 grid grid-cols-1 md:grid-cols-{{ count($filters) + 1 }} gap-4">
    {{-- Search Input --}}
    <div class="{{ count($filters) == 1 ? 'md:col-span-1' : 'md:col-span-2' }}">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <x-icon name="heroicon-o-magnifying-glass" class="h-5 w-5 text-gray-400" />
            </div>
            <input wire:model.live.debounce.300ms="{{ $searchModel }}" 
                   type="text" 
                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" 
                   placeholder="{{ $searchPlaceholder }}">
        </div>
    </div>
    
    {{-- Filter Dropdowns --}}
    @foreach($filters as $filter)
        <div>
            <select wire:model.live="{{ $filter['model'] }}" 
                    class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="">-- {{ $filter['label'] ?? 'Tất cả' }} --</option>
                @foreach($filter['options'] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
    @endforeach
    
    {{-- Custom Slot for Additional Filters --}}
    {{ $slot }}
</div>
