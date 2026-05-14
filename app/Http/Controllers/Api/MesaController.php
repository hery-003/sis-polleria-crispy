<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mesa;

class MesaController extends Controller
{
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
