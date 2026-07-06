<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm index tăng tốc các truy vấn nóng:
 * - Chống trùng phòng / lịch đặt phòng: bookings(room_id, status, check_in, check_out)
 * - Sắp xếp & lọc theo trạng thái
 * - Dashboard visa sắp hết hạn: customers(visa_expiry)
 * - Lọc quốc tịch: customers(nationality)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['room_id', 'status'], 'bookings_room_status_idx');
            $table->index('check_in', 'bookings_check_in_idx');
            $table->index('check_out', 'bookings_check_out_idx');
            $table->index('status', 'bookings_status_idx');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('visa_expiry', 'customers_visa_expiry_idx');
            $table->index('nationality', 'customers_nationality_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_room_status_idx');
            $table->dropIndex('bookings_check_in_idx');
            $table->dropIndex('bookings_check_out_idx');
            $table->dropIndex('bookings_status_idx');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_visa_expiry_idx');
            $table->dropIndex('customers_nationality_idx');
        });
    }
};
