<?php

namespace App\Livewire\Admin\Rooms;

use Livewire\Component;
use App\Models\Room;
use App\Models\Area;

class Create extends Component
{
    public $area_id;
    public $code;
    public $type = 'Studio';
    public $price;
    public $status = 'available';
    public $description;

    protected $rules = [
        'area_id' => 'required|exists:areas,id',
        'code' => 'required|string|max:255|unique:rooms,code',
        'type' => 'required|string',
        'price' => 'required|numeric|min:0',
        'status' => 'required|in:available,occupied,maintenance,reserved',
        'description' => 'nullable|string',
    ];

    public function save()
    {
        $this->validate();

        Room::create([
            'area_id' => $this->area_id,
            'code' => $this->code,
            'type' => $this->type,
            'price' => $this->price,
            'status' => $this->status,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Thêm phòng mới thành công.');

        return redirect()->route('admin.rooms.index');
    }

    public function render()
    {
        return view('livewire.admin.rooms.create', [
            'areas' => Area::all(),
        ])->layout('components.layouts.admin');
    }
}
