<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index phục vụ các truy vấn hay dùng: lịch/phòng trống (status + ngày), danh sách phòng theo toà.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['status', 'check_in', 'check_out'], 'bookings_status_dates_idx');
            $table->index(['price_type', 'status'], 'bookings_type_status_idx');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->index(['area_id', 'code'], 'rooms_area_code_idx');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_status_dates_idx');
            $table->dropIndex('bookings_type_status_idx');
        });
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex('rooms_area_code_idx');
        });
    }
};
