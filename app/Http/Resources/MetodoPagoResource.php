<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class MetodoPagoResource extends JsonApiResource
{
    public string $type = 'metodos-pago';

    protected function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'is_active' => $this->is_active,
            'qr_image' => $this->qr_image,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    protected function toRelationships(Request $request): array
    {
        return [
            'orders' => fn() => OrderResource::collection($this->whenLoaded('orders')),
        ];
    }
}
