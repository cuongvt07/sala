<?php

namespace App\Livewire\Admin\Partials;

use Livewire\Component;
use App\Models\Area;

class GlobalAreaSelector extends Component
{
    public $selectedAreaId;

    public function mount()
    {
        $this->selectedAreaId = session('admin_selected_area_id', '');
    }

    public function updatedSelectedAreaId($value)
    {
        if ($value) {
            session(['admin_selected_area_id' => $value]);
        } else {
            session()->forget('admin_selected_area_id');
        }

        $this->dispatch('area-selected');
        // Removed redirect to allow SPA-like refresh via listeners
    }

    public function render()
    {
        return view('livewire.admin.partials.global-area-selector', [
            'areas' => Area::all(),
        ]);
    }
}
