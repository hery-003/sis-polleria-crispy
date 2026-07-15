<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Attributes\Authorize;
use Illuminate\Routing\Attributes\Middleware;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    #[Middleware(['auth', 'role:admin'])]
    #[Authorize('manage-audit-logs')]
    public function index(Request $request)
    {
        $cacheKey = 'audit_logs_'.md5(serialize($request->only(['action', 'user_id', 'date_from', 'date_to'])).request('page', 1));

        $logs = Cache::tags(['audit_logs'])->remember($cacheKey, 60, function () use ($request) {
            $query = AuditLog::with('user:id,name')
                ->select('id', 'user_id', 'action', 'auditable_type', 'auditable_id', 'old_values', 'new_values', 'ip_address', 'user_agent', 'created_at')
                ->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

            return $query->paginate(50)->withQueryString();
        });

        $actions = AuditLog::select('action')
            ->distinct()
            ->pluck('action');

        $users = User::select('id', 'name')
            ->orderBy('name')
            ->take(100)
            ->get();

        return Inertia::render('AuditLogs/Index', [
            'logs' => $logs,
            'actions' => $actions,
            'users' => $users,
            'filters' => $request->only(['action', 'user_id', 'date_from', 'date_to']),
        ]);
    }
}
