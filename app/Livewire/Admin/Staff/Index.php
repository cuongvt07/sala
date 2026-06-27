<?php

namespace App\Livewire\Admin\Staff;

use App\Models\User;
use App\Models\Area;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $userId;
    public $name, $email, $password, $role = 'staff', $area_id;
    public $permissions = [];

    protected $queryString = ['search'];

    public function render()
    {
        $users = User::where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->with('area')
            ->paginate(10);

        $areas = Area::all();
        
        $availableRoutes = [
            'admin.dashboard' => 'Dashboard',
            'admin.booking-calendar' => 'Lịch đặt phòng',
            'admin.bookings.index' => 'Quản lý đặt phòng',
            'admin.customers.index' => 'Quản lý khách hàng',
            'admin.services.index' => 'Dịch vụ',
            'admin.areas.index' => 'Tòa nhà',
            'admin.rooms.index' => 'Phòng',
            'admin.room-maintenances.index' => 'Bảo dưỡng phòng',
            'admin.staff.index' => 'Nhân sự',
            'admin.activity-logs.index' => 'Nhật ký hoạt động',
            'admin.settings.index' => 'Cài đặt hệ thống',
        ];

        return view('livewire.admin.staff.index', [
            'users' => $users,
            'areas' => $areas,
            'availableRoutes' => $availableRoutes,
        ])->layout('components.layouts.admin');
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->userId = $id;

        if ($id) {
            $user = User::findOrFail($id);
            $this->name = $user->name;
            $this->email = $user->email;
            $this->role = $user->role;
            $this->area_id = $user->area_id;
            $this->permissions = $user->permissions ?? [];
            $this->password = '';
        } else {
            $this->name = '';
            $this->email = '';
            $this->password = '';
            $this->role = 'staff';
            $this->area_id = null;
            $this->permissions = [];
        }

        $this->showModal = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->userId)],
            'role' => 'required|in:super_admin,admin,staff',
            'area_id' => 'nullable|exists:areas,id',
            'permissions' => 'array',
        ];

        if (!$this->userId) {
            $rules['password'] = 'required|min:8';
        } else {
            $rules['password'] = 'nullable|min:8';
        }

        $this->validate($rules);

        // Phân quyền: chỉ Super Admin được tạo/sửa tài khoản quyền cao (super_admin/admin),
        // tránh việc nhân viên tự nâng quyền cho mình hoặc người khác.
        $isSuperAdmin = auth()->user() && auth()->user()->role === 'super_admin';

        if (in_array($this->role, ['super_admin', 'admin']) && !$isSuperAdmin) {
            $this->dispatch('toast', message: 'Bạn không có quyền gán vai trò Quản trị. Chỉ Super Admin mới được phép.', type: 'error');
            return;
        }

        if ($this->userId && !$isSuperAdmin) {
            $target = User::find($this->userId);
            if ($target && in_array($target->role, ['super_admin', 'admin'])) {
                $this->dispatch('toast', message: 'Bạn không có quyền chỉnh sửa tài khoản Quản trị.', type: 'error');
                return;
            }
        }

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'area_id' => $this->area_id,
            'permissions' => $this->permissions,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->userId) {
            User::findOrFail($this->userId)->update($data);
            $this->dispatch('toast', message: 'Cập nhật nhân sự thành công!');
        } else {
            User::create($data);
            $this->dispatch('toast', message: 'Thêm nhân sự mới thành công!');
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        if ($id == auth()->id()) {
            $this->dispatch('toast', message: 'Bạn không thể tự xóa tài khoản của mình!', type: 'error');
            return;
        }

        $user = User::find($id);
        if (!$user) {
            $this->dispatch('toast', message: 'Không tìm thấy nhân sự.', type: 'error');
            return;
        }

        $isSuperAdmin = auth()->user() && auth()->user()->role === 'super_admin';

        // Chỉ Super Admin được xóa tài khoản quyền cao
        if (in_array($user->role, ['super_admin', 'admin']) && !$isSuperAdmin) {
            $this->dispatch('toast', message: 'Bạn không có quyền xóa tài khoản Quản trị.', type: 'error');
            return;
        }

        // Không cho xóa Super Admin cuối cùng để tránh mất toàn bộ quyền quản trị hệ thống
        if ($user->role === 'super_admin' && User::where('role', 'super_admin')->count() <= 1) {
            $this->dispatch('toast', message: 'Không thể xóa Super Admin cuối cùng của hệ thống.', type: 'error');
            return;
        }

        $user->delete();
        $this->dispatch('toast', message: 'Đã xóa nhân sự!');
    }
}
