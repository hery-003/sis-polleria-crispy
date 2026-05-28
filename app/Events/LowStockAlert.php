<?php

namespace App\Events;

use App\Models\ProductVariant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockAlert implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ProductVariant $variant
    ) {
        $this->variant->load('product');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('inventory'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'stock.low';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->variant->id,
            'product_name' => $this->variant->product->name,
            'variant_name' => $this->variant->name,
            'stock' => $this->variant->stock,
            'threshold' => $this->variant->stock_threshold,
        ];
    }
}
