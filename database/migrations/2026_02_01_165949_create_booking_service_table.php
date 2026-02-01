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
        Schema::create('booking_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            
            // For Meter services (Electricity, Water)
            $table->decimal('start_index', 10, 2)->nullable();
            $table->decimal('end_index', 10, 2)->nullable();
            $table->decimal('usage', 10, 2)->nullable(); // Calculated (End - Start)
            
            // For Fixed services (Wifi, Cleaning)
            $table->integer('quantity')->default(1);
            
            // Money
            $table->decimal('unit_price', 15, 0); // Snapshot price
            $table->decimal('total_amount', 15, 0); // Calculated total
            
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_service');
    }
};
