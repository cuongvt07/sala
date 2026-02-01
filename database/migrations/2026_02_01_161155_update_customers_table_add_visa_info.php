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
            $table->string('visa_number')->nullable()->after('nationality');
            $table->date('visa_expiry')->nullable()->after('visa_number');
            $table->string('images')->nullable()->after('visa_expiry'); // JSON or path
            $table->text('notes')->nullable()->after('images');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['visa_number', 'visa_expiry', 'images', 'notes']);
        });
    }
};
