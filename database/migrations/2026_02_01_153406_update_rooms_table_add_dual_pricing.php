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
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'price')) {
                $table->renameColumn('price', 'price_day');
            }
        });

        Schema::table('rooms', function (Blueprint $table) {
             if (!Schema::hasColumn('rooms', 'price_hour')) {
                $table->decimal('price_hour', 12, 0)->nullable()->after('price_day');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'price_day')) {
                $table->renameColumn('price_day', 'price');
            }
             if (Schema::hasColumn('rooms', 'price_hour')) {
                $table->dropColumn('price_hour');
            }
        });
    }
};
