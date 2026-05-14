<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MetodoPago;
use App\Models\Mesa;
use App\Models\ProductVariant;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Cache;
use Exception;
use App\Events\OrderCreated;
use App\Events\OrderUpdated;

class POSController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index()
    {
        $categories = Cache::remember('pos_categories', now()->addMinutes(30), function () {
            return Category::where('is_active', true)
                ->orderBy('sort_order')
                ->with(['products' => function ($query) {
                    $query->where('is_active', true)
                        ->with(['variants' => function ($q) {
                            $q->where(function ($q2) {
                                $q2->whereNull('stock')->orWhere('stock', '>', 0);
                            });
                        }])
                        ->whereHas('variants', function ($q) {
                            $q->where(function ($q2) {
                                $q2->whereNull('stock')->orWhere('stock', '>', 0);
                            });
                        });
                }])
                ->get();
        });

        $metodosPago = Cache::remember('pos_metodos_pago', now()->addMinutes(60), function () {
            return MetodoPago::where('is_active', true)->get();
        });

        $mesas = Cache::remember('pos_mesas', now()->addMinutes(60), function () {
            return Mesa::where('is_active', true)->orderBy('number')->get();
        });

        return Inertia::render('POS/Index', [
            'categories' => $categories,
            'metodosPago' => $metodosPago,
            'mesas' => $mesas,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.variant_id' => 'required|integer|exists:product_variants,id',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,card,yape,plin',
            'type' => 'required|in:dine_in,take_out,delivery',
            'total' => 'nullable|numeric',
            'metodo_pago_id' => 'nullable|integer',
            'notes' => 'nullable|string',
            'mesa_id' => 'nullable|integer',
            'client_id' => 'nullable|integer|exists:clients,id',
            'auto_pay' => 'nullable|boolean',
        ]);

        try {
            $order = $this->orderService->createOrder([
                'items' => $validated['items'],
                'payment_method' => $validated['payment_method'],
                'type' => $validated['type'],
                'metodo_pago_id' => $validated['metodo_pago_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'mesa_id' => $validated['mesa_id'] ?? null,
                'client_id' => $validated['client_id'] ?? null,
            ], auth()->id());

            // Auto-pay: marcar como pagado y completar si no es para mesa
            if (!empty($validated['auto_pay']) && $validated['auto_pay'] !== false) {
                $this->orderService->markOrderPaid($order, $validated['payment_method']);
                if ($validated['type'] !== 'dine_in') {
                    $order->update(['status' => 'completed']);
                }
                OrderUpdated::dispatch($order->fresh());
            }

            // Invalidar caches
            Cache::forget('dashboard_data');
            Cache::forget('kitchen_orders');
            Cache::forget('reports_stats_' . now()->format('Y-m-d') . '_' . now()->format('Y-m-d'));

            // Dispatch printing to background job
            \App\Jobs\ProcessOrderPrinting::dispatch($order->id);

            // Disparar evento de broadcast
            OrderCreated::dispatch($order);

            return redirect()->back()->with([
                'success' => 'Pedido registrado correctamente',
                'last_order_id' => $order->id,
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al procesar el pedido: ' . $e->getMessage());
        }
    }
}
