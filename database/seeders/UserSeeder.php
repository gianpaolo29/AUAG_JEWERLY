<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::firstOrCreate(
            [
                'email' => 'admin1000@gmail.com',
            ],
            [
                'name' => 'Gian Mulingbayan',
                'password' => Hash::make('Computer_29'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );

        // Create Staff User
        User::firstOrCreate(
            [
                'email' => 'neil.armstrong@gmail.com.com',
            ],
            [
                'name' => 'Neil Armstrong',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );

        // Create Single Customer User
        User::firstOrCreate(
            [
                'email' => 'gianpaolo.mulingbayan@example.com',
            ],
            [
                'name' => 'Gian Paolo Mulingbayan',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );

        // 🔥 Create 100 random customer users
        User::factory()
            ->count(100)
            ->state([
                'role' => 'customer',
            ])
            ->create();
    }
}
