<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sổ thu chi: mỗi khoản tiền đã xác nhận (thu hoặc chi) là 1 dòng.
 * Lưu kèm "ảnh chụp" phòng/khách dạng text để khi booking/khách bị xoá (sau check-out)
 * thì sổ thu chi vẫn còn nguyên, không phụ thuộc dữ liệu khách.
 *
 * Không dùng khoá ngoại (FK) cho các cột liên kết: một số host MySQL lệch engine/kiểu cột
 * khiến tạo FK lỗi (errno 150). Việc gỡ liên kết khi xoá được xử lý trong code.
 * Migration viết idempotent để chạy lại an toàn nếu lần trước dừng giữa chừng.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->id();
                $table->string('type', 10);                    // income | expense
                $table->string('category', 50);                // Tiền phòng, Tiền cọc, Dịch vụ, Hoàn cọc, Bảo trì, Khác...
                $table->decimal('amount', 15, 0);
                $table->string('method', 20)->default('cash'); // cash | transfer
                $table->date('txn_date');
                $table->string('description')->nullable();
                $table->unsignedBigInteger('area_id')->nullable()->index();
                $table->string('room_code', 50)->nullable();
                $table->string('customer_name')->nullable();
                $table->unsignedBigInteger('booking_id')->nullable();
                $table->date('period_due')->nullable();        // Kỳ hợp đồng (ngày đến hạn) mà khoản thu này thanh toán
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();

                $table->index(['txn_date', 'type'], 'transactions_date_type_idx');
                $table->index(['booking_id', 'period_due'], 'transactions_booking_period_idx');
            });
        }

        // Đánh dấu dịch vụ (điện/nước/phụ thu) đã được thu trong khoản thu nào
        if (!Schema::hasColumn('booking_usage_logs', 'transaction_id')) {
            Schema::table('booking_usage_logs', function (Blueprint $table) {
                $table->unsignedBigInteger('transaction_id')->nullable()->after('booking_id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('booking_usage_logs', 'transaction_id')) {
            Schema::table('booking_usage_logs', function (Blueprint $table) {
                $table->dropIndex(['transaction_id']);
                $table->dropColumn('transaction_id');
            });
        }
        Schema::dropIfExists('transactions');
    }
};
