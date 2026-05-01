<?php

/**
 * Script cập nhật nhanh cờ is_contract cho các booking cũ
 * Cách dùng: php update_contract_flag.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Booking;

echo "--- Đang bắt đầu cập nhật dữ liệu ---\n";

try {
    // Cập nhật tất cả booking có price_type là 'month' thành is_contract = 1
    // Theo logic mới, 'month' thường đi kèm với Hợp đồng
    $affected = Booking::where('price_type', 'month')->update(['is_contract' => 1]);
    
    echo "Thành công: Đã cập nhật $affected booking sang trạng thái Hợp đồng (is_contract = 1).\n";
} catch (\Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo "Lưu ý: Hãy đảm bảo bạn đã chạy 'php artisan migrate' thành công trước khi chạy file này.\n";
}

echo "--- Hoàn tất ---\n";
