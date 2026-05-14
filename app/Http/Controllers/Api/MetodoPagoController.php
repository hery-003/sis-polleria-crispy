<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MetodoPago;

class MetodoPagoController extends Controller
{
    public function index()
    {
        return MetodoPago::where('is_active', true)->get();
    }
}
