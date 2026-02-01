@props([
    'label' => null,
    'error' => null,
    'helper' => null,
    'compact' => false,
])

<div class="space-y-1.5">
    @if ($label)
        <label {{ $attributes->has('id') ? 'for=' . $attributes->get('id') : '' }} class="block font-black text-gray-400 uppercase tracking-widest {{ $compact ? 'text-[9px] mb-0.5' : 'text-xs mb-1.5' }}">
            {{ $label }} 
            @if($attributes->has('required')) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <div class="relative">
        <textarea {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border-gray-200 text-gray-900 transition-all shadow-sm' 
                . ($compact ? ' p-2 text-xs font-bold' : ' p-2.5 text-sm')
                . ($attributes->has('readonly') || $attributes->has('disabled') 
                    ? ' bg-gray-100/80 text-gray-500 cursor-not-allowed border-gray-100' 
                    : ' bg-white hover:border-blue-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10')
                . ($error ? ' border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500/10' : '')
        ]) }}></textarea>
    </div>

    @if ($error)
        <p class="text-xs text-red-500">{{ $error }}</p>
    @endif
    
    @if ($helper)
        <p class="text-xs text-gray-500">{{ $helper }}</p>
    @endif
</div>
