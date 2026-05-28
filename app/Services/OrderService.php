<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    public function createOrder(array $data, int $userId): Order
    {
        $startTime = microtime(true);
        $this->validateOrderData($data);

        return DB::transaction(function () use ($data, $userId, $startTime) {
            $order = Order::create([
                'user_id' => $userId,
                'order_number' => $this->generateOrderNumber(),
                'total_amount' => 0,
                'status' => 'pending',
                'payment_status' => 'pending',
                'metodo_pago_id' => $data['metodo_pago_id'] ?? null,
                'payment_method' => $data['payment_method'],
                'type' => $data['type'],
                'notes' => $data['notes'] ?? null,
                'mesa_id' => $data['mesa_id'] ?? null,
                'client_id' => $data['client_id'] ?? null,
            ]);

            $totalAmount = 0;
            foreach ($data['items'] as $item) {
                $totalAmount += $this->createOrderItem($order, $item);
            }
            $order->update(['total_amount' => $totalAmount]);

            $this->logOrderAction($order, 'order_created', [
                'items_count' => count($data['items']),
                'total' => $order->total_amount,
            ]);

            $endTime = microtime(true);
            $duration = $endTime - $startTime;
            \Illuminate\Support\Facades\Log::info("Order creation time: " . round($duration, 3) . "s");
            if ($duration > 1) {
                \Illuminate\Support\Facades\Log::warning("⚠️ Order creation exceeded 1s limit!");
            } else {
                \Illuminate\Support\Facades\Log::info("✅ Order creation within 1s limit");
            }

            return $order;
        });
    }

    public function updateOrderStatus(Order $order, string $newStatus, ?string $reason = null): bool
    {
        if ($newStatus === 'cancelled') {
            return $this->cancelOrder($order, $reason);
        }

        if (!$order->canBeModified()) {
            throw new Exception('No se puede modificar un pedido completado o cancelado');
        }

        $allowedTransitions = [
            'pending'  => ['cooking'],
            'cooking'  => ['ready'],
            'ready'    => ['completed'],
        ];

        $currentStatus = $order->status;
        if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            throw new Exception("Transición de estado no válida: {$currentStatus} -> {$newStatus}");
        }

        $oldStatus = $order->status;
        $order->update(['status' => $newStatus]);

        $this->logOrderAction($order, 'status_changed', [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        return true;
    }

    public function cancelOrder(Order $order, ?string $reason = null): bool
    {
        if (!$reason || strlen(trim($reason)) < 3) {
            throw new Exception('La cancelación requiere un motivo obligatorio (mínimo 3 caracteres)');
        }

        return DB::transaction(function () use ($order, $reason) {
            $wasPaid = $order->payment_status === 'paid';
            $amountToRefund = $order->total_amount;

            $success = $order->cancel($reason);

            if ($success) {
                // Devolver stock
                foreach ($order->items as $item) {
                    if ($item->variant) {
                        $item->variant->increment('stock', $item->quantity);
                    }
                }

                // Descontar puntos si fue pagado
                if ($wasPaid && $order->client_id) {
                    $pointsToDeduct = floor($order->total_amount / 10);
                    if ($pointsToDeduct > 0) {
                        $order->client->decrement('points', $pointsToDeduct);
                    }
                }

                // Si estaba pagado en efectivo, registrar el egreso en la caja que procesó el pago
                if ($wasPaid && $order->payment_method === 'cash') {
                    $registerId = $order->cash_register_id
                        ?? \App\Models\CashRegister::where('status', 'open')->first()?->id;
                    if ($registerId) {
                        \App\Models\CashMovement::create([
                            'cash_register_id' => $registerId,
                            'user_id' => auth()->id() ?? $order->user_id,
                            'type' => 'out',
                            'amount' => $amountToRefund,
                            'description' => "Devolución por anulación de pedido #{$order->order_number}. Motivo: {$reason}"
                        ]);
                    }
                }

                $this->logOrderAction($order, 'order_cancelled', [
                    'reason' => $reason,
                    'refunded' => $wasPaid,
                    'amount' => $wasPaid ? $amountToRefund : 0
                ]);

                // Monitoreo de Seguridad: Alerta si hay muchas anulaciones
                $userId = auth()->id() ?? $order->user_id;
                $cancellationCount = AuditLog::where('user_id', $userId)
                    ->where('action', 'order_cancelled')
                    ->where('created_at', '>=', now()->subHour())
                    ->count();

                if ($cancellationCount >= 3) {
                    $user = \App\Models\User::find($userId);
                    \App\Events\SecurityAlert::dispatch(
                        $user,
                        'high_cancellations',
                        "El usuario {$user->name} ha realizado {$cancellationCount} anulaciones en la última hora.",
                        ['count' => $cancellationCount, 'last_order' => $order->order_number]
                    );
                }
            }

            return $success;
        });
    }

    public function markOrderPaid(Order $order, string $paymentMethod): Order
    {
        if (!$order->canBePaid()) {
            throw new Exception('Este pedido no puede ser marcado como pagado');
        }

        $order->markAsPaid($paymentMethod);

        // Vincular con la caja abierta si es pago en efectivo
        if ($paymentMethod === 'cash') {
            $activeRegister = \App\Models\CashRegister::where('status', 'open')->first();
            if ($activeRegister && !$order->cash_register_id) {
                $order->update(['cash_register_id' => $activeRegister->id]);
            }
        }

        // Sistema de puntos: 1 punto por cada 10 Bs.
        if ($order->client_id) {
            $points = floor($order->total_amount / 10);
            if ($points > 0) {
                $order->client->increment('points', $points);
            }
        }

        $this->logOrderAction($order, 'order_paid', [
            'payment_method' => $paymentMethod,
            'points_earned' => $order->client_id ? floor($order->total_amount / 10) : 0
        ]);

        return $order;
    }

    protected function validateOrderData(array $data): void
    {
        if (empty($data['items'])) {
            throw new Exception('El pedido debe tener al menos un producto');
        }

        foreach ($data['items'] as $item) {
            if (!isset($item['product_id']) || !isset($item['variant_id'])) {
                throw new Exception('Cada item debe tener product_id y variant_id');
            }
            if ($item['quantity'] <= 0) {
                throw new Exception('La cantidad debe ser mayor a 0');
            }
        }
    }

    protected function createOrderItem(Order $order, array $item): float
    {
        // Usamos lockForUpdate para prevenir condiciones de carrera en el stock
        $variant = ProductVariant::where('id', $item['variant_id'])->lockForUpdate()->first();

        if (!$variant) {
            throw new Exception("Variante no encontrada: {$item['variant_id']}");
        }

        // Validar que el product_id corresponde a la variante
        if ($variant->product_id !== (int) $item['product_id']) {
            throw new Exception("La variante no pertenece al producto indicado");
        }

        // Validar que el precio enviado coincide con el precio real (evita manipulación)
        $submittedPrice = (float) $item['price'];
        $actualPrice = (float) $variant->price;
        if (abs($submittedPrice - $actualPrice) > 0.0001) {
            throw new Exception("Precio inválido para {$variant->name}");
        }

        if ($variant->stock !== null && $variant->stock < $item['quantity']) {
            throw new Exception("Stock insuficiente para {$variant->name} (Disponible: {$variant->stock})");
        }

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $variant->product_id,
            'product_variant_id' => $item['variant_id'],
            'quantity' => $item['quantity'],
            'price' => $variant->price,
            'subtotal' => $variant->price * $item['quantity'],
        ]);

        if ($variant->stock !== null) {
            $variant->decrement('stock', $item['quantity']);
        }

        return $variant->price * $item['quantity'];
    }

    protected function generateOrderNumber(): string
    {
        return 'ORD-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid('', true), -8));
    }

    protected function logOrderAction(Order $order, string $action, array $details = []): void
    {
        AuditLog::log($action, 'Order', $order->id, null, [
            'order_number' => $order->order_number,
            ...$details,
        ]);
    }
}
