@props([
    'title' => 'Bộ lọc',
    'icon' => 'heroicon-o-funnel',
    'show' => false
])

<div x-data="{ open: @js($show) }" class="mb-6">
    <button @click="open = !open" 
            class="flex items-center gap-2 text-sm font-bold text-gray-700 hover:text-blue-600 transition-colors group focus:outline-none">
        <x-icon :name="$icon" class="w-5 h-5 text-gray-400 group-hover:text-blue-500 transition-colors" />
        <span class="tracking-tight">{{ $title }}</span>
        <x-icon name="heroicon-o-chevron-down" 
                class="w-4 h-4 transition-transform duration-300" 
                ::class="open ? 'rotate-180' : ''" />
    </button>

    <div x-show="open" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2 scale-[0.98]"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-2 scale-[0.98]"
         {{ $attributes->merge(['class' => 'mt-4 p-6 bg-white/80 backdrop-blur-xl border border-white/20 rounded-3xl shadow-sm ring-1 ring-black/[0.02]']) }}>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-6">
            {{ $slot }}
        </div>
    </div>
</div>
