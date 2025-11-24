<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::firstOrCreate([
            'name' => 'Ring',
        ]);

        Category::firstOrCreate([
            'name' => 'Bracelet',
        ]);

        Category::firstOrCreate([
            'name' => 'Necklace',
        ]);

        Category::firstOrCreate([
            'name' => 'Earring',
        ]);
    }
}
