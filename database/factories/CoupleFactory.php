<?php

namespace Database\Factories;

use App\Models\Couple;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Couple>
 */
class CoupleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name().' & '.fake()->name(),
            'connected_at' => now(),
        ];
    }
}
