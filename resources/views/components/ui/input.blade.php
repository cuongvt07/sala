@props([
    'label' => null,
    'error' => null,
    'helper' => null,
    'compact' => false,
])

<div class="space-y-1.5">
    @if ($label)
        <label {{ $attributes->has('id') ? 'for=' . $attributes->get('id') : '' }} class="block font-normal text-gray-900 uppercase tracking-widest {{ $compact ? 'text-[9px] mb-0.5' : 'text-[11px] mb-1.5' }}">
            {{ $label }} 
            @if($attributes->has('required')) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <div class="relative">
        <input {{ $attributes->merge([
            'class' => 'premium-input ' 
                . ($compact ? ' p-2 text-[11px] font-bold' : '')
                . ($error ? ' border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500/10' : '')
        ]) }}>
    </div>

    @if ($error)
        <p class="text-[11px] text-red-500">{{ $error }}</p>
    @endif
    
    @if ($helper)
        <p class="text-[11px] text-gray-600">{{ $helper }}</p>
    @endif
</div>
