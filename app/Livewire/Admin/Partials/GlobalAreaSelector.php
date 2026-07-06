<?php

namespace App\Livewire\Admin\Partials;

use Livewire\Component;
use App\Models\Area;

class GlobalAreaSelector extends Component
{
    public $selectedAreaId;

    public function mount()
    {
        // Nhân viên bị khóa toà: luôn cố định về toà của họ
        if (auth()->check() && auth()->user()->isAreaRestricted()) {
            $this->selectedAreaId = auth()->user()->area_id;
            session(['admin_selected_area_id' => auth()->user()->area_id]);
            return;
        }

        $this->selectedAreaId = session('admin_selected_area_id', '');
    }

    public function setArea($id)
    {
        // Nhân viên bị khóa toà không được đổi sang toà khác
        if (auth()->check() && auth()->user()->isAreaRestricted()) {
            $this->selectedAreaId = auth()->user()->area_id;
            session(['admin_selected_area_id' => auth()->user()->area_id]);
            return;
        }

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
            // Nhân viên bị khóa toà chỉ thấy đúng toà của mình
            if (auth()->check() && auth()->user()->isAreaRestricted()) {
                $areas = Area::where('id', auth()->user()->area_id)->get();
            } else {
                $areas = Area::all();
            }
        } catch (\Exception $e) {
            $areas = collect();
        }

        return view('livewire.admin.partials.global-area-selector', [
            'areas' => $areas,
        ]);
    }
}
