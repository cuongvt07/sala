<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('check_out')->nullable()->change();
            $table->string('price_type')->default('day')->after('check_out'); // day, hour, month
            $table->decimal('unit_price', 12, 0)->default(0)->after('price_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('check_out')->nullable(false)->change();
            $table->dropColumn(['price_type', 'unit_price']);
        });
    }
};
