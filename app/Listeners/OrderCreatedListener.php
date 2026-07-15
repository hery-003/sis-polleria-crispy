<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class OrderCreatedListener
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        AuditLog::log('order_created', 'Order', $order->id, null, [
            'order_number' => $order->order_number,
            'total_amount' => $order->total_amount,
            'type' => $order->type,
            'items_count' => $order->items->count(),
        ]);

        Log::info("Pedido creado: {$order->order_number}", [
            'order_id' => $order->id,
            'amount' => $order->total_amount,
        ]);
    }
}
