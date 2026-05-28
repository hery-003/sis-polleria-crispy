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
            'opening_balance' => fake()->randomFloat(2, 100, 500),
            'status' => 'open',
        ];
    }

    public function closed(): static
    {
        return $this->state(fn(array $attr) => [
            'closed_at' => now(),
            'closing_balance' => fake()->randomFloat(2, 200, 1000),
            'status' => 'closed',
        ]);
    }
}
