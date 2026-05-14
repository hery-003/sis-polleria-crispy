<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ReportService
{
    public function getStats(Carbon $startDate, Carbon $endDate): array
    {
        $cacheKey = "reports_stats_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($startDate, $endDate) {
            return [
                'totalSales' => $this->getTotalSales($startDate, $endDate),
                'ordersCount' => $this->getOrdersCount($startDate, $endDate),
                'cancellations' => $this->getCancellations($startDate, $endDate),
                'netIncome' => $this->getNetIncome($startDate, $endDate),
                'avgTicket' => $this->getAvgTicket($startDate, $endDate),
                'topProducts' => $this->getTopProducts($startDate, $endDate),
                'salesByPayment' => $this->getSalesByPayment($startDate, $endDate),
                'salesByDay' => $this->getSalesByDay($startDate, $endDate),
                'salesByHour' => $this->getSalesByHour($startDate, $endDate),
                'salesByType' => $this->getSalesByType($startDate, $endDate),
                'salesByCategory' => $this->getSalesByCategory($startDate, $endDate),
            ];
        });
    }

    public function getDailySummary(Carbon $date): array
    {
        $cacheKey = "daily_summary_{$date->format('Y-m-d')}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($date) {
            $start = $date->copy()->startOfDay();
            $end = $date->copy()->endOfDay();

            return [
                'total_sales' => $this->getTotalSales($start, $end),
                'orders_count' => $this->getOrdersCount($start, $end),
                'cancelled' => $this->getCancellations($start, $end),
                'avg_ticket' => $this->getAvgTicket($start, $end),
                'cash_total' => Order::where('payment_method', 'cash')
                    ->where('payment_status', 'paid')
                    ->whereBetween('created_at', [$start, $end])->sum('total_amount'),
            ];
        });
    }

    public function clearCache(): void
    {
        $today = now()->format('Y-m-d');
        Cache::forget("daily_summary_{$today}");
        Cache::forget("reports_stats_{$today}_{$today}");
        // Clear all report caches for the last 30 days
        for ($i = 0; $i < 30; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            Cache::forget("daily_summary_{$date}");
            Cache::forget("reports_stats_{$date}_{$date}");
        }
    }

    protected function getTotalSales(Carbon $start, Carbon $end): float
    {
        return (float) Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_amount');
    }

    protected function getOrdersCount(Carbon $start, Carbon $end): int
    {
        return Order::whereBetween('created_at', [$start, $end])->count();
    }

    protected function getCancellations(Carbon $start, Carbon $end): int
    {
        return Order::where('status', 'cancelled')
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    protected function getNetIncome(Carbon $start, Carbon $end): float
    {
        $totalSales = $this->getTotalSales($start, $end);
        
        $refundedAmount = (float) Order::where('payment_status', 'refunded')
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_amount');

        return $totalSales - $refundedAmount;
    }

    protected function getAvgTicket(Carbon $start, Carbon $end): float
    {
        $paidOrders = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        if ($paidOrders === 0) {
            return 0;
        }

        return $this->getTotalSales($start, $end) / $paidOrders;
    }

    protected function getTopProducts(Carbon $start, Carbon $end, int $limit = 5)
    {
        return OrderItem::select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('order', function ($q) use ($start, $end) {
                $q->where('payment_status', 'paid')
                  ->whereBetween('created_at', [$start, $end]);
            })
            ->groupBy('product_id')
            ->with('product')
            ->orderBy('total_qty', 'desc')
            ->take($limit)
            ->get();
    }

    protected function getSalesByPayment(Carbon $start, Carbon $end)
    {
        return Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->select('payment_method', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();
    }

    protected function getSalesByDay(Carbon $start, Carbon $end)
    {
        return Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    protected function getSalesByHour(Carbon $start, Carbon $end)
    {
        $driver = DB::connection()->getDriverName();
        $hourExpr = match ($driver) {
            'sqlite' => "CAST(strftime('%H', created_at) AS INTEGER)",
            'pgsql'  => "EXTRACT(HOUR FROM created_at)::int",
            default  => "HOUR(created_at)",
        };

        return Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->select(DB::raw("{$hourExpr} as hour"), DB::raw('SUM(total_amount) as total'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }

    protected function getSalesByType(Carbon $start, Carbon $end)
    {
        return Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->select('type', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();
    }

    protected function getSalesByCategory(Carbon $start, Carbon $end)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereIn('orders.payment_status', ['paid', 'completed'])
            ->whereBetween('orders.created_at', [$start, $end])
            ->select('categories.name', DB::raw('SUM(order_items.subtotal) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total', 'desc')
            ->get();
    }
}
