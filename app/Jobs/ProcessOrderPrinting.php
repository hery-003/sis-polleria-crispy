<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\PrintService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessOrderPrinting implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $orderId
    ) {
        $this->onQueue('printing');
    }

    /**
     * Execute the job.
     */
    public function handle(PrintService $printService): void
    {
        $order = Order::with(['items.product', 'items.variant', 'user', 'mesa', 'metodoPago'])
            ->find($this->orderId);

        if (!$order || !$order->exists) {
            Log::warning("Order #{$this->orderId} no longer exists, skipping print job.");
            return;
        }

        try {
            $printService->printOrderReceipt($order);
            $printService->printKitchenOrder($order);
            
            Log::info("Order #{$order->order_number} printed successfully via Job.");
        } catch (\Exception $e) {
            Log::error("Failed to print order #{$order->order_number} via Job: " . $e->getMessage());
            throw $e;
        }
    }
}
