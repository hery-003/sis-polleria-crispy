<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Exception;
use App\Events\OrderUpdated;

class WaiterController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index()
    {
        $orders = Cache::remember('waiter_orders', now()->addSeconds(10), function () {
            return Order::with(['items.product', 'items.variant', 'mesa', 'user'])
                ->whereIn('status', ['ready', 'completed'])
                ->where('type', 'dine_in')
                ->orderBy('updated_at', 'desc')
                ->get();
        });

        return Inertia::render('Waiter/Index', [
            'orders' => $orders,
        ]);
    }

    public function markDelivered(Order $order)
    {
        if ($order->status !== 'ready') {
            return redirect()->back()->with('error', 'Solo se pueden entregar pedidos en estado "listo"');
        }

        try {
            $this->orderService->updateOrderStatus($order, 'completed');

            OrderUpdated::dispatch($order->fresh());

            Cache::forget('waiter_orders');
            Cache::forget('dashboard_data');
            Cache::forget('kitchen_orders');

            return redirect()->back()->with('success', 'Pedido marcado como entregado');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
