<?php

namespace Database\Factories;

use App\Models\Mesa;
use Illuminate\Database\Eloquent\Factories\Factory;

class MesaFactory extends Factory
{
    protected $model = Mesa::class;

    public function definition(): array
    {
        return [
            'number' => fake()->unique()->numberBetween(1, 50),
            'name' => 'Mesa ' . fake()->unique()->numberBetween(1, 50),
            'capacity' => fake()->randomElement([2, 4, 6, 8, 10]),
            'is_active' => true,
        ];
    }
}
