<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 1; $i <= 20; $i++) {
            User::create([
                'name'     => $faker->name,
                'email'    => $faker->unique()->safeEmail(),
                'password' => Hash::make('password'), // default password
                'role'     => 'customer',
            ]);
        }
    }
}
