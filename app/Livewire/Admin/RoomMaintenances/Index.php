<?php

namespace App\Livewire\Admin\RoomMaintenances;

use App\Models\RoomMaintenance;
use App\Models\Room;
use App\Models\Booking;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editingId = null;
    public $selectedAreaId = '';
    public $search = '';

    protected $listeners = ['area-selected' => 'handleAreaSelected'];

    public function handleAreaSelected()
    {
        $this->selectedAreaId = session('admin_selected_area_id', '');
        $this->selectedRoomId = null; // Reset khi đổi khu
    }

    public function mount()
    {
        $this->selectedAreaId = session('admin_selected_area_id', '');
    }

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

    public $selectedRoomId = null;

    public function selectRoom($roomId)
    {
        $this->selectedRoomId = $roomId;
        $this->resetPage();
    }

    public function clearRoom()
    {
        $this->selectedRoomId = null;
        $this->resetPage();
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['maintenance_date', 'task_name', 'description', 'cost', 'editingId']);
        
        if (!$this->selectedRoomId) {
            $this->reset('room_id');
        } else {
            $this->room_id = $this->selectedRoomId;
        }

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

        // Phân quyền toà: chỉ được lên lịch bảo dưỡng cho phòng thuộc toà mình quản lý
        $roomForArea = Room::find($this->room_id);
        if ($roomForArea && !auth()->user()->canAccessArea($roomForArea->area_id)) {
            $this->dispatch('toast', message: 'Bạn không có quyền thao tác bảo dưỡng cho toà nhà này.', type: 'error');
            return;
        }

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
            $message = 'Cập nhật lịch bảo dưỡng thành công!';
        } else {
            RoomMaintenance::create($data);
            $message = 'Thêm lịch bảo dưỡng thành công!';
        }

        $this->showModal = false;
        $this->dispatch('toast', message: $message, type: 'success');

        // Cảnh báo (không chặn) nếu phòng đang có khách vào đúng ngày bảo dưỡng
        $hasBooking = Booking::where('room_id', $this->room_id)
            ->whereIn('status', ['pending', 'checked_in'])
            ->whereDate('check_in', '<=', $this->maintenance_date)
            ->where(function ($q) {
                $q->whereDate('check_out', '>=', $this->maintenance_date)
                  ->orWhereNull('check_out');
            })
            ->exists();

        if ($hasBooking) {
            $this->dispatch('toast', message: 'Lưu ý: phòng đang có khách vào ngày bảo dưỡng này.', type: 'warning');
        }
    }

    public function delete($id)
    {
        $record = RoomMaintenance::with('room')->find($id);
        if (!$record) {
            $this->dispatch('toast', message: 'Không tìm thấy lịch bảo dưỡng.', type: 'error');
            return;
        }

        // Phân quyền toà: chặn xóa bảo dưỡng của toà khác
        if ($record->room && !auth()->user()->canAccessArea($record->room->area_id)) {
            $this->dispatch('toast', message: 'Bạn không có quyền xóa bảo dưỡng của toà nhà này.', type: 'error');
            return;
        }

        $record->delete();
        $this->dispatch('toast', message: 'Xóa lịch bảo dưỡng thành công.', type: 'success');
    }

    public function render()
    {
        $query = Room::with('area');

        // Nhân viên bị khóa toà: luôn giới hạn theo toà của họ
        $restrictedAreaId = (auth()->check() && auth()->user()->isAreaRestricted()) ? auth()->user()->area_id : null;

        if ($restrictedAreaId) {
            $query->where('area_id', $restrictedAreaId);
        } elseif ($this->selectedAreaId) {
            $query->where('area_id', $this->selectedAreaId);
        }

        if ($this->search) {
            $query->where('code', 'like', '%' . $this->search . '%');
        }

        $rooms = $query->withCount('roomMaintenances as maintenances_count')
            ->withSum('roomMaintenances as maintenances_sum_cost', 'cost')
            ->orderBy('code')
            ->get();

        $maintenances = [];
        if ($this->selectedRoomId) {
            $maintenances = RoomMaintenance::where('room_id', $this->selectedRoomId)
                ->latest('maintenance_date')
                ->paginate(10);
        }

        return view('livewire.admin.room-maintenances.index', [
            'rooms' => $rooms,
            'maintenances' => $maintenances,
            'selectedRoom' => $this->selectedRoomId ? Room::find($this->selectedRoomId) : null,
        ])->layout('components.layouts.admin');
    }
}
