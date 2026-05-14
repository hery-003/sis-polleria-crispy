<?php

namespace Database\Seeders;

use App\Models\Mesa;
use Illuminate\Database\Seeder;

class MesaSeeder extends Seeder
{
    public function run(): void
    {
        $mesas = [
            ['number' => 1, 'name' => 'Mesa 1', 'capacity' => 4, 'is_active' => true],
            ['number' => 2, 'name' => 'Mesa 2', 'capacity' => 4, 'is_active' => true],
            ['number' => 3, 'name' => 'Mesa 3', 'capacity' => 6, 'is_active' => true],
            ['number' => 4, 'name' => 'Mesa 4', 'capacity' => 6, 'is_active' => true],
            ['number' => 5, 'name' => 'Mesa 5', 'capacity' => 8, 'is_active' => true],
            ['number' => 6, 'name' => 'Mesa 6', 'capacity' => 2, 'is_active' => true],
            ['number' => 7, 'name' => 'Mesa 7', 'capacity' => 4, 'is_active' => true],
            ['number' => 8, 'name' => 'Mesa 8', 'capacity' => 10, 'is_active' => true],
        ];

        foreach ($mesas as $mesa) {
            Mesa::updateOrCreate(
                ['number' => $mesa['number']],
                $mesa
            );
        }
    }
}
