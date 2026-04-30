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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('birthday')->comment('male, female, other');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->json('additional_guests')->nullable()->after('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('gender');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('additional_guests');
        });
    }
};
