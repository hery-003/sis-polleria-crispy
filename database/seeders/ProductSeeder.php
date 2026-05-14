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
