<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No autenticado'], 401);
            }

            return redirect()->route('login');
        }

        if (! in_array($request->user()->role, $roles)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No autorizado'], 403);
            }

            return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a esta página.');
        }

        return $next($request);
    }
}
