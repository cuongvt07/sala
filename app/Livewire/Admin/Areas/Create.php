<?php

namespace App\Livewire\Admin\Areas;

use Livewire\Component;
use App\Models\Area;

class Create extends Component
{
    public $name;
    public $address;
    public $description;

    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'nullable|string|max:255',
        'description' => 'nullable|string',
    ];

    public function save()
    {
        $this->validate();

        Area::create([
            'name' => $this->name,
            'address' => $this->address,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Thêm khu vực mới thành công.');

        return redirect()->route('admin.areas.index');
    }

    public function render()
    {
        return view('livewire.admin.areas.create')->layout('components.layouts.admin');
    }
}
