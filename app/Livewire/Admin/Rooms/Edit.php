<?php

namespace App\Livewire\Admin\Rooms;

use Livewire\Component;
use App\Models\Room;
use App\Models\Area;
use Illuminate\Validation\Rule;

class Edit extends Component
{
    public $roomId;
    public $area_id;
    public $code;
    public $type;
    public $price;
    public $status;
    public $description;

    public function mount($room)
    {
        $roomModel = Room::find($room);
        if (!$roomModel) {
             return redirect()->route('admin.rooms.index');
        }
        
        $this->roomId = $roomModel->id;
        $this->area_id = $roomModel->area_id;
        $this->code = $roomModel->code;
        $this->type = $roomModel->type;
        $this->price = $roomModel->price;
        $this->status = $roomModel->status;
        $this->description = $roomModel->description;
    }

    public function rules()
    {
        return [
            'area_id' => 'required|exists:areas,id',
            'code' => ['required', 'string', 'max:255', Rule::unique('rooms', 'code')->ignore($this->roomId)],
            'type' => 'required|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:available,occupied,maintenance,reserved',
            'description' => 'nullable|string',
        ];
    }

    public function save()
    {
        $this->validate();

        $room = Room::find($this->roomId);
        $room->update([
            'area_id' => $this->area_id,
            'code' => $this->code,
            'type' => $this->type,
            'price' => $this->price,
            'status' => $this->status,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Cập nhật phòng thành công.');

        return redirect()->route('admin.rooms.index');
    }

    public function render()
    {
        return view('livewire.admin.rooms.edit', [
            'areas' => Area::all(),
        ])->layout('components.layouts.admin');
    }
}
