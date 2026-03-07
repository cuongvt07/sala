<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xóa toàn bộ dữ liệu hệ thống, chỉ giữ lại thông tin Tòa nhà (Areas).';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->confirm('BẠN CÓ CHẮC CHẮN muốn xóa toàn bộ dữ liệu (Account, Phòng, Đặt phòng, Khách hàng, Cài đặt...)? Hành động này không thể hoàn tác!')) {
            $this->info('Đã hủy lệnh reset data.');
            return;
        }

        $this->warn('Đang tiến hành reset dữ liệu...');

        $tables = [
            'booking_usage_logs',
            'room_maintenances',
            'bookings',
            'rooms',
            'customers',
            'services',
            'settings',
            'users',
        ];

        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        foreach ($tables as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                $count = \Illuminate\Support\Facades\DB::table($table)->count();
                \Illuminate\Support\Facades\DB::table($table)->truncate();
                $this->line("- Đã xóa <info>{$count}</info> bản ghi từ bảng <comment>{$table}</comment>");
            }
        }

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // Recreate default admin
        \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@sala.vn',
            'password' => bcrypt('password'),
        ]);

        $this->info('- Đã tạo lại tài khoản mặc định: <comment>admin@sala.vn</comment> / <comment>password</comment>');

        $this->success('Đã reset dữ liệu thành công! Chỉ còn lại Tòa nhà (Areas) và tài khoản Admin mặc định.');
    }

    private function success($message)
    {
        $this->output->writeln("<info>✔</info> {$message}");
    }
}
