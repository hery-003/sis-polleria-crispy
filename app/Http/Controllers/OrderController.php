<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function print(Order $order)
    {
        $this->authorize('view', $order);
        $order->load(['items.product', 'items.variant', 'user', 'mesa', 'metodoPago']);

        return Inertia::render('Orders/Print', [
            'order' => $order,
        ]);
    }
}
