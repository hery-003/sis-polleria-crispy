<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApplySettings
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = Cache::remember('settings_all', 300, function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        Cache::touch('settings_all', 300);

        if ($lifetime = ($settings['session_lifetime'] ?? null)) {
            config(['session.lifetime' => (int) $lifetime]);
        }

        return $next($request);
    }
}
