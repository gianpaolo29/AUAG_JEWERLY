<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            User::create([
                'name'     => fake()->name(),
                'email'    => fake()->unique()->safeEmail(),
                'password' => Hash::make('password'), // default password
                'role'     => 'customer',
            ]);
        }
    }
}
