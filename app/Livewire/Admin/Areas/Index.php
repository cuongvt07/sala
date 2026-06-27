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
        $this->dispatch('toast', message: $message, type: 'success');
        $this->reset(['name', 'address', 'description', 'editingAreaId']);
    }

    public function delete($id)
    {
        $area = Area::withCount('rooms')->find($id);
        if (!$area) {
            $this->dispatch('toast', message: 'Không tìm thấy khu vực.', type: 'error');
            return;
        }

        // Chặn xóa khi khu vực còn phòng để tránh cascade xóa luôn phòng + booking + bảo dưỡng
        if ($area->rooms_count > 0) {
            $this->dispatch('toast', message: 'Không thể xóa: khu vực còn ' . $area->rooms_count . ' phòng. Vui lòng xóa/chuyển các phòng trước.', type: 'error');
            return;
        }

        $area->delete();
        $this->dispatch('toast', message: 'Xóa khu vực thành công.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.areas.index', [
            'areas' => Area::withCount('rooms')->latest()->paginate(10),
        ])->layout('components.layouts.admin');
    }
}
