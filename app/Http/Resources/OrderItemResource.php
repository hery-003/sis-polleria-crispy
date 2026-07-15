<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class OrderItemResource extends JsonApiResource
{
    public string $type = 'order-items';

    protected function toAttributes(Request $request): array
    {
        return [
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->subtotal,
            'notes' => $this->notes,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    protected function toRelationships(Request $request): array
    {
        return [
            'order' => fn() => new OrderResource($this->whenLoaded('order')),
            'product' => fn() => new ProductResource($this->whenLoaded('product')),
            'variant' => fn() => new ProductVariantResource($this->whenLoaded('variant')),
        ];
    }
}
