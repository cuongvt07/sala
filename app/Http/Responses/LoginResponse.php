<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements LoginResponseContract
{
    /**
     * @return RedirectResponse | Redirector
     */
    public function toResponse($request): RedirectResponse | Redirector
    {
        return redirect()->to('/admin/dashboard');
    }
}
