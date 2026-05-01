<div class="relative">
    <select wire:model.live="selectedArea" class="premium-select !rounded-full !px-6 !py-1.5 !text-[10px] !bg-slate-50 hover:!bg-white border-slate-200">
        <option value="">-- Tất cả khu vực --</option>
        @foreach($areas as $area)
            <option value="{{ $area->id }}">{{ $area->name }}</option>
        @endforeach
    </select>
</div>
