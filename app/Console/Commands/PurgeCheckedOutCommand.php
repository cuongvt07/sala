<?php

namespace App\Console\Commands;

use App\Services\BookingPurger;
use Illuminate\Console\Command;

class PurgeCheckedOutCommand extends Command
{
    protected $signature = 'app:purge-checked-out {--grace=7 : Số ngày giữ khách tạo tay chưa có đặt phòng}';

    protected $description = 'Xoá booking đã trả phòng/huỷ và khách hàng không còn đặt phòng nào (giữ nguyên sổ thu chi).';

    public function handle()
    {
        $result = BookingPurger::sweep((int) $this->option('grace'));
        $this->info("Đã xoá {$result['bookings']} booking và {$result['customers']} khách hàng.");

        return self::SUCCESS;
    }
}
