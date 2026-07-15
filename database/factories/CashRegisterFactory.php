<?php

namespace Database\Factories;

use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashRegisterFactory extends Factory
{
    protected $model = CashRegister::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'opened_at' => now(),
            'closed_at' => null,
            'opening_balance' => fake()->randomFloat(2, 100, 500),
            'closing_balance' => 0,
            'status' => 'open',
        ];
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'closed_at' => now()->addHours(8),
            'closing_balance' => fake()->randomFloat(2, 500, 2000),
            'status' => 'closed',
        ]);
    }
}
