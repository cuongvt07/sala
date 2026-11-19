<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ResetDataCommand extends Command
{
    protected $signature = 'app:reset-data {--force : Bỏ qua câu hỏi xác nhận}';

    protected $description = 'Xoá toàn bộ dữ liệu khách hàng, đặt phòng, thu chi, nhật ký. GIỮ LẠI: Toà nhà, Phòng, Tài khoản, Dịch vụ, Cài đặt.';

    public function handle()
    {
        $this->warn('Lệnh này sẽ XOÁ: khách hàng, đặt phòng, dịch vụ đã chốt, sổ thu chi, bảo dưỡng phòng, nhật ký hoạt động.');
        $this->info('GIỮ LẠI: toà nhà, phòng, tài khoản đăng nhập, bảng giá dịch vụ, cài đặt.');

        if (!$this->option('force') && !$this->confirm('Bạn có chắc chắn? Hành động này KHÔNG THỂ hoàn tác!')) {
            $this->info('Đã huỷ.');
            return self::SUCCESS;
        }

        $tables = [
            'booking_usage_logs',
            'booking_service',
            'transactions',
            'bookings',
            'customers',
            'room_maintenances',
            'activity_logs',
        ];

        Schema::disableForeignKeyConstraints();
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                DB::table($table)->truncate();
                $this->line("- Đã xoá <info>{$count}</info> bản ghi bảng <comment>{$table}</comment>");
            }
        }
        Schema::enableForeignKeyConstraints();

        // Ảnh CCCD/hộ chiếu của khách
        try {
            Storage::disk('public')->deleteDirectory('customers');
            $this->line('- Đã xoá ảnh giấy tờ khách hàng');
        } catch (\Throwable $e) {
            $this->line('- Không có ảnh khách hàng để xoá');
        }

        $this->info('✔ Xong. Còn lại ' . DB::table('areas')->count() . ' toà nhà, ' . DB::table('rooms')->count() . ' phòng.');

        return self::SUCCESS;
    }
}
