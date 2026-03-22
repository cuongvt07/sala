<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DefaultAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        \App\Models\User::firstOrCreate(
            ['email' => 'salaapartment@admin.com'],
            [
                'name' => 'Sala Apartment',
                'password' => bcrypt('12345678'),
            ]
        );
    }
}
