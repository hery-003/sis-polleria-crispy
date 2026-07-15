<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Attributes\Middleware;
use App\Http\Controllers\Controller;
use App\Models\Mesa;

class MesaController extends Controller
{
    #[Middleware('auth:sanctum')]
    public function index()
    {
        return Mesa::where('is_active', true)
            ->withCount(['orders' => function ($q) {
                $q->whereNotIn('status', ['completed', 'cancelled']);
            }])
            ->orderBy('number')
            ->get();
    }
}
