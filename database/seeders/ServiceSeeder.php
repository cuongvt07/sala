<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $services = [
            [
                'name' => 'Điện',
                'type' => 'meter',
                'unit_price' => 4500,
                'unit_name' => 'kWh (Số điện)',
                'is_active' => true,
            ],
            [
                'name' => 'Nước',
                'type' => 'meter',
                'unit_price' => 30000,
                'unit_name' => 'm3 (Khối)',
                'is_active' => true,
            ],
            [
                'name' => 'Wifi / Internet',
                'type' => 'fixed',
                'unit_price' => 100000,
                'unit_name' => 'Tháng',
                'is_active' => true,
            ],
            [
                'name' => 'Vệ sinh',
                'type' => 'fixed',
                'unit_price' => 50000,
                'unit_name' => 'Lần',
                'is_active' => true,
            ],
             [
                'name' => 'Rác',
                'type' => 'fixed',
                'unit_price' => 30000,
                'unit_name' => 'Tháng',
                'is_active' => true,
            ],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(['name' => $service['name']], $service);
        }
    }
}
