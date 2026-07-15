<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class CashMovementResource extends JsonApiResource
{
    public string $type = 'cash-movements';

    protected function toAttributes(Request $request): array
    {
        return [
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'cash_register_id' => $this->cash_register_id,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    protected function toRelationships(Request $request): array
    {
        return [
            'cashRegister' => fn() => new CashRegisterResource($this->whenLoaded('cashRegister')),
            'user' => fn() => new UserResource($this->whenLoaded('user')),
        ];
    }
}
