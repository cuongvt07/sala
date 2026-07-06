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

    // Filters
    public $search = '';
    public $filterType = '';
    public $filterStatus = '';
    public $filterArea = '';

    // Form inputs
    public $area_id;
    public $code;
    public $type = 'Studio';
    public $price_day;
    public $price_hour;
    public $price_month;
    public $status = 'active';
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
            'price_month' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,maintenance',
            'description' => 'nullable|string',
        ];
    }

    public function create()
    {
        $this->resetValidation();
        $this->reset(['area_id', 'code', 'type', 'price_day', 'price_hour', 'price_month', 'status', 'description', 'editingRoomId']);

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

        // Phân quyền toà: chặn sửa phòng của toà khác
        if (!auth()->user()->canAccessArea($room->area_id)) {
            $this->dispatch('toast', message: 'Bạn không có quyền sửa phòng của toà nhà này.', type: 'error');
            return;
        }

        $this->editingRoomId = $id;

        $this->area_id = $room->area_id;
        $this->code = $room->code;
        $this->type = $room->type;
        $this->price_day = $room->price_day ? number_format($room->price_day, 0, '', '.') : '';
        $this->price_hour = $room->price_hour ? number_format($room->price_hour, 0, '', '.') : '';
        $this->price_month = $room->price_month ? number_format($room->price_month, 0, '', '.') : '';
        $this->status = $room->status;
        $this->description = $room->description;
        
        $this->showModal = true;
    }

    public function save()
    {
        // Sanitize currency inputs
        $this->price_day = str_replace(['.', ','], '', $this->price_day);
        $this->price_hour = str_replace(['.', ','], '', $this->price_hour);
        $this->price_month = str_replace(['.', ','], '', $this->price_month);

        // Convert empty strings to null for decimal columns
        $this->price_day = $this->price_day === '' ? null : $this->price_day;
        $this->price_hour = $this->price_hour === '' ? null : $this->price_hour;
        $this->price_month = $this->price_month === '' ? null : $this->price_month;

        $this->validate();

        // Phân quyền toà: chỉ được tạo/sửa phòng thuộc toà mình quản lý
        if (!auth()->user()->canAccessArea($this->area_id)) {
            $this->dispatch('toast', message: 'Bạn không có quyền tạo/sửa phòng cho toà nhà này.', type: 'error');
            return;
        }

        $data = [
            'area_id' => $this->area_id,
            'code' => $this->code,
            'type' => $this->type,
            'price_day' => $this->price_day,
            'price_hour' => $this->price_hour,
            'price_month' => $this->price_month,
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
        $this->dispatch('toast', message: $message, type: 'success');
        $this->reset(['area_id', 'code', 'type', 'price_day', 'price_hour', 'price_month', 'status', 'description', 'editingRoomId']);
    }

    public function delete($id)
    {
        $room = Room::find($id);
        if (!$room) {
            $this->dispatch('toast', message: 'Không tìm thấy phòng.', type: 'error');
            return;
        }

        // Phân quyền toà: chặn xóa phòng của toà khác
        if (!auth()->user()->canAccessArea($room->area_id)) {
            $this->dispatch('toast', message: 'Bạn không có quyền xóa phòng của toà nhà này.', type: 'error');
            return;
        }

        // Chặn xóa khi phòng còn booking để tránh cascade xóa luôn toàn bộ lịch đặt phòng
        if ($room->bookings()->exists()) {
            $this->dispatch('toast', message: 'Không thể xóa: phòng còn lịch đặt. Vui lòng xử lý các booking trước.', type: 'error');
            return;
        }

        $room->delete();
        $this->dispatch('toast', message: 'Xóa phòng thành công.', type: 'success');
    }

    public function render()
    {
        $query = Room::with('area')->latest();

        if ($this->search) {
            $query->where('code', 'like', '%' . $this->search . '%');
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // Nhân viên bị khóa toà: luôn giới hạn theo toà của họ, bỏ qua bộ lọc thủ công
        $restrictedAreaId = (auth()->check() && auth()->user()->isAreaRestricted()) ? auth()->user()->area_id : null;

        if ($restrictedAreaId) {
            $query->where('area_id', $restrictedAreaId);
        } elseif ($this->filterArea) {
            $query->where('area_id', $this->filterArea);
        } elseif (session('admin_selected_area_id')) {
            $query->where('area_id', session('admin_selected_area_id'));
        }

        $maintenances = [];
        if ($this->editingRoomId) {
            $maintenances = Room::find($this->editingRoomId)->roomMaintenances()
                ->orderBy('maintenance_date', 'desc')
                ->get()
                ->groupBy(function($item) {
                    $today = now()->startOfDay();
                    $mDate = $item->maintenance_date->startOfDay();
                    
                    if ($mDate->lt($today)) return 'old';
                    if ($mDate->eq($today)) return 'current';
                    return 'new';
                });
        }

        return view('livewire.admin.rooms.index', [
            'rooms' => $query->paginate(10),
            'areas' => Area::all(),
            'maintenances' => $maintenances,
        ])->layout('components.layouts.admin');
    }
}
