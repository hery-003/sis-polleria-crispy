<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class MesaResource extends JsonApiResource
{
    public string $type = 'mesas';

    protected function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'number' => $this->number,
            'capacity' => $this->capacity,
            'is_active' => $this->is_active,
            'status' => $this->status,
            'reserved_at' => $this->reserved_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    protected function toRelationships(Request $request): array
    {
        return [
            'orders' => fn() => OrderResource::collection($this->whenLoaded('orders')),
        ];
    }
}
