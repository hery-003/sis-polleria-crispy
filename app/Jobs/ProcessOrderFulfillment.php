<?php

namespace App\Jobs;

use App\Events\OrderCreated;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\DebounceFor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

#[DebounceFor(5000)]
#[Once]
#[FailOnTimeout]
class ProcessOrderFulfillment implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected int $orderId
    ) {}

    public function handle(): void
    {
        $order = Order::with(['items.product', 'items.variant', 'user', 'mesa', 'metodoPago'])
            ->find($this->orderId);

        if (! $order) {
            Log::warning("Order #{$this->orderId} no longer exists, skipping fulfillment.");

            return;
        }

        try {
            Cache::forget('dashboard_data');
            Cache::forget('kitchen_orders');
            Cache::forget('waiter_orders');
            Cache::forget('reports_stats_'.now()->format('Y-m-d').'_'.now()->format('Y-m-d'));

            OrderCreated::dispatch($order);

            Log::info("Order #{$order->order_number} fulfilled successfully.");
        } catch (\Exception $e) {
            Log::error("Failed to fulfill order #{$order->order_number}: ".$e->getMessage());
            throw $e;
        }
    }
}
