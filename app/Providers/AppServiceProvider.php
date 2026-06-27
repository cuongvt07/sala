<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \Filament\Auth\Http\Responses\Contracts\LoginResponse::class,
            \App\Http\Responses\LoginResponse::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $settings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();

            // Mật khẩu SMTP được lưu mã hóa; giải mã an toàn (giá trị cũ dạng plaintext vẫn dùng được)
            $mailPassword = $settings['mail_password'] ?? null;
            if (!empty($mailPassword)) {
                try {
                    $mailPassword = \Illuminate\Support\Facades\Crypt::decryptString($mailPassword);
                } catch (\Throwable $e) {
                    // Giá trị cũ chưa mã hóa -> giữ nguyên
                }
            }

            config([
                'mail.mailers.smtp.host' => $settings['mail_host'] ?? config('mail.mailers.smtp.host'),
                'mail.mailers.smtp.port' => $settings['mail_port'] ?? config('mail.mailers.smtp.port'),
                'mail.mailers.smtp.username' => $settings['mail_username'] ?? config('mail.mailers.smtp.username'),
                'mail.mailers.smtp.password' => $mailPassword ?: config('mail.mailers.smtp.password'),
                'mail.mailers.smtp.encryption' => $settings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'),
                'mail.from.address' => $settings['mail_from_address'] ?? config('mail.from.address'),
                'mail.from.name' => $settings['mail_from_name'] ?? config('mail.from.name'),
                'app.name' => $settings['site_name'] ?? config('app.name'),
            ]);
        }
    }
}
