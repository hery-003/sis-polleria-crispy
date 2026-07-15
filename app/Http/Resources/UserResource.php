<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class UserResource extends JsonApiResource
{
    public string $type = 'users';

    protected function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }

    protected function toRelationships(Request $request): array
    {
        return [
            'orders' => fn() => OrderResource::collection($this->whenLoaded('orders')),
            'cashRegisters' => fn() => CashRegisterResource::collection($this->whenLoaded('cashRegisters')),
        ];
    }
}
