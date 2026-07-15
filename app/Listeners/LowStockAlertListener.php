<?php

namespace App\Listeners;

use App\Events\LowStockAlert;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class LowStockAlertListener
{
    public function handle(LowStockAlert $event): void
    {
        $variant = $event->variant;

        AuditLog::log('stock_low', 'ProductVariant', $variant->id, null, [
            'product_name' => $variant->product?->name,
            'variant_name' => $variant->name,
            'stock' => $variant->stock,
            'threshold' => $variant->stock_threshold,
        ]);

        Log::warning("Stock bajo: {$variant->product?->name} - {$variant->name}", [
            'variant_id' => $variant->id,
            'stock' => $variant->stock,
            'threshold' => $variant->stock_threshold,
        ]);
    }
}
