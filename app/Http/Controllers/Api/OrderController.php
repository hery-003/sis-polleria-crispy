<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Attributes\Middleware;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CancelOrderRequest;
use App\Http\Requests\Api\MarkPaidRequest;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Requests\Api\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    #[Middleware('auth:sanctum')]
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $cacheKey = 'api_orders_'.md5(serialize($request->only(['status', 'date_from', 'date_to', 'page', 'per_page'])));

        return Cache::tags(['orders'])->remember($cacheKey, 60, function () use ($request) {
            $query = Order::with([
                'user:id,name',
                'items.product:id,name,image',
                'items.variant:id,name,price',
                'mesa:id,name',
                'metodoPago:id,name,slug',
                'client:id,name',
            ])
                ->select('id', 'order_number', 'user_id', 'mesa_id', 'metodo_pago_id', 'client_id', 'total_amount', 'status', 'type', 'payment_status', 'payment_method', 'received_amount', 'change', 'created_at');

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->date_from) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->date_to) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $perPage = min((int) ($request->per_page ?? 50), 100);

            return $query->orderBy('created_at', 'desc')->paginate($perPage);
        });
    }

    public function store(StoreOrderRequest $request)
    {
        try {
            $order = $this->orderService->createOrder($request->validated(), $request->user()->id);

            Cache::tags(['orders', 'cancellations'])->flush();

            return response()->json($order->load('items.product', 'items.variant', 'mesa'), 201);
        } catch (\Exception $e) {
            Log::error('Error creating order: '.$e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Order $order)
    {
        return $order->load('user', 'items.product', 'items.variant', 'mesa', 'metodoPago', 'client');
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order)
    {
        try {
            $this->orderService->updateOrderStatus($order, $request->status, $request->reason);

            Cache::tags(['orders', 'cancellations'])->flush();

            return response()->json($order->fresh()->load('items.product', 'items.variant'));
        } catch (\Exception $e) {
            Log::error('Error updating order status: '.$e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cancel(CancelOrderRequest $request, Order $order)
    {
        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'No se puede cancelar un pedido pagado. Realice una devolución desde el módulo de caja.'], 422);
        }

        try {
            $this->orderService->cancelOrder($order, $request->reason);

            Cache::tags(['orders', 'cancellations'])->flush();

            return response()->json($order->fresh()->load('items.product', 'items.variant'));
        } catch (\Exception $e) {
            Log::error('Error cancelling order: '.$e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function markPaid(MarkPaidRequest $request, Order $order)
    {
        try {
            $order = $this->orderService->markOrderPaid($order, $request->payment_method);

            Cache::tags(['orders'])->flush();

            return response()->json($order->load('items.product', 'items.variant'));
        } catch (\Exception $e) {
            Log::error('Error marking order paid: '.$e->getMessage());
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
