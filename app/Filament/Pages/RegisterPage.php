<?php

namespace App\Filament\Pages;

use Filament\Pages\Auth\Register as BaseRegister;

class RegisterPage extends BaseRegister
{
    protected function getRedirectUrl(): string
    {
        // Sau khi đăng ký thành công chuyển về dashboard hoặc login
        return '/admin/dashboard';
    }
}
