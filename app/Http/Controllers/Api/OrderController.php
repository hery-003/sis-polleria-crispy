<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $query = Order::with('user', 'items.product', 'items.variant', 'mesa', 'metodoPago', 'client');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 50);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
            'type' => 'required|in:dine_in,take_out,delivery',
            'mesa_id' => 'nullable|exists:mesas,id',
            'metodo_pago_id' => 'nullable|exists:metodos_pago,id',
            'client_id' => 'nullable|exists:clients,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $order = $this->orderService->createOrder($request->all(), $request->user()->id);
            return response()->json($order->load('items.product', 'items.variant', 'mesa'), 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Order $order)
    {
        return $order->load('user', 'items.product', 'items.variant', 'mesa', 'metodoPago', 'client');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,cooking,ready,completed,cancelled',
            'reason' => 'required_if:status,cancelled|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->orderService->updateOrderStatus($order, $request->status, $request->reason);
            return response()->json($order->fresh()->load('items.product', 'items.variant'));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function cancel(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $this->orderService->cancelOrder($order, $request->reason);
            return response()->json($order->fresh()->load('items.product', 'items.variant'));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function markPaid(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $order = $this->orderService->markOrderPaid($order, $request->payment_method);
            return response()->json($order->load('items.product', 'items.variant'));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
