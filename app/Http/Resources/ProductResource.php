<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ProductResource extends JsonApiResource
{
    public string $type = 'products';

    protected function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'image_thumbnail_url' => $this->image_thumbnail_url,
            'is_active' => $this->is_active,
            'category_id' => $this->category_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    protected function toRelationships(Request $request): array
    {
        return [
            'category' => fn() => new CategoryResource($this->whenLoaded('category')),
            'variants' => fn() => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
