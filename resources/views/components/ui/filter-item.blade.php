@props(['label' => ''])

<div class="space-y-1.5 min-w-0">
    @if($label)
        <label class="text-[10px] font-black uppercase tracking-widest text-gray-400 block truncate">
            {{ $label }}
        </label>
    @endif
    <div class="relative group">
        {{ $slot }}
    </div>
</div>
