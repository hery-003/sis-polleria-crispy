<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order->load('items.product', 'items.variant', 'user', 'mesa', 'client');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'type' => $this->order->type,
            'status' => $this->order->status,
            'total_amount' => (float) $this->order->total_amount,
            'client_name' => $this->order->client?->name,
            'mesa' => $this->order->mesa ? ['number' => $this->order->mesa->number] : null,
            'user' => ['name' => $this->order->user?->name],
            'notes' => $this->order->notes,
            'items' => $this->order->items->map(fn($i) => [
                'id' => $i->id,
                'quantity' => $i->quantity,
                'price' => (float) $i->price,
                'subtotal' => (float) $i->subtotal,
                'notes' => $i->notes,
                'product' => ['name' => $i->product?->name],
                'variant' => ['name' => $i->variant?->name],
            ])->toArray(),
            'created_at' => $this->order->created_at->toIso8601String(),
        ];
    }
}
