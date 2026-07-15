<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ClientResource extends JsonApiResource
{
    public string $type = 'clients';

    protected function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'document_number' => $this->document_number,
            'address' => $this->address,
            'points' => $this->points,
            'is_active' => $this->is_active,
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
