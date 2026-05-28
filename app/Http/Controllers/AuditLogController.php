<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')
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

        $logs = $query->paginate(50)->withQueryString();

        $actions = AuditLog::select('action')
            ->distinct()
            ->pluck('action');

        $users = \App\Models\User::select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('AuditLogs/Index', [
            'logs' => $logs,
            'actions' => $actions,
            'users' => $users,
            'filters' => $request->only(['action', 'user_id', 'date_from', 'date_to']),
        ]);
    }
}
