<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm giá thuê theo hợp đồng/tháng, tách khỏi giá ngày (price_day).
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'price_month')) {
                $table->decimal('price_month', 12, 0)->nullable()->after('price_hour');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'price_month')) {
                $table->dropColumn('price_month');
            }
        });
    }
};
