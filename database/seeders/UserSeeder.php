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
        User::create([
            'name' => 'Gian Mulingbayan',
            'email' => 'admin1000@gmail.com',
            'password' => Hash::make('Computer_29'),
            'role' => 'admin',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        // Create Staff User
        User::create([
            'name' => 'Neil Armstrong',
            'email' => 'neil.armstrong@gmail.com.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        // Create Customer User
        User::create([
            'name' => 'Gian Paolo Mulingbayan',
            'email' => 'gianpaolo.mulingbayan@example.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

    }
}
