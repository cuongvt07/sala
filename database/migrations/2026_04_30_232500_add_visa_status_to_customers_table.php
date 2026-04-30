<?php
2: 
3: use Illuminate\Database\Migrations\Migration;
4: use Illuminate\Database\Schema\Blueprint;
5: use Illuminate\Support\Facades\Schema;
6: 
7: return new class extends Migration
8: {
9:     /**
10:      * Run the migrations.
11:      */
12:     public function up(): void
13:     {
14:         Schema::table('customers', function (Blueprint $table) {
15:             $table->tinyInteger('visa_status')->default(1)->after('visa_expiry')->comment('1: Sắp hết hạn, 2: Đã thông báo, 3: Đã gia hạn');
16:         });
17:     }
18: 
19:     /**
20:      * Reverse the migrations.
21:      */
22:     public function down(): void
23:     {
24:         Schema::table('customers', function (Blueprint $table) {
25:             $table->dropColumn('visa_status');
26:         });
27:     }
28: };
