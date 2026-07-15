<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Middleware;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CancellationController extends Controller
{
    #[Middleware(['auth', 'role:admin'])]
    public function index(Request $request)
    {
        $cacheKey = 'cancellations_'.md5(serialize($request->only(['date_from', 'date_to', 'user_id'])).request('page', 1));

        $orders = Cache::tags(['cancellations'])->remember($cacheKey, 60, function () use ($request) {
            $query = Order::with(['user:id,name', 'items.product:id,name', 'items.variant:id,name'])
                ->select('id', 'order_number', 'user_id', 'total_amount', 'cancellation_reason', 'cancelled_at', 'payment_status', 'created_at')
                ->where('status', 'cancelled')
                ->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

            return $query->paginate(20)->withQueryString();
        });

        $stats = [
            'total_cancelled' => Order::where('status', 'cancelled')->count(),
            'total_refunded' => Order::where('status', 'cancelled')->where('payment_status', 'refunded')->sum('total_amount'),
            'today_cancelled' => Order::where('status', 'cancelled')->whereDate('created_at', today())->count(),
            'top_reasons' => Order::where('status', 'cancelled')
                ->whereNotNull('cancellation_reason')
                ->select('cancellation_reason', DB::raw('COUNT(*) as count'))
                ->groupBy('cancellation_reason')
                ->orderBy('count', 'desc')
                ->take(5)
                ->get(),
        ];

        $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Cancellations/Index', [
            'orders' => $orders,
            'stats' => $stats,
            'users' => $users,
            'filters' => $request->only(['date_from', 'date_to', 'user_id']),
        ]);
    }
}
