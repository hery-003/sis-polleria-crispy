<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->word(),
            'price' => fake()->randomFloat(2, 5, 100),
            'stock' => fake()->optional()->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}
