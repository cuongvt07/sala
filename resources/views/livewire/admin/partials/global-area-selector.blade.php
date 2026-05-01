<div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200 shadow-inner">
    <button wire:click="setArea('')" 
            class="px-4 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all {{ $selectedAreaId === '' ? 'bg-white text-blue-600 shadow-sm ring-1 ring-slate-200' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
        Tất cả
    </button>
    @foreach($areas as $area)
        <button wire:click="setArea('{{ $area->id }}')" 
                class="px-4 py-1.5 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all {{ (string)$selectedAreaId === (string)$area->id ? 'bg-white text-blue-600 shadow-sm ring-1 ring-slate-200' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
            {{ $area->name }}
        </button>
    @endforeach
</div>
