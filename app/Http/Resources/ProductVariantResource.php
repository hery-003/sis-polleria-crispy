<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ProductVariantResource extends JsonApiResource
{
    public string $type = 'product-variants';

    protected function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'image' => $this->image,
            'image_thumbnail' => $this->image_thumbnail,
            'stock' => $this->stock,
            'stock_threshold' => $this->stock_threshold,
            'is_active' => $this->is_active,
            'product_id' => $this->product_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    protected function toRelationships(Request $request): array
    {
        return [
            'product' => fn() => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
