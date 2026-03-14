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
    public $price_day;
    public $price_hour;
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
        $this->price_day = $roomModel->price_day ? number_format($roomModel->price_day, 0, '', '.') : '';
        $this->price_hour = $roomModel->price_hour ? number_format($roomModel->price_hour, 0, '', '.') : '';
        $this->status = $roomModel->status;
    }

    public function rules()
    {
        return [
            'area_id' => 'required|exists:areas,id',
            'code' => ['required', 'string', 'max:255', Rule::unique('rooms', 'code')->ignore($this->roomId)],
            'type' => 'required|string',
            'price_day' => 'required|numeric|min:0',
            'price_hour' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,maintenance',
            'description' => 'nullable|string',
        ];
    }

    public function save()
    {
        $this->price_day = str_replace(['.', ','], '', $this->price_day);
        $this->price_hour = str_replace(['.', ','], '', $this->price_hour);
        
        // Convert empty strings to null for decimal columns
        $this->price_day = $this->price_day === '' ? null : $this->price_day;
        $this->price_hour = $this->price_hour === '' ? null : $this->price_hour;
        
        $this->validate();

        $room = Room::find($this->roomId);
        $room->update([
            'area_id' => $this->area_id,
            'code' => $this->code,
            'type' => $this->type,
            'price_day' => $this->price_day,
            'price_hour' => $this->price_hour,
            'status' => $this->status,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Cập nhật phòng thành công.');

        return redirect()->route('admin.rooms.index');
    }

    public function render()
    {
        $maintenances = Room::find($this->roomId)->roomMaintenances()
            ->orderBy('maintenance_date', 'desc')
            ->get()
            ->groupBy(function($item) {
                $today = now()->startOfDay();
                $mDate = $item->maintenance_date->startOfDay();
                
                if ($mDate->lt($today)) return 'old';
                if ($mDate->eq($today)) return 'current';
                return 'new';
            });

        return view('livewire.admin.rooms.edit', [
            'areas' => Area::all(),
            'maintenances' => $maintenances,
        ])->layout('components.layouts.admin');
    }
}
