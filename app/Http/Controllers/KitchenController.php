<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Middleware;
use App\Events\OrderUpdated;
use App\Http\Requests\UpdateKitchenStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class KitchenController extends Controller
{
    #[Middleware(['auth', 'role:admin,cashier,waiter'])]
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index()
    {
        $orders = Cache::remember('kitchen_orders', now()->addSeconds(config('cache_ttl.kitchen_orders')), function () {
            return Order::with(['items.product', 'items.variant'])
                ->whereIn('status', ['pending', 'cooking', 'ready'])
                ->orderBy('created_at', 'asc')
                ->get();
        });

        return Inertia::render('Kitchen/Index', [
            'orders' => $orders,
        ]);
    }

    public function updateStatus(UpdateKitchenStatusRequest $request, Order $order)
    {
        $validated = $request->validated();

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
            Log::error('Error updating kitchen status: '.$e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
