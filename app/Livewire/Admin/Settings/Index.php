<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Livewire\Component;
use Illuminate\Support\Facades\Artisan;

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
        $this->settings = [
            'mail_host' => $dbSettings['mail_host'] ?? config('mail.mailers.smtp.host'),
            'mail_port' => $dbSettings['mail_port'] ?? config('mail.mailers.smtp.port'),
            'mail_username' => $dbSettings['mail_username'] ?? config('mail.mailers.smtp.username'),
            'mail_password' => $dbSettings['mail_password'] ?? config('mail.mailers.smtp.password'),
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
