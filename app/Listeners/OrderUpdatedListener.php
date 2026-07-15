<?php

namespace App\Listeners;

use App\Events\OrderUpdated;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class OrderUpdatedListener
{
    public function handle(OrderUpdated $event): void
    {
        $order = $event->order;

        AuditLog::log('order_updated', 'Order', $order->id, null, [
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
        ]);

        Log::info("Pedido actualizado: {$order->order_number}", [
            'order_id' => $order->id,
            'status' => $order->status,
        ]);
    }
}
