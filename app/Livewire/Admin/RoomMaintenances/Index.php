<?php

namespace App\Livewire\Admin\RoomMaintenances;

use App\Models\RoomMaintenance;
use App\Models\Room;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editingId = null;

    // Form inputs
    public $room_id;
    public $maintenance_date;
    public $task_name;
    public $description;
    public $cost = 0;

    protected $rules = [
        'room_id' => 'required|exists:rooms,id',
        'maintenance_date' => 'required|date',
        'task_name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'cost' => 'nullable',
    ];

    public function create()
    {
        $this->resetValidation();
        $this->reset(['room_id', 'maintenance_date', 'task_name', 'description', 'cost', 'editingId']);
        
        $this->maintenance_date = date('Y-m-d');
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $record = RoomMaintenance::findOrFail($id);
        
        $this->editingId = $id;
        $this->room_id = $record->room_id;
        $this->maintenance_date = $record->maintenance_date->format('Y-m-d');
        $this->task_name = $record->task_name;
        $this->description = $record->description;
        $this->cost = number_format($record->cost, 0, ',', '.');

        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $cleanCost = str_replace('.', '', $this->cost);

        $data = [
            'room_id' => $this->room_id,
            'maintenance_date' => $this->maintenance_date,
            'task_name' => $this->task_name,
            'description' => $this->description,
            'cost' => $cleanCost ?: 0,
        ];

        if ($this->editingId) {
            RoomMaintenance::find($this->editingId)->update($data);
            session()->flash('message', 'Cập nhật lịch bảo dưỡng thành công!');
        } else {
            RoomMaintenance::create($data);
            session()->flash('message', 'Thêm lịch bảo dưỡng thành công!');
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        RoomMaintenance::find($id)?->delete();
        session()->flash('message', 'Xóa lịch bảo dưỡng thành công!');
    }

    public function render()
    {
        return view('livewire.admin.room-maintenances.index', [
            'maintenances' => RoomMaintenance::with('room.area')->latest('maintenance_date')->paginate(10),
            'rooms' => Room::with('area')->orderBy('code')->get()
        ])->layout('components.layouts.admin');
    }
}
