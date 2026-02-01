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
        session()->flash('success', $message);
    }

    public function delete($id)
    {
        Service::find($id)->delete();
        session()->flash('success', 'Xóa dịch vụ thành công.');
    }

    public function render()
    {
        return view('livewire.admin.services.index', [
            'services' => Service::latest()->paginate(10),
        ])->layout('components.layouts.admin');
    }
}
