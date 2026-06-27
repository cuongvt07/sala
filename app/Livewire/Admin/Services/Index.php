<?php

namespace App\Livewire\Admin\Services;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Service;

class Index extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editingServiceId = null;

    // Form inputs
    public $name;
    public $type = 'fixed'; // meter, fixed
    public $unit_price = 0;
    public $unit_name;
    public $description;
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:meter,fixed',
        'unit_price' => 'required|numeric|min:0',
        'unit_name' => 'nullable|string|max:255',
        'description' => 'nullable|string',
        'is_active' => 'boolean',
    ];

    public function create()
    {
        $this->resetValidation();
        $this->reset(['name', 'type', 'unit_price', 'unit_name', 'description', 'is_active', 'editingServiceId']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $service = Service::findOrFail($id);
        $this->editingServiceId = $id;

        $this->name = $service->name;
        $this->type = $service->type;
        $this->unit_price = number_format($service->unit_price, 0, ',', '.');
        $this->unit_name = $service->unit_name;
        $this->description = $service->description;
        $this->is_active = $service->is_active;

        $this->showModal = true;
    }

    public function save()
    {
        $this->unit_price = str_replace('.', '', $this->unit_price);
        $this->validate();

        $data = [
            'name' => $this->name,
            'type' => $this->type,
            'unit_price' => $this->unit_price,
            'unit_name' => $this->unit_name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->editingServiceId) {
            Service::find($this->editingServiceId)->update($data);
            $message = 'Cập nhật dịch vụ thành công.';
        } else {
            Service::create($data);
            $message = 'Thêm dịch vụ mới thành công.';
        }

        $this->showModal = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function delete($id)
    {
        $service = Service::find($id);
        if (!$service) {
            $this->dispatch('toast', message: 'Không tìm thấy dịch vụ.', type: 'error');
            return;
        }

        // Chặn xóa khi dịch vụ đang được dùng ở booking để tránh mất dữ liệu hóa đơn/pivot.
        // Khuyến nghị ngưng kích hoạt (is_active = false) thay vì xóa.
        if ($service->bookings()->exists() || $service->usageLogs()->exists()) {
            $this->dispatch('toast', message: 'Không thể xóa: dịch vụ đang được sử dụng trong booking. Hãy chuyển sang "Ngừng kích hoạt".', type: 'error');
            return;
        }

        $service->delete();
        $this->dispatch('toast', message: 'Xóa dịch vụ thành công.', type: 'success');
    }

    public $search = '';

    public function render()
    {
        $services = Service::query()
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('unit_name', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.services.index', [
            'services' => $services,
        ])->layout('components.layouts.admin');
    }
}
