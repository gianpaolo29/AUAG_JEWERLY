<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer; // ✅ ADD THIS
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin1@gmail.com'],
            [
                'name' => 'Gian Mulingbayan',
                'password' => Hash::make('Computer_29'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );

        User::firstOrCreate(
            ['email' => 'neil.armstrong@gmail.com'],
            [
                'name' => 'Neil Armstrong',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );

        User::firstOrCreate(
            ['email' => 'gianpaolo.mulingbayan@example.com'],
            [
                'name' => 'Gian Paolo Mulingbayan',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]
        );

        Customer::factory()->count(100)->create();
    }
}
