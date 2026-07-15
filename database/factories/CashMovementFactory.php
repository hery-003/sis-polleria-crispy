<?php

namespace Database\Factories;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashMovementFactory extends Factory
{
    protected $model = CashMovement::class;

    public function definition(): array
    {
        return [
            'cash_register_id' => CashRegister::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['in', 'out']),
            'amount' => fake()->randomFloat(2, 10, 500),
            'description' => fake()->sentence(),
        ];
    }
}
