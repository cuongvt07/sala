<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Livewire\Component;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;

class Index extends Component
{
    public $settings = [];

    protected $rules = [
        'settings.mail_host' => 'required',
        'settings.mail_port' => 'required|numeric',
        'settings.mail_username' => 'required',
        'settings.mail_password' => 'required',
        'settings.mail_encryption' => 'required',
        'settings.mail_from_address' => 'required|email',
        'settings.mail_from_name' => 'required',
        'settings.mail_subject_prefix' => 'nullable',
        'settings.mail_footer_text' => 'nullable',
        'settings.site_name' => 'nullable',
    ];

    public function mount()
    {
        $dbSettings = Setting::all()->pluck('value', 'key')->toArray();

        // Giải mã mật khẩu SMTP đã lưu (giá trị cũ dạng plaintext vẫn hiển thị được)
        $mailPassword = $dbSettings['mail_password'] ?? config('mail.mailers.smtp.password');
        if (!empty($dbSettings['mail_password'])) {
            try {
                $mailPassword = Crypt::decryptString($dbSettings['mail_password']);
            } catch (\Throwable $e) {
                $mailPassword = $dbSettings['mail_password'];
            }
        }

        $this->settings = [
            'mail_host' => $dbSettings['mail_host'] ?? config('mail.mailers.smtp.host'),
            'mail_port' => $dbSettings['mail_port'] ?? config('mail.mailers.smtp.port'),
            'mail_username' => $dbSettings['mail_username'] ?? config('mail.mailers.smtp.username'),
            'mail_password' => $mailPassword,
            'mail_encryption' => $dbSettings['mail_encryption'] ?? config('mail.mailers.smtp.encryption'),
            'mail_from_address' => $dbSettings['mail_from_address'] ?? config('mail.from.address'),
            'mail_from_name' => $dbSettings['mail_from_name'] ?? config('mail.from.name'),
            'mail_subject_prefix' => $dbSettings['mail_subject_prefix'] ?? '[SALA] ',
            'mail_footer_text' => $dbSettings['mail_footer_text'] ?? '© 2026 SALA. All rights reserved.',
            'site_name' => $dbSettings['site_name'] ?? config('app.name'),
        ];
    }

    public function save()
    {
        $this->validate();

        foreach ($this->settings as $key => $value) {
            // Mã hóa mật khẩu SMTP trước khi lưu để không lộ credential dạng plaintext trong DB
            if ($key === 'mail_password' && !empty($value)) {
                $value = Crypt::encryptString($value);
            }
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->dispatch('toast', message: 'Cấu hình đã được lưu thành công!', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.settings.index')
            ->layout('components.layouts.admin', ['title' => 'Cài đặt hệ thống']);
    }
}
