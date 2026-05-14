<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Pollos a la Brasa', 'sort_order' => 1],
            ['name' => 'Broster', 'sort_order' => 2],
            ['name' => 'Combos Familiares', 'sort_order' => 3],
            ['name' => 'Bebidas', 'sort_order' => 4],
            ['name' => 'Guarniciones', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                [
                    'slug' => Str::slug($category['name']),
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
