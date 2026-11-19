<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sổ thu chi: mỗi khoản tiền đã xác nhận (thu hoặc chi) là 1 dòng.
 * Lưu kèm "ảnh chụp" phòng/khách dạng text để khi booking/khách bị xoá (sau check-out)
 * thì sổ thu chi vẫn còn nguyên, không phụ thuộc dữ liệu khách.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type', 10);                 // income | expense
            $table->string('category', 50);             // Tiền phòng, Tiền cọc, Dịch vụ, Hoàn cọc, Bảo trì, Khác...
            $table->decimal('amount', 15, 0);
            $table->string('method', 20)->default('cash'); // cash | transfer
            $table->date('txn_date');
            $table->string('description')->nullable();
            $table->foreignId('area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('room_code', 50)->nullable();
            $table->string('customer_name')->nullable();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->date('period_due')->nullable();     // Kỳ hợp đồng (ngày đến hạn) mà khoản thu này thanh toán
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['txn_date', 'type'], 'transactions_date_type_idx');
            $table->index(['booking_id', 'period_due'], 'transactions_booking_period_idx');
        });

        // Đánh dấu dịch vụ (điện/nước/phụ thu) đã được thu trong khoản thu nào
        Schema::table('booking_usage_logs', function (Blueprint $table) {
            $table->foreignId('transaction_id')->nullable()->after('booking_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('booking_usage_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('transaction_id');
        });
        Schema::dropIfExists('transactions');
    }
};
