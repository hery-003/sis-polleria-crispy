<?php

namespace Database\Seeders;

use App\Models\MetodoPago;
use Illuminate\Database\Seeder;

class MetodosPagoSeeder extends Seeder
{
    public function run(): void
    {
        $metodos = [
            ['name' => 'Efectivo', 'slug' => 'cash', 'is_active' => true],
            ['name' => 'Tarjeta', 'slug' => 'card', 'is_active' => true],
            ['name' => 'Yape/Plin', 'slug' => 'yape', 'is_active' => true],
        ];

        foreach ($metodos as $metodo) {
            MetodoPago::updateOrCreate(
                ['slug' => $metodo['slug']],
                $metodo
            );
        }
    }
}
