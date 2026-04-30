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
15:             $table->string('gender')->nullable()->after('birthday')->comment('male, female, other');
16:         });
17: 
18:         Schema::table('bookings', function (Blueprint $table) {
19:             $table->json('additional_guests')->nullable()->after('customer_id');
20:         });
21:     }
22: 
23:     /**
24:      * Reverse the migrations.
25:      */
26:     public function down(): void
27:     {
28:         Schema::table('customers', function (Blueprint $table) {
29:             $table->dropColumn('gender');
30:         });
31: 
32:         Schema::table('bookings', function (Blueprint $table) {
33:             $table->dropColumn('additional_guests');
34:         });
35:     }
36: };
