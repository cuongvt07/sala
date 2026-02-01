@props([
    'variant' => 'gray', // gray, blue, green, red, yellow, purple
])

@php
    $variants = [
        'gray' => 'bg-gray-100 text-gray-800',
        'blue' => 'bg-blue-100 text-blue-800',
        'green' => 'bg-green-100 text-green-800',
        'red' => 'bg-red-100 text-red-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'purple' => 'bg-purple-100 text-purple-800',
    ];

    $classes = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . ($variants[$variant] ?? $variants['gray']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
