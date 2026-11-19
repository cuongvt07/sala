<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Dọn dữ liệu khách đã trả phòng còn sót (chạy nếu server có cron `php artisan schedule:run`)
Schedule::command('app:purge-checked-out')->dailyAt('03:00');
