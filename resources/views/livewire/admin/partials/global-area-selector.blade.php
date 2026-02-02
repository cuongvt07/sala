<div class="flex items-center space-x-2 bg-gray-100 p-1 rounded-lg border border-gray-200">
    <button 
        wire:click="$set('selectedAreaId', '')"
        class="px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $selectedAreaId === '' ? 'bg-white text-blue-600 shadow-sm ring-1 ring-black/5' : 'text-gray-900 hover:text-black hover:bg-gray-200/50' }}"
    >
        All
    </button>
    @foreach($areas as $area)
        <button 
            wire:click="$set('selectedAreaId', {{ $area->id }})"
            class="px-4 py-2 text-[12px] font-medium rounded-md transition-all duration-200 {{ $selectedAreaId == $area->id ? 'bg-white text-blue-600 shadow-sm ring-1 ring-black/5' : 'text-gray-900 hover:text-black hover:bg-gray-200/50' }}"
        >
            {{ $area->name }}
        </button>
    @endforeach
</div>
