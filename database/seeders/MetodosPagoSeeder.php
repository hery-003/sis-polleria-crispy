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
            ['name' => 'Transferencia Bancaria', 'slug' => 'transferencia', 'is_active' => true],
            ['name' => 'Mercado Pago', 'slug' => 'mercado-pago', 'is_active' => true],
            ['name' => 'Visa', 'slug' => 'visa', 'is_active' => true],
            ['name' => 'Mastercard', 'slug' => 'mastercard', 'is_active' => true],
            ['name' => 'American Express', 'slug' => 'amex', 'is_active' => true],
            ['name' => 'Diners Club', 'slug' => 'diners', 'is_active' => true],
            ['name' => 'Pago Efectivo', 'slug' => 'pago-efectivo', 'is_active' => true],
        ];

        foreach ($metodos as $metodo) {
            MetodoPago::updateOrCreate(
                ['slug' => $metodo['slug']],
                $metodo
            );
        }
    }
}
