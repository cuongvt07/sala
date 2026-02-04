<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all bookings with 'confirmed' status to 'pending'
        DB::table('bookings')
            ->where('status', 'confirmed')
            ->update(['status' => 'pending']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally revert pending back to confirmed if needed
        // Not implementing as we don't track which were originally confirmed
    }
};
