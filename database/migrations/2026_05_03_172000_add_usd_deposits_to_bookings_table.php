<?php
/**
 * Migration created manually to avoid artisan command issues.
 */
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
            $table->decimal('deposit_usd', 12, 2)->default(0)->after('deposit');
            $table->decimal('deposit_2_usd', 12, 2)->default(0)->after('deposit_2');
            $table->decimal('usd_rate', 12, 2)->default(25400)->after('deposit_2_usd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['deposit_usd', 'deposit_2_usd', 'usd_rate']);
        });
    }
};
