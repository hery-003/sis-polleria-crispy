<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        $today = Carbon::today();
        
        $dashboardData = Cache::remember('dashboard_data', now()->addMinutes(2), function () use ($today) {
            $stats = $this->reportService->getDailySummary($today);
            
            $activeRegister = \App\Models\CashRegister::where('status', 'open')->first();
            
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
                ->map(fn($v) => [
                    'product_name' => $v->product?->name ?? 'Producto eliminado',
                    'variant_name' => $v->name,
                    'stock' => $v->stock,
                ]);
            
            return [
                'todaySales' => $stats['total_sales'],
                'activeOrders' => Order::whereIn('status', ['pending', 'cooking', 'ready'])->count(),
                'cancellations' => $stats['cancelled'],
                'avgTicket' => $stats['avg_ticket'],
                'cashTotal' => $stats['cash_total'],
                'totalOrdersToday' => Order::whereDate('created_at', $today)->count(),
                'activeRegister' => $activeRegister,
                'recentOrders' => $recentOrders,
                'lowStock' => $lowStock,
            ];
        });
        
        return inertia('Dashboard', $dashboardData);
    }
}
