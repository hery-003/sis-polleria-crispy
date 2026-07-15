<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Middleware;
use App\Events\OrderUpdated;
use App\Http\Requests\StorePOSOrderRequest;
use App\Jobs\ProcessOrderFulfillment;
use App\Jobs\ProcessOrderPrinting;
use App\Models\Category;
use App\Models\Mesa;
use App\Models\MetodoPago;
use App\Services\OrderService;
use Exception;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class POSController extends Controller
{
    #[Middleware(['auth', 'role:admin,cashier'])]
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index()
    {
        $categories = Cache::flexible('pos_categories', config('cache_ttl.pos_categories'), function () {
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

        Cache::touch('pos_categories', config('cache_ttl.pos_categories')[1]);

        $metodosPago = Cache::flexible('pos_metodos_pago', config('cache_ttl.pos_metodos_pago'), function () {
            return MetodoPago::where('is_active', true)->get();
        });

        Cache::touch('pos_metodos_pago', config('cache_ttl.pos_metodos_pago')[1]);

        $mesas = Cache::flexible('pos_mesas', config('cache_ttl.pos_mesas'), function () {
            return Mesa::where('is_active', true)->orderBy('number')->get();
        });

        Cache::touch('pos_mesas', config('cache_ttl.pos_mesas')[1]);

        return Inertia::render('POS/Index', [
            'categories' => $categories,
            'metodosPago' => $metodosPago,
            'mesas' => $mesas,
        ]);
    }

    public function store(StorePOSOrderRequest $request)
    {
        $validated = $request->validated();

        try {
            $order = $this->orderService->createOrder([
                'items' => $validated['items'],
                'payment_method' => $validated['payment_method'],
                'type' => $validated['type'],
                'metodo_pago_id' => $validated['metodo_pago_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'mesa_id' => $validated['mesa_id'] ?? null,
                'client_id' => $validated['client_id'] ?? null,
                'received_amount' => $validated['received_amount'] ?? null,
                'change' => $validated['change'] ?? null,
            ], auth()->id());

            // Auto-pay: marcar como pagado y completar si no es para mesa
            if (! empty($validated['auto_pay']) && $validated['auto_pay'] !== false) {
                $this->orderService->markOrderPaid($order, $validated['payment_method']);
                if ($validated['type'] !== 'dine_in') {
                    $order->update(['status' => 'completed']);
                }
                OrderUpdated::dispatch($order->fresh());
            }

            // Dispatch job chain: print → fulfill (broadcast + cache invalidation)
            Bus::chain([
                new ProcessOrderPrinting($order->id),
                new ProcessOrderFulfillment($order->id),
            ])->onQueue('printing')->dispatch();

            return redirect()->back()->with([
                'success' => 'Pedido registrado correctamente',
                'last_order_id' => $order->id,
            ]);
        } catch (Exception $e) {
            Log::error('Error processing POS order: '.$e->getMessage());
            return redirect()->back()->with('error', 'Error al procesar el pedido: '.$e->getMessage());
        }
    }
}
