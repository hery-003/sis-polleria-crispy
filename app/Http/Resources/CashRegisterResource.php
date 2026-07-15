<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class CashRegisterResource extends JsonApiResource
{
    public string $type = 'cash-registers';

    protected function toAttributes(Request $request): array
    {
        return [
            'opening_balance' => $this->opening_balance,
            'closing_balance' => $this->closing_balance,
            'opened_at' => $this->opened_at,
            'closed_at' => $this->closed_at,
            'status' => $this->status,
            'notes' => $this->notes,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    protected function toRelationships(Request $request): array
    {
        return [
            'user' => fn() => new UserResource($this->whenLoaded('user')),
            'movements' => fn() => CashMovementResource::collection($this->whenLoaded('movements')),
        ];
    }
}
