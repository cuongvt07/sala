<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/admin', '/admin/dashboard'); // Redirect /admin to new Dashboard

Route::prefix('admin')->name('admin.')->group(function () {
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
});
