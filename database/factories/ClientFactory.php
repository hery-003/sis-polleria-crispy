<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'document_number' => fake()->unique()->numerify('########'),
            'address' => fake()->address(),
            'points' => fake()->numberBetween(0, 500),
            'is_active' => true,
        ];
    }
}
