<?php

namespace Database\Factories;

use App\Models\MetodoPago;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MetodoPagoFactory extends Factory
{
    protected $model = MetodoPago::class;

    protected $table = 'metodos_pago';

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Efectivo', 'Tarjeta', 'Yape', 'Plin', 'Transferencia']);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'is_active' => true,
        ];
    }
}
