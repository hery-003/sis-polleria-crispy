<?php

namespace App\Services;

use App\Events\SecurityAlert;
use App\Models\AuditLog;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected StockService $stockService,
        protected LoyaltyService $loyaltyService,
    ) {}

    public function createOrder(array $data, int $userId): Order
    {
        $startTime = microtime(true);
        $this->validateOrderData($data);

        return DB::transaction(function () use ($data, $userId, $startTime) {
            $order = Order::create([
                'user_id' => $userId,
                'order_number' => $this->generateOrderNumber(),
                'total_amount' => 0,
                'received_amount' => $data['received_amount'] ?? null,
                'change' => $data['change'] ?? null,
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
            Log::info('Order creation time: '.round($duration, 3).'s');
            if ($duration > 1) {
                Log::warning('Order creation exceeded 1s limit!');
            } else {
                Log::info('Order creation within 1s limit');
            }

            return $order;
        });
    }

    public function canBeModified(Order $order): bool
    {
        return ! in_array($order->status, ['completed', 'cancelled']);
    }

    public function canBeCancelled(Order $order): bool
    {
        return ! in_array($order->status, ['completed', 'cancelled']);
    }

    public function canBePaid(Order $order): bool
    {
        return ! in_array($order->status, ['cancelled', 'completed']) && $order->payment_status !== 'paid';
    }

    public function updateOrderStatus(Order $order, string $newStatus, ?string $reason = null): bool
    {
        if ($newStatus === 'cancelled') {
            return $this->cancelOrder($order, $reason);
        }

        if (! $this->canBeModified($order)) {
            throw new Exception('No se puede modificar un pedido completado o cancelado');
        }

        $allowedTransitions = [
            'pending' => ['cooking'],
            'cooking' => ['ready'],
            'ready' => ['completed'],
        ];

        $currentStatus = $order->status;
        if (! isset($allowedTransitions[$currentStatus]) || ! in_array($newStatus, $allowedTransitions[$currentStatus])) {
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
        if (! $reason || strlen(trim($reason)) < 3) {
            throw new Exception('La cancelación requiere un motivo obligatorio (mínimo 3 caracteres)');
        }

        return DB::transaction(function () use ($order, $reason) {
            $wasPaid = $order->payment_status === 'paid';
            $amountToRefund = $order->total_amount;

            $success = $this->applyCancellation($order, $reason);

            if ($success) {
                $this->stockService->restoreOrderItemsStock($order->items);

                if ($wasPaid && $order->client_id) {
                    $this->loyaltyService->deductPoints($order);
                }

                if ($wasPaid && $order->payment_method === 'cash') {
                    $registerId = $order->cash_register_id
                        ?? CashRegister::where('status', 'open')->first()?->id;
                    if ($registerId) {
                        CashMovement::create([
                            'cash_register_id' => $registerId,
                            'user_id' => auth()->id() ?? $order->user_id,
                            'type' => 'out',
                            'amount' => $amountToRefund,
                            'description' => "Devolución por anulación de pedido #{$order->order_number}. Motivo: {$reason}",
                        ]);
                    }
                }

                $this->logOrderAction($order, 'order_cancelled', [
                    'reason' => $reason,
                    'refunded' => $wasPaid,
                    'amount' => $wasPaid ? $amountToRefund : 0,
                ]);

                $userId = auth()->id() ?? $order->user_id;
                $cancellationCount = AuditLog::where('user_id', $userId)
                    ->where('action', 'order_cancelled')
                    ->where('created_at', '>=', now()->subHour())
                    ->count();

                if ($cancellationCount >= 3) {
                    $user = User::find($userId);
                    SecurityAlert::dispatch(
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
        if (! $this->canBePaid($order)) {
            throw new Exception('Este pedido no puede ser marcado como pagado');
        }

        $this->applyPayment($order, $paymentMethod);

        if ($paymentMethod === 'cash') {
            $activeRegister = CashRegister::where('status', 'open')->first();
            if ($activeRegister && ! $order->cash_register_id) {
                $order->update(['cash_register_id' => $activeRegister->id]);
            }
        }

        $pointsEarned = $this->loyaltyService->awardPoints($order);

        $this->logOrderAction($order, 'order_paid', [
            'payment_method' => $paymentMethod,
            'points_earned' => $pointsEarned,
        ]);

        return $order;
    }

    protected function applyCancellation(Order $order, string $reason): bool
    {
        if (! $this->canBeCancelled($order)) {
            return false;
        }

        $order->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'payment_status' => $order->payment_status === 'paid' ? 'refunded' : 'pending',
        ]);

        return true;
    }

    protected function applyPayment(Order $order, string $paymentMethod): void
    {
        $order->update([
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod,
        ]);
    }

    protected function validateOrderData(array $data): void
    {
        if (empty($data['items'])) {
            throw new Exception('El pedido debe tener al menos un producto');
        }

        foreach ($data['items'] as $item) {
            if (! isset($item['product_id']) || ! isset($item['variant_id'])) {
                throw new Exception('Cada item debe tener product_id y variant_id');
            }
            if ($item['quantity'] <= 0) {
                throw new Exception('La cantidad debe ser mayor a 0');
            }
        }
    }

    protected function createOrderItem(Order $order, array $item): float
    {
        $variant = $this->stockService->lockAndValidateVariant(
            $item['variant_id'],
            $item['product_id'],
            $item['price'],
            $item['quantity']
        );

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $variant->product_id,
            'product_variant_id' => $item['variant_id'],
            'quantity' => $item['quantity'],
            'price' => $variant->price,
            'subtotal' => $variant->price * $item['quantity'],
        ]);

        $this->stockService->decrementStock($variant, $item['quantity']);

        return $variant->price * $item['quantity'];
    }

    protected function generateOrderNumber(): string
    {
        return 'ORD-'.now()->format('ymd').'-'.strtoupper(substr(uniqid('', true), -8));
    }

    protected function logOrderAction(Order $order, string $action, array $details = []): void
    {
        AuditLog::log($action, 'Order', $order->id, null, [
            'order_number' => $order->order_number,
            ...$details,
        ]);
    }
}
