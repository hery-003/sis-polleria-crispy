<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SanitizeInput
{
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value, $key) {
            if (is_string($value) && !in_array($key, ['password', 'password_confirmation', 'current_password', '_token'])) {
                $value = trim($value);
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
