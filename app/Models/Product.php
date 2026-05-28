<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['category_id', 'name', 'slug', 'description', 'image', 'is_active'];
    protected $appends = ['image_url'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getImageUrlAttribute(): string|null
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
