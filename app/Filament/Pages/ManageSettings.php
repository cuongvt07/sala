<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

class ManageSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Systems';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $title = 'System Settings';
    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();
        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Settings')
                    ->tabs([
                        Tabs\Tab::make('Email Configuration')
                            ->icon('heroicon-o-envelope')
                            ->schema([
                                Section::make('SMTP Settings')
                                    ->description('Configure your SMTP server details for sending emails.')
                                    ->schema([
                                        TextInput::make('mail_host')
                                            ->label('SMTP Host')
                                            ->placeholder('smtp.gmail.com')
                                            ->required(),
                                        TextInput::make('mail_port')
                                            ->label('SMTP Port')
                                            ->numeric()
                                            ->placeholder('587')
                                            ->required(),
                                        TextInput::make('mail_username')
                                            ->label('SMTP Username')
                                            ->required(),
                                        TextInput::make('mail_password')
                                            ->label('SMTP Password')
                                            ->password()
                                            ->revealable()
                                            ->required(),
                                        Select::make('mail_encryption')
                                            ->label('Encryption')
                                            ->options([
                                                'tls' => 'TLS',
                                                'ssl' => 'SSL',
                                                '' => 'None',
                                            ])
                                            ->required(),
                                    ])->columns(2),

                                Section::make('Sender Information')
                                    ->schema([
                                        TextInput::make('mail_from_address')
                                            ->label('From Email Address')
                                            ->email()
                                            ->required(),
                                        TextInput::make('mail_from_name')
                                            ->label('From Name')
                                            ->required(),
                                    ])->columns(2),

                                Section::make('Email Content Structure')
                                    ->schema([
                                        TextInput::make('mail_subject_prefix')
                                            ->label('Default Subject Prefix')
                                            ->placeholder('[SALA] ')
                                            ->live(),
                                        TextInput::make('mail_footer_text')
                                            ->label('Email Footer Text')
                                            ->placeholder('© 2026 SALA. All rights reserved.')
                                            ->live(),
                                        
                                        Placeholder::make('preview')
                                            ->label('Email Visual Preview')
                                            ->content(function ($get) {
                                                $prefix = $get('mail_subject_prefix') ?? '[SALA] ';
                                                $name = $get('mail_from_name') ?? 'SALA System';
                                                $siteName = $get('site_name') ?? 'SALA';
                                                $footer = $get('mail_footer_text') ?? '© 2026 SALA. All rights reserved.';
                                                
                                                return new HtmlString("
                                                    <div class='mt-2 p-4 border rounded-xl bg-gray-50 dark:bg-gray-900 font-sans'>
                                                        <div class='flex items-center gap-3 mb-4'>
                                                            <div class='w-9 h-9 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-md ring-2 ring-white'>
                                                                " . substr($siteName, 0, 1) . "
                                                            </div>
                                                            <div>
                                                                <div class='text-sm font-semibold'>$name</div>
                                                                <div class='text-xs text-gray-500'>To: customer@example.com</div>
                                                            </div>
                                                        </div>
                                                        <div class='mb-2 text-sm font-bold'>$prefix New Booking Confirmation #12345</div>
                                                        <div class='text-sm text-gray-700 dark:text-gray-300 italic mb-4 p-3 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700'>
                                                            Hello! This is a sample email content to show how your configuration will look in a real mailbox.
                                                        </div>
                                                        <div class='text-xs text-center text-gray-400 mt-4 border-t pt-4'>
                                                            $footer
                                                        </div>
                                                    </div>
                                                ");
                                            }),
                                    ])->columns(1),
                            ]),
                        
                        Tabs\Tab::make('General Settings')
                            ->icon('heroicon-o-computer-desktop')
                            ->schema([
                                TextInput::make('site_name')
                                    ->label('Site Name')
                                    ->live(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Notification::make()
            ->title('Settings saved successfully!')
            ->success()
            ->send();
            
        // Optional: clear config cache if needed
        // Artisan::call('config:clear');
    }
}
