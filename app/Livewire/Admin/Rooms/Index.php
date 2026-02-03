<?php

namespace App\Livewire\Admin\Rooms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Area;
use App\Models\Room;
use Illuminate\Validation\Rule;

class Index extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editingRoomId = null;

    // Form inputs
    public $area_id;
    public $code;
    public $type = 'Studio';
    public $price_day;
    public $price_hour;
    public $status = 'available';
    public $description;

    protected $listeners = ['area-selected' => '$refresh'];

    public function rules()
    {
        return [
            'area_id' => 'required|exists:areas,id',
            'code' => ['required', 'string', 'max:255', Rule::unique('rooms', 'code')->ignore($this->editingRoomId)],
            'type' => 'required|string',
            'price_day' => 'required|numeric|min:0',
            'price_hour' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,occupied,maintenance,reserved',
            'description' => 'nullable|string',
        ];
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['area_id', 'code', 'type', 'price_day', 'price_hour', 'status', 'description', 'editingRoomId']);
        
        // Auto-select area if filter is active
        if (session('admin_selected_area_id')) {
            $this->area_id = session('admin_selected_area_id');
        }

        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $room = Room::findOrFail($id);
        $this->editingRoomId = $id;
        
        $this->area_id = $room->area_id;
        $this->code = $room->code;
        $this->type = $room->type;
        $this->price_day = $room->price_day ? number_format($room->price_day, 0, '', '.') : '';
        $this->price_hour = $room->price_hour ? number_format($room->price_hour, 0, '', '.') : '';
        $this->status = $room->status;
        $this->description = $room->description;
        
        $this->showModal = true;
    }

    public function save()
    {
        // Sanitize currency inputs
        $this->price_day = str_replace(['.', ','], '', $this->price_day);
        $this->price_hour = str_replace(['.', ','], '', $this->price_hour);

        $this->validate();

        $data = [
            'area_id' => $this->area_id,
            'code' => $this->code,
            'type' => $this->type,
            'price_day' => $this->price_day,
            'price_hour' => $this->price_hour,
            'status' => $this->status,
            'description' => $this->description,
        ];

        if ($this->editingRoomId) {
            $room = Room::find($this->editingRoomId);
            $room->update($data);
            $message = 'Cập nhật phòng thành công.';
        } else {
            Room::create($data);
            $message = 'Thêm phòng mới thành công.';
        }

        $this->showModal = false;
        session()->flash('success', $message);
        $this->reset(['area_id', 'code', 'type', 'price_day', 'price_hour', 'status', 'description', 'editingRoomId']);
    }

    public function delete($id)
    {
        Room::find($id)->delete();
        session()->flash('success', 'Xóa phòng thành công.');
    }

    public function render()
    {
        $query = Room::with('area')->latest();

        if (session('admin_selected_area_id')) {
            $query->where('area_id', session('admin_selected_area_id'));
        }

        return view('livewire.admin.rooms.index', [
            'rooms' => $query->paginate(10),
            'areas' => Area::all(),
        ])->layout('components.layouts.admin');
    }
}
