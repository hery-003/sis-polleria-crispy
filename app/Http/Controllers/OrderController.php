<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function print(Order $order)
    {
        $order->load(['items.product', 'items.variant', 'user']);
        
        return Inertia::render('Orders/Print', [
            'order' => $order
        ]);
    }
}
