<?php

use Illuminate\Support\Facades\Route;

// Alias route [login] → chuyển về Filament login. Phải khai báo ngoài group admin để KHÔNG bị prefix tên 'admin.'.
Route::get('/login', function () {
    return redirect('/filament/login');
})->name('login');

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/admin', '/admin/dashboard'); // Redirect /admin to new Dashboard

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
    Route::get('/booking-calendar', \App\Livewire\Admin\BookingCalendar::class)->name('booking-calendar');
    
    // Areas
    Route::get('/areas', \App\Livewire\Admin\Areas\Index::class)->name('areas.index');

    // Rooms
    Route::get('/rooms', \App\Livewire\Admin\Rooms\Index::class)->name('rooms.index');

    // Customers
    Route::get('/customers', \App\Livewire\Admin\Customers\Index::class)->name('customers.index');

    // Bookings
    Route::get('/bookings', \App\Livewire\Admin\Bookings\Index::class)->name('bookings.index');

    // Services
    Route::get('/services', \App\Livewire\Admin\Services\Index::class)->name('services.index');

    // Room Maintenances
    Route::get('/room-maintenances', \App\Livewire\Admin\RoomMaintenances\Index::class)->name('room-maintenances.index');

    // Settings
    Route::get('/settings', \App\Livewire\Admin\Settings\Index::class)->name('settings.index');
});
