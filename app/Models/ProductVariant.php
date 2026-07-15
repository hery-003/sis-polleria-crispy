<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable('product_id', 'name', 'price', 'image', 'image_thumbnail', 'stock', 'stock_threshold', 'is_active')]
class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'stock_threshold' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
