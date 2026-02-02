<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create User (Admin)
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@sala.vn'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );

        // 2. Create Areas (Vietnamese)
        $areasData = [
            [
                'name' => 'Khu Đô Thị Sala - Sarina',
                'address' => '10 Mai Chí Thọ, Thủ Thiêm, Quận 2, TP.HCM',
                'description' => 'Căn hộ cao cấp thấp tầng, không gian sống nghỉ dưỡng.',
            ],
            [
                'name' => 'Khu Đô Thị Sala - Sarica',
                'address' => '6 đường D9, Thủ Thiêm, Quận 2, TP.HCM',
                'description' => 'Vị trí đắc địa dọc công viên sông nước.',
            ],
            [
                'name' => 'Khu Đô Thị Sala - Sadora',
                'address' => 'Đường Nguyễn Cơ Thạch, Thủ Thiêm, Quận 2, TP.HCM',
                'description' => 'Tổ hợp căn hộ cao tầng với view nhìn trọn vẹn trung tâm thành phố.',
            ],
        ];

        $areas = [];
        foreach ($areasData as $data) {
            $areas[] = Area::create($data);
        }

        // 3. Create Rooms for each Area
        $roomTypes = ['Studio', '1PN', '2PN', '3PN', 'Penthouse'];

        // Status definition: reserved means booked but not checked in yet (or deposit paid)
        // occupied means currently checked in.
        // available means free.
        $statuses = ['available', 'occupied', 'maintenance', 'reserved'];

        foreach ($areas as $area) {
            // Generate 15-20 rooms per area
            $numRooms = rand(15, 20);

            // Generate prefix based on Area Name (e.g. Sarina -> SAR)
            // Naive extraction: take first 3 chars after " - "
            $parts = explode(' - ', $area->name);
            $prefix = isset($parts[1]) ? strtoupper(substr($parts[1], 0, 3)) : 'APT';

            for ($i = 1; $i <= $numRooms; $i++) {
                $type = $roomTypes[array_rand($roomTypes)];

                // Price logic based on type (USD assumed based on existing seeder, but let's make it VND for "Vietnamese" context?)
                // User said "tiếng việt", usually implies VND.
                // Studio: 15-20M, 1PN: 20-30M, 2PN: 30-45M, 3PN: 50-70M.
                // Let's us raw numbers.
                $basePrice = match ($type) {
                    'Studio' => rand(15000000, 20000000),
                    '1PN' => rand(20000000, 30000000),
                    '2PN' => rand(30000000, 45000000),
                    '3PN' => rand(50000000, 70000000),
                    'Penthouse' => rand(80000000, 120000000),
                };

                Room::create([
                    'area_id' => $area->id,
                    'code' => $prefix . '-' . str_pad($i, 3, '0', STR_PAD_LEFT), // e.g., SAR-001
                    'type' => $type,
                    'price_day' => $basePrice,
                    'status' => $statuses[array_rand($statuses)],
                    'description' => "Căn hộ $type đầy đủ nội thất, view đẹp, thoáng mát.",
                ]);
            }
        }

        // 4. Create Customers (Vietnamese via Factory)
        Customer::factory(50)->create();
        $customers = Customer::all();
        $rooms = Room::all();

        // 5. Create Bookings
        // Logic: Iterate through rooms to determine if they need active bookings based on their status
        // AND create some past/future bookings for history.

        foreach ($rooms as $room) {
            // Create some history (past bookings) for every room (0-3 past bookings)
            $pastBookingsCount = rand(0, 3);
            for ($k = 0; $k < $pastBookingsCount; $k++) {
                $customer = $customers->random();
                $stayDuration = rand(2, 14); // days
                $daysAgo = rand(10, 365);

                $checkIn = Carbon::now()->subDays($daysAgo);
                $checkOut = (clone $checkIn)->addDays($stayDuration);

                Booking::create([
                    'customer_id' => $customer->id,
                    'room_id' => $room->id,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'price' => round(($room->price_day / 30) * $stayDuration),
                    'deposit' => 0,
                    'status' => 'checked_out', // Completed
                    'notes' => 'Đã hoàn thành thuê.',
                ]);
            }

            // Current Active Booking logic based on Room Status
            if ($room->status === 'occupied') {
                // Create a current active booking (checked_in)
                $customer = $customers->random();
                Booking::create([
                    'customer_id' => $customer->id,
                    'room_id' => $room->id,
                    'check_in' => Carbon::now()->subDays(rand(1, 10)),
                    'check_out' => Carbon::now()->addDays(rand(2, 30)),
                    'price' => $room->price_day, // One month rent as placeholder
                    'deposit' => $room->price_day * 2, // 2 months deposit
                    'status' => 'checked_in',
                    'notes' => 'Đang thuê.',
                ]);
            } elseif ($room->status === 'reserved') {
                // Create a future/pending booking
                $customer = $customers->random();
                Booking::create([
                    'customer_id' => $customer->id,
                    'room_id' => $room->id,
                    'check_in' => Carbon::now()->addDays(rand(1, 5)),
                    'check_out' => Carbon::now()->addDays(rand(30, 60)),
                    'price' => $room->price_day,
                    'deposit' => round($room->price_day * 0.5), // 50% deposit
                    'status' => 'confirmed', // Confirmed but not checked in
                    'notes' => 'Đã đặt cọc giữ chỗ.',
                ]);
            }
        }

        // 6. Create Services
        $this->call(ServiceSeeder::class);
    }
}
