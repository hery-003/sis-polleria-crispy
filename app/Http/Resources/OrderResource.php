<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class OrderResource extends JsonApiResource
{
    public string $type = 'orders';

    protected function toAttributes(Request $request): array
    {
        return [
            'order_number' => $this->order_number,
            'total_amount' => $this->total_amount,
            'received_amount' => $this->received_amount,
            'change' => $this->change,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'type' => $this->type,
            'notes' => $this->notes,
            'cancellation_reason' => $this->cancellation_reason,
            'user_id' => $this->user_id,
            'mesa_id' => $this->mesa_id,
            'metodo_pago_id' => $this->metodo_pago_id,
            'client_id' => $this->client_id,
            'cash_register_id' => $this->cash_register_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    protected function toRelationships(Request $request): array
    {
        return [
            'user' => fn() => new UserResource($this->whenLoaded('user')),
            'mesa' => fn() => new MesaResource($this->whenLoaded('mesa')),
            'metodoPago' => fn() => new MetodoPagoResource($this->whenLoaded('metodoPago')),
            'client' => fn() => new ClientResource($this->whenLoaded('client')),
            'cashRegister' => fn() => new CashRegisterResource($this->whenLoaded('cashRegister')),
            'items' => fn() => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
