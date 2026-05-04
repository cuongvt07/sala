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

    public function setArea($id)
    {
        $this->selectedAreaId = $id;
        if ($id) {
            session(['admin_selected_area_id' => $id]);
        } else {
            session()->forget('admin_selected_area_id');
        }

        $this->dispatch('area-selected');
    }

    public function render()
    {
        try {
            $areas = Area::all();
        } catch (\Exception $e) {
            $areas = collect();
        }

        return view('livewire.admin.partials.global-area-selector', [
            'areas' => $areas,
        ]);
    }
}
