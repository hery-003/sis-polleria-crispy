<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Nuevos productos para Entradas
        $catEntradas = Category::where('slug', 'entradas')->first();
        if ($catEntradas) {
            $tequenos = Product::updateOrCreate(
                ['name' => 'Tequeños'],
                [
                    'category_id' => $catEntradas->id,
                    'slug' => Str::slug('Tequeños'),
                    'description' => 'Tequeños de queso con salsa de la casa.',
                    'is_active' => true,
                ]
            );
            ProductVariant::updateOrCreate(
                ['product_id' => $tequenos->id, 'name' => '6 unidades'],
                ['price' => 12.00, 'stock' => null]
            );
            ProductVariant::updateOrCreate(
                ['product_id' => $tequenos->id, 'name' => '12 unidades'],
                ['price' => 22.00, 'stock' => null]
            );
        }

        $salchipapas = Product::updateOrCreate(
            ['name' => 'Salchipapas'],
            [
                'category_id' => $catEntradas->id,
                'slug' => Str::slug('Salchipapas'),
                'description' => 'Papas fritas con salchicha y salsas.',
                'is_active' => true,
            ]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $salchipapas->id, 'name' => 'Personal'],
            ['price' => 14.00, 'stock' => null]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $salchipapas->id, 'name' => 'Grande'],
            ['price' => 22.00, 'stock' => null]
        );

        // Nuevos productos para Postres
        $catPostres = Category::where('slug', 'postres')->first();
        if ($catPostres) {
            $picarones = Product::updateOrCreate(
                ['name' => 'Picarones'],
                [
                    'category_id' => $catPostres->id,
                    'slug' => Str::slug('Picarones'),
                    'description' => 'Picarones con miel de la casa.',
                    'is_active' => true,
                ]
            );
            ProductVariant::updateOrCreate(
                ['product_id' => $picarones->id, 'name' => 'Porción (4 und)'],
                ['price' => 10.00, 'stock' => null]
            );
        }

        // Nuevos productos para Bebidas
        $catBebida = Category::where('slug', 'bebidas')->first();

        $cocacola = Product::updateOrCreate(
            ['name' => 'Coca Cola'],
            [
                'category_id' => $catBebida->id,
                'slug' => Str::slug('Coca Cola'),
                'is_active' => true,
            ]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $cocacola->id, 'name' => '500ml'],
            ['price' => 4.50, 'stock' => null]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $cocacola->id, 'name' => '1.5 Litros'],
            ['price' => 10.00, 'stock' => null]
        );

        $agua = Product::updateOrCreate(
            ['name' => 'Agua Mineral'],
            [
                'category_id' => $catBebida->id,
                'slug' => Str::slug('Agua Mineral'),
                'is_active' => true,
            ]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $agua->id, 'name' => '500ml'],
            ['price' => 3.00, 'stock' => null]
        );

        // Nuevos productos para Cervezas
        $catCervezas = Category::where('slug', 'cervezas')->first();
        if ($catCervezas) {
            $cusquena = Product::updateOrCreate(
                ['name' => 'Cerveza Cusqueña'],
                [
                    'category_id' => $catCervezas->id,
                    'slug' => Str::slug('Cerveza Cusqueña'),
                    'is_active' => true,
                ]
            );
            ProductVariant::updateOrCreate(
                ['product_id' => $cusquena->id, 'name' => 'Botella 630ml'],
                ['price' => 9.00, 'stock' => null]
            );
            ProductVariant::updateOrCreate(
                ['product_id' => $cusquena->id, 'name' => 'Lata 355ml'],
                ['price' => 7.00, 'stock' => null]
            );
        }

        // Nuevos productos para Jugos Naturales
        $catJugos = Category::where('slug', 'jugos-naturales')->first();
        if ($catJugos) {
            $jugo = Product::updateOrCreate(
                ['name' => 'Jugo de Fruta Natural'],
                [
                    'category_id' => $catJugos->id,
                    'slug' => Str::slug('Jugo de Fruta Natural'),
                    'description' => 'Maracuyá, naranja o papaya.',
                    'is_active' => true,
                ]
            );
            ProductVariant::updateOrCreate(
                ['product_id' => $jugo->id, 'name' => 'Vaso'],
                ['price' => 7.00, 'stock' => null]
            );
        }

        // Pollo a la Brasa
        $catPollo = Category::where('slug', 'pollos-a-la-brasa')->first();
        $pollo = Product::updateOrCreate(
            ['name' => 'Pollo a la Brasa'],
            [
                'category_id' => $catPollo->id,
                'slug' => Str::slug('Pollo a la Brasa'),
                'description' => 'Tradicional pollo a la brasa con papas y ensalada.',
                'is_active' => true,
            ]
        );

        ProductVariant::updateOrCreate(
            ['product_id' => $pollo->id, 'name' => '1/4 de Pollo'],
            ['price' => 22.00, 'stock' => null]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $pollo->id, 'name' => '1/2 de Pollo'],
            ['price' => 42.00, 'stock' => null]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $pollo->id, 'name' => '1 Pollo Entero'],
            ['price' => 78.00, 'stock' => null]
        );

        // Broster
        $catBroster = Category::where('slug', 'broster')->first();
        $broster = Product::updateOrCreate(
            ['name' => 'Pollo Broster Crispy'],
            [
                'category_id' => $catBroster->id,
                'slug' => Str::slug('Pollo Broster Crispy'),
                'description' => 'Piezas de pollo crujiente con receta secreta.',
                'is_active' => true,
            ]
        );

        ProductVariant::updateOrCreate(
            ['product_id' => $broster->id, 'name' => 'Pecho'],
            ['price' => 8.00, 'stock' => null]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $broster->id, 'name' => 'Pierna'],
            ['price' => 7.00, 'stock' => null]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $broster->id, 'name' => 'Ala'],
            ['price' => 5.00, 'stock' => null]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $broster->id, 'name' => 'Contramuslo'],
            ['price' => 7.00, 'stock' => null]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $broster->id, 'name' => '1/2 Pollo Broster'],
            ['price' => 22.00, 'stock' => null]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $broster->id, 'name' => '1 Pollo Entero Broster'],
            ['price' => 40.00, 'stock' => null]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $broster->id, 'name' => 'Mostrito'],
            ['price' => 18.00, 'stock' => null]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $broster->id, 'name' => 'Duo Broster'],
            ['price' => 25.00, 'stock' => null]
        );

        // Bebidas
        $catBebida = Category::where('slug', 'bebidas')->first();
        $gaseosa = Product::updateOrCreate(
            ['name' => 'Inca Kola'],
            [
                'category_id' => $catBebida->id,
                'slug' => Str::slug('Inca Kola'),
                'is_active' => true,
            ]
        );

        ProductVariant::updateOrCreate(
            ['product_id' => $gaseosa->id, 'name' => '500ml'],
            ['price' => 4.50, 'stock' => null]
        );
        ProductVariant::updateOrCreate(
            ['product_id' => $gaseosa->id, 'name' => '1.5 Litros'],
            ['price' => 10.00, 'stock' => null]
        );
    }
}
