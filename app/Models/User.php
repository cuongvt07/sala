<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'area_id',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    public function area()
    {
        return $this->belongsTo(\App\Models\Area::class);
    }
    public function hasPermission($route)
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        return in_array($route, $this->permissions ?? []);
    }

    /** Quản trị cấp cao: xem/sửa được mọi toà nhà. */
    public function isAdminLevel(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /** Nhân viên bị khóa vào 1 toà nhà cụ thể. */
    public function isAreaRestricted(): bool
    {
        return !$this->isAdminLevel() && !empty($this->area_id);
    }

    /** Được phép thao tác dữ liệu thuộc toà $areaId hay không. */
    public function canAccessArea($areaId): bool
    {
        if ($this->isAdminLevel()) {
            return true;
        }
        // Nhân viên không gán toà -> giữ hành vi cũ (xem tất cả)
        if (!$this->isAreaRestricted()) {
            return true;
        }
        return (int) $this->area_id === (int) $areaId;
    }
}
