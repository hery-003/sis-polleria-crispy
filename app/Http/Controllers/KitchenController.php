<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Exception;
use App\Events\OrderUpdated;

class KitchenController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index()
    {
        $orders = Cache::remember('kitchen_orders', now()->addSeconds(10), function () {
            return Order::with(['items.product', 'items.variant'])
                ->whereIn('status', ['pending', 'cooking', 'ready'])
                ->orderBy('created_at', 'asc')
                ->get();
        });

        return Inertia::render('Kitchen/Index', [
            'orders' => $orders,
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        if (!$order->canBeModified()) {
            return redirect()->back()->with('error', 'No se puede modificar un pedido completado o cancelado');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,cooking,ready,completed,cancelled',
            'cancellation_reason' => 'required_if:status,cancelled|string|min:3',
        ]);

        try {
            $reason = $validated['status'] === 'cancelled'
                ? $validated['cancellation_reason']
                : null;

            $this->orderService->updateOrderStatus($order, $validated['status'], $reason);

            // Disparar evento de broadcast
            $freshOrder = $order->fresh();
            if ($freshOrder) {
                OrderUpdated::dispatch($freshOrder);
            }

            Cache::forget('kitchen_orders');
            Cache::forget('dashboard_data');
            Cache::forget('waiter_orders');

            return redirect()->back()->with('success', 'Estado actualizado');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
