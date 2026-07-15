<?php

namespace Database\Factories;

use App\Models\Mesa;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => 'ORD-'.now()->format('ymd').'-'.strtoupper(substr(uniqid('', true), -8)),
            'total_amount' => fake()->randomFloat(2, 10, 200),
            'status' => fake()->randomElement(['pending', 'cooking', 'ready', 'completed', 'cancelled']),
            'payment_status' => fake()->randomElement(['pending', 'paid', 'refunded']),
            'payment_method' => fake()->randomElement(['cash', 'card', 'yape', 'plin']),
            'type' => fake()->randomElement(['dine_in', 'take_out', 'delivery']),
            'mesa_id' => null,
            'metodo_pago_id' => null,
            'client_id' => null,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function dineIn(): static
    {
        return $this->state(fn (array $attr) => [
            'type' => 'dine_in',
            'mesa_id' => Mesa::factory(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attr) => [
            'payment_status' => 'paid',
            'status' => 'completed',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attr) => [
            'status' => 'cancelled',
            'cancellation_reason' => fake()->sentence(),
        ]);
    }
}
