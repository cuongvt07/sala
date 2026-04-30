<div class="p-6">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Quản lý nhân sự</h1>
            <p class="text-sm text-gray-500">Quản lý tài khoản và phân quyền truy cập hệ thống.</p>
        </div>
        <button wire:click="openModal()" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
            <x-icon name="heroicon-o-plus" class="h-5 w-5" />
            Thêm nhân sự
        </button>
    </div>

    <!-- Filters -->
    <div class="mb-6 rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-200">
        <div class="relative max-w-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <x-icon name="heroicon-o-magnifying-glass" class="h-5 w-5 text-gray-400" />
            </div>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Tìm kiếm tên hoặc email..." class="block w-full rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm">
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Họ tên</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Vai trò</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Khu vực</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="whitespace-nowrap px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <span class="font-medium text-gray-900">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                        <td class="whitespace-nowrap px-6 py-4">
                            <span @class([
                                'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium',
                                'bg-purple-100 text-purple-700' => $user->role === 'super_admin',
                                'bg-blue-100 text-blue-700' => $user->role === 'admin',
                                'bg-green-100 text-green-700' => $user->role === 'staff',
                            ])>
                                {{ $user->role === 'super_admin' ? 'Super Admin' : ($user->role === 'admin' ? 'Administrator' : 'Staff') }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                            {{ $user->area->name ?? 'Tất cả' }}
                        </td>
                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                            <button wire:click="openModal({{ $user->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Sửa</button>
                            <button wire:confirm="Bạn có chắc chắn muốn xóa nhân sự này?" wire:click="delete({{ $user->id }})" class="text-red-600 hover:text-red-900">Xóa</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal -->
    <div x-show="$wire.showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div x-show="$wire.showModal" x-transition.opacity class="absolute inset-0 bg-gray-500 bg-opacity-75" @click="$wire.showModal = false"></div>
        
        <div x-show="$wire.showModal" x-transition.scale class="relative w-full max-w-2xl overflow-hidden rounded-xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-bold text-gray-900">{{ $userId ? 'Cập nhật nhân sự' : 'Thêm nhân sự mới' }}</h3>
                <button @click="$wire.showModal = false" class="text-gray-400 hover:text-gray-500">
                    <x-icon name="heroicon-o-x-mark" class="h-6 w-6" />
                </button>
            </div>

            <form wire:submit="save" class="p-6">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Họ tên</label>
                        <input type="text" wire:model="name" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" wire:model="email" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Mật khẩu {{ $userId ? '(Để trống nếu không đổi)' : '' }}</label>
                        <input type="password" wire:model="password" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @error('password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Vai trò</label>
                        <select wire:model.live="role" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="super_admin">Super Admin</option>
                            <option value="admin">Administrator</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>

                    @if($role !== 'super_admin')
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Khu vực quản lý</label>
                        <select wire:model="area_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="">Tất cả khu vực</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}">{{ $area->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Permissions Selection -->
                    @if($role !== 'super_admin')
                    <div class="col-span-2 mt-4">
                        <label class="block text-sm font-bold text-gray-900 mb-2 underline">Phân quyền truy cập (Các trang được phép vào):</label>
                        <div class="grid grid-cols-2 gap-2 rounded-lg bg-gray-50 p-4 ring-1 ring-gray-200">
                            @foreach($availableRoutes as $route => $label)
                                <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:text-blue-600">
                                    <input type="checkbox" wire:model="permissions" value="{{ $route }}" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="col-span-2 mt-4 rounded-lg bg-purple-50 p-4 text-sm text-purple-700">
                        <x-icon name="heroicon-o-information-circle" class="inline h-5 w-5 mr-1" />
                        Vai trò <strong>Super Admin</strong> có toàn quyền truy cập tất cả các tính năng mà không cần kiểm tra phân quyền.
                    </div>
                    @endif
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" @click="$wire.showModal = false" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Hủy</button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500">Lưu thông tin</button>
                </div>
            </form>
        </div>
    </div>
</div>
