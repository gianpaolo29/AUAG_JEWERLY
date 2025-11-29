<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Directly call the factory here
        User::factory()
            ->count(50)
            ->state([
                'role' => 'customer',   // set role field
            ])
            ->create();
    }
}
