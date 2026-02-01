<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Use Vietnamese locale for realistic names and addresses
        $faker = \Faker\Factory::create('vi_VN');

        return [
            'name' => $faker->name(),
            'email' => $faker->unique()->safeEmail(),
            'phone' => $faker->phoneNumber(),
            'identity_id' => $faker->numerify('0##0##00####'), // Realistic CCCD format
            'birthday' => $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'nationality' => 'Vietnam',
        ];
    }
}
