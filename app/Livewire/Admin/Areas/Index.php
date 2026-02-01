<?php

namespace App\Livewire\Admin\Areas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Area;

class Index extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editingAreaId = null;

    // Form inputs
    public $name;
    public $address;
    public $description;

    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'nullable|string|max:255',
        'description' => 'nullable|string',
    ];

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'address', 'description', 'editingAreaId']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $area = Area::findOrFail($id);
        $this->editingAreaId = $id;
        $this->name = $area->name;
        $this->address = $area->address;
        $this->description = $area->description;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingAreaId) {
            $area = Area::find($this->editingAreaId);
            $area->update([
                'name' => $this->name,
                'address' => $this->address,
                'description' => $this->description,
            ]);
            $message = 'Cập nhật khu vực thành công.';
        } else {
            Area::create([
                'name' => $this->name,
                'address' => $this->address,
                'description' => $this->description,
            ]);
            $message = 'Thêm khu vực mới thành công.';
        }

        $this->showModal = false;
        session()->flash('success', $message);
        $this->reset(['name', 'address', 'description', 'editingAreaId']);
    }

    public function delete($id)
    {
        Area::find($id)->delete();
        session()->flash('success', 'Xóa khu vực thành công.');
    }

    public function render()
    {
        return view('livewire.admin.areas.index', [
            'areas' => Area::withCount('rooms')->latest()->paginate(10),
        ])->layout('components.layouts.admin');
    }
}
