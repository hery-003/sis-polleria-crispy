<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable('category_id', 'name', 'slug', 'description', 'image', 'image_thumbnail', 'is_active')]
#[Appends('image_url', 'image_thumbnail_url')]
class Product extends Model
{
    use HasFactory, SoftDeletes;

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

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    public function getImageThumbnailUrlAttribute(): ?string
    {
        return $this->image_thumbnail ? asset('storage/'.$this->image_thumbnail) : $this->image_url;
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
