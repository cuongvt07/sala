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
        Schema::create('booking_usage_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $blueprint->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $blueprint->string('type'); // meter, fixed, manual, room_base
            $blueprint->string('billing_unit')->nullable(); // day, month, quantity
            $blueprint->decimal('start_index', 15, 2)->nullable();
            $blueprint->decimal('end_index', 15, 2)->nullable();
            $blueprint->decimal('quantity', 15, 2)->nullable();
            $blueprint->decimal('unit_price', 15, 2);
            $blueprint->decimal('total_amount', 15, 2);
            $blueprint->date('billing_date')->nullable();
            $blueprint->text('notes')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_usage_logs');
    }
};
