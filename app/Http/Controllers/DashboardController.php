<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Middleware;
use App\Models\CashRegister;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    #[Middleware('auth')]
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        $today = Carbon::today();
        $weekStart = $today->copy()->subDays(6);

        $dashboardData = Cache::flexible('dashboard_data', config('cache_ttl.dashboard_data'), function () use ($today, $weekStart) {
            $stats = $this->reportService->getDailySummary($today);

            $activeRegister = CashRegister::where('status', 'open')->first();

            $recentOrders = Order::with(['user', 'items.product'])
                ->whereDate('created_at', $today)
                ->latest()
                ->take(5)
                ->get();

            $lowStock = ProductVariant::whereNotNull('stock')
                ->whereColumn('stock', '<=', 'stock_threshold')
                ->with('product')
                ->orderBy('stock')
                ->take(10)
                ->get()
                ->map(fn ($v) => [
                    'product_name' => $v->product?->name ?? 'Producto eliminado',
                    'variant_name' => $v->name,
                    'stock' => $v->stock,
                ]);

            $weeklySales = $this->reportService->getSalesByDay($weekStart, $today->copy()->endOfDay());

            $topProducts = $this->reportService->getTopProducts($weekStart, $today->copy()->endOfDay(), 5);

            return [
                'todaySales' => $stats['total_sales'],
                'activeOrders' => Order::whereIn('status', ['pending', 'cooking', 'ready'])->count(),
                'cancellations' => $stats['cancelled'],
                'avgTicket' => $stats['avg_ticket'],
                'cashTotal' => $stats['cash_total'],
                'totalOrdersToday' => $stats['orders_count'],
                'activeRegister' => $activeRegister,
                'recentOrders' => $recentOrders,
                'lowStock' => $lowStock,
                'weeklySales' => $weeklySales,
                'topProducts' => $topProducts,
            ];
        });

        Cache::touch('dashboard_data', config('cache_ttl.dashboard_data')[1]);

        return inertia('Dashboard', $dashboardData);
    }
}
