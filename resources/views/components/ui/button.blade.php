@props([
    'variant' => 'primary', // primary, secondary, danger, success, ghost
    'size' => 'md', // sm, md, lg
    'icon' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-bold rounded-xl transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-offset-0 disabled:opacity-50 disabled:cursor-not-allowed uppercase tracking-widest';

    $variants = [
        'primary' => 'bg-blue-600 hover:bg-blue-700 text-white shadow-lg shadow-blue-600/20 active:scale-95 border border-transparent focus:ring-blue-500/20',
        'secondary' => 'bg-white hover:bg-gray-50 text-gray-700 border border-gray-200 shadow-sm active:scale-95 focus:ring-gray-200/50',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white shadow-lg shadow-red-600/20 active:scale-95 border border-transparent focus:ring-red-500/20',
        'success' => 'bg-green-600 hover:bg-green-700 text-white shadow-lg shadow-green-600/20 active:scale-95 border border-transparent focus:ring-green-500/20',
        'ghost' => 'bg-transparent hover:bg-gray-100 text-gray-600 hover:text-gray-900 border border-transparent',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-[10px]',
        'md' => 'px-5 py-2.5 text-xs',
        'lg' => 'px-8 py-3 text-sm',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

<button {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <span class="mr-2 -ml-1">
            {{ $icon }}
        </span>
    @endif
    {{ $slot }}
</button>
