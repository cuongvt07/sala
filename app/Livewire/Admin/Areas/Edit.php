<?php

namespace App\Livewire\Admin\Areas;

use Livewire\Component;
use App\Models\Area;

class Edit extends Component
{
    public $areaId;
    public $name;
    public $address;
    public $description;

    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'nullable|string|max:255',
        'description' => 'nullable|string',
    ];

    public function mount($area)
    {
        $areaModel = Area::find($area);
        if (!$areaModel) {
             return redirect()->route('admin.areas.index');
        }
        
        $this->areaId = $areaModel->id;
        $this->name = $areaModel->name;
        $this->address = $areaModel->address;
        $this->description = $areaModel->description;
    }

    public function save()
    {
        $this->validate();

        $area = Area::find($this->areaId);
        $area->update([
            'name' => $this->name,
            'address' => $this->address,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Cập nhật khu vực thành công.');

        return redirect()->route('admin.areas.index');
    }

    public function render()
    {
        return view('livewire.admin.areas.edit')->layout('components.layouts.admin');
    }
}
