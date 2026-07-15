<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\ProductVariant;
use Exception;

class StockService
{
    public function lockAndValidateVariant(int $variantId, int $productId, float $submittedPrice, int $quantity): ProductVariant
    {
        $variant = ProductVariant::where('id', $variantId)->lockForUpdate()->first();

        if (! $variant) {
            throw new Exception("Variante no encontrada: {$variantId}");
        }

        if ($variant->product_id !== $productId) {
            throw new Exception('La variante no pertenece al producto indicado');
        }

        $actualPrice = (float) $variant->price;
        if (abs($submittedPrice - $actualPrice) > 0.0001) {
            throw new Exception("Precio inválido para {$variant->name}");
        }

        if ($variant->stock !== null && $variant->stock < $quantity) {
            throw new Exception("Stock insuficiente para {$variant->name} (Disponible: {$variant->stock})");
        }

        return $variant;
    }

    public function decrementStock(ProductVariant $variant, int $quantity): void
    {
        if ($variant->stock !== null) {
            $variant->decrement('stock', $quantity);
        }
    }

    public function restoreStock(OrderItem $item): void
    {
        if ($item->variant) {
            $item->variant->increment('stock', $item->quantity);
        }
    }

    public function restoreOrderItemsStock(iterable $items): void
    {
        foreach ($items as $item) {
            $this->restoreStock($item);
        }
    }

    public function checkLowStock(ProductVariant $variant): bool
    {
        return $variant->stock !== null
            && $variant->stock_threshold !== null
            && $variant->stock <= $variant->stock_threshold;
    }
}
