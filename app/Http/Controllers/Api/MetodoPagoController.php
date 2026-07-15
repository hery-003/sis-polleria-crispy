<?php

namespace App\Http\Controllers\Api;

use Illuminate\Routing\Attributes\Middleware;
use App\Http\Controllers\Controller;
use App\Models\MetodoPago;

class MetodoPagoController extends Controller
{
    #[Middleware('auth:sanctum')]
    public function index()
    {
        return MetodoPago::where('is_active', true)->get();
    }
}
