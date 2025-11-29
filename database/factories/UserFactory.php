<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),                   // ✅ use $this->faker
            'email' => $this->faker->unique()->safeEmail(),   // ✅
            'email_verified_at' => now(),
            'password' => bcrypt('password'),                 // or Hash::make('password')
            'role' => 'customer',                             // default role for factory
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
