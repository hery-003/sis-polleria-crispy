<?php

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Services\OrderService;

beforeEach(function () {
    $this->orderService = app(OrderService::class);
    $this->user = User::factory()->create(['role' => 'cashier']);

    $category = Category::factory()->create();
    $this->product = Product::factory()->create(['category_id' => $category->id]);
    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'price' => 25.00,
        'stock' => 100,
    ]);
});

it('creates an order successfully', function () {
    $data = [
        'items' => [
            [
                'product_id' => $this->product->id,
                'variant_id' => $this->variant->id,
                'quantity' => 2,
                'price' => 25.00,
            ],
        ],
        'payment_method' => 'cash',
        'type' => 'dine_in',
    ];

    $order = $this->orderService->createOrder($data, $this->user->id);

    expect($order)->toBeInstanceOf(Order::class)
        ->and($order->total_amount)->toEqual(50.0)
        ->and($order->status)->toBe('pending')
        ->and($order->payment_status)->toBe('pending')
        ->and($order->user_id)->toBe($this->user->id);
});

it('requires items to create an order', function () {
    $data = [
        'items' => [],
        'payment_method' => 'cash',
        'type' => 'dine_in',
    ];

    expect(fn() => $this->orderService->createOrder($data, $this->user->id))
        ->toThrow(Exception::class, 'El pedido debe tener al menos un producto');
});

it('requires valid variant for order items', function () {
    $data = [
        'items' => [
            [
                'product_id' => $this->product->id,
                'variant_id' => 99999,
                'quantity' => 1,
                'price' => 25.00,
            ],
        ],
        'payment_method' => 'cash',
        'type' => 'dine_in',
    ];

    expect(fn() => $this->orderService->createOrder($data, $this->user->id))
        ->toThrow(Exception::class);
});

it('deducts stock when creating an order', function () {
    $data = [
        'items' => [
            [
                'product_id' => $this->product->id,
                'variant_id' => $this->variant->id,
                'quantity' => 3,
                'price' => 25.00,
            ],
        ],
        'payment_method' => 'cash',
        'type' => 'take_out',
    ];

    $this->orderService->createOrder($data, $this->user->id);

    $this->variant->refresh();
    expect($this->variant->stock)->toBe(97);
});

it('cancels an order and restores stock', function () {
    $data = [
        'items' => [
            [
                'product_id' => $this->product->id,
                'variant_id' => $this->variant->id,
                'quantity' => 2,
                'price' => 25.00,
            ],
        ],
        'payment_method' => 'cash',
        'type' => 'dine_in',
    ];

    $order = $this->orderService->createOrder($data, $this->user->id);

    // Verify stock was deducted
    $this->variant->refresh();
    expect($this->variant->stock)->toBe(98);

    $this->orderService->cancelOrder($order->load('items'), 'Cliente no vino');

    $order->refresh();
    expect($order->status)->toBe('cancelled')
        ->and($order->cancellation_reason)->toBe('Cliente no vino');

    $this->variant->refresh();
    expect((int) $this->variant->stock)->toBe(100);
});

it('requires cancellation reason', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    expect(fn() => $this->orderService->cancelOrder($order, null))
        ->toThrow(Exception::class, 'requiere un motivo');
});

it('marks order as paid', function () {
    $data = [
        'items' => [
            [
                'product_id' => $this->product->id,
                'variant_id' => $this->variant->id,
                'quantity' => 1,
                'price' => 25.00,
            ],
        ],
        'payment_method' => 'cash',
        'type' => 'dine_in',
    ];

    $order = $this->orderService->createOrder($data, $this->user->id);
    $this->orderService->markOrderPaid($order, 'cash');

    $order->refresh();
    expect($order->payment_status)->toBe('paid')
        ->and($order->payment_method)->toBe('cash');
});

it('cannot modify completed orders', function () {
    $order = Order::factory()->create(['status' => 'completed', 'payment_status' => 'paid']);

    expect(fn() => $this->orderService->updateOrderStatus($order, 'cooking'))
        ->toThrow(Exception::class, 'No se puede modificar');
});

it('cannot cancel paid orders without reason', function () {
    $order = Order::factory()->create(['status' => 'pending', 'payment_status' => 'paid']);

    expect(fn() => $this->orderService->cancelOrder($order, null))
        ->toThrow(Exception::class);
});

it('generates unique order numbers', function () {
    $data = [
        'items' => [
            [
                'product_id' => $this->product->id,
                'variant_id' => $this->variant->id,
                'quantity' => 1,
                'price' => 25.00,
            ],
        ],
        'payment_method' => 'cash',
        'type' => 'dine_in',
    ];

    $order1 = $this->orderService->createOrder($data, $this->user->id);
    $order2 = $this->orderService->createOrder($data, $this->user->id);

    expect($order1->order_number)->not->toBe($order2->order_number);
});

it('prevents negative quantities', function () {
    $data = [
        'items' => [
            [
                'product_id' => $this->product->id,
                'variant_id' => $this->variant->id,
                'quantity' => -1,
                'price' => 25.00,
            ],
        ],
        'payment_method' => 'cash',
        'type' => 'dine_in',
    ];

    expect(fn() => $this->orderService->createOrder($data, $this->user->id))
        ->toThrow(Exception::class, 'La cantidad debe ser mayor a 0');
});

it('prevents insufficient stock', function () {
    $this->variant->update(['stock' => 1]);

    $data = [
        'items' => [
            [
                'product_id' => $this->product->id,
                'variant_id' => $this->variant->id,
                'quantity' => 5,
                'price' => 25.00,
            ],
        ],
        'payment_method' => 'cash',
        'type' => 'dine_in',
    ];

    expect(fn() => $this->orderService->createOrder($data, $this->user->id))
        ->toThrow(Exception::class, 'Stock insuficiente');
});

it('updates order status correctly', function () {
    $order = Order::factory()->create(['status' => 'pending', 'payment_status' => 'pending']);

    $this->orderService->updateOrderStatus($order, 'cooking');
    expect($order->fresh()->status)->toBe('cooking');

    $this->orderService->updateOrderStatus($order, 'ready');
    expect($order->fresh()->status)->toBe('ready');

    $this->orderService->updateOrderStatus($order, 'completed');
    expect($order->fresh()->status)->toBe('completed');
});

it('prevents invalid status transitions', function () {
    $order = Order::factory()->create(['status' => 'pending', 'payment_status' => 'pending']);

    expect(fn() => $this->orderService->updateOrderStatus($order, 'completed'))
        ->toThrow(Exception::class, 'Transición de estado no válida');

    expect(fn() => $this->orderService->updateOrderStatus($order, 'ready'))
        ->toThrow(Exception::class, 'Transición de estado no válida');
});

it('prevents regression to previous status', function () {
    $order = Order::factory()->create(['status' => 'cooking', 'payment_status' => 'pending']);

    expect(fn() => $this->orderService->updateOrderStatus($order, 'pending'))
        ->toThrow(Exception::class, 'Transición de estado no válida');
});

it('awards points when marking paid with client', function () {
    $client = \App\Models\Client::factory()->create(['points' => 0]);
    $data = [
        'items' => [
            [
                'product_id' => $this->product->id,
                'variant_id' => $this->variant->id,
                'quantity' => 5,
                'price' => 20.00,
            ],
        ],
        'payment_method' => 'cash',
        'type' => 'dine_in',
        'client_id' => $client->id,
    ];

    $order = $this->orderService->createOrder($data, $this->user->id);
    $this->orderService->markOrderPaid($order, 'cash');

    $client->refresh();
    expect((int) $client->points)->toBe(10);
});

it('deducts exact points on cancellation even if client spent some', function () {
    $client = \App\Models\Client::factory()->create(['points' => 100]);
    $data = [
        'items' => [
            [
                'product_id' => $this->product->id,
                'variant_id' => $this->variant->id,
                'quantity' => 10,
                'price' => 25.00,
            ],
        ],
        'payment_method' => 'cash',
        'type' => 'dine_in',
        'client_id' => $client->id,
    ];

    $order = $this->orderService->createOrder($data, $this->user->id);
    $this->orderService->markOrderPaid($order, 'cash');

    // Client spent some points elsewhere
    $client->decrement('points', 60);
    $client->refresh();
    expect((int) $client->points)->toBe(65);

    // Cancel should deduct full 25 points, not min(65, 25)
    $this->orderService->cancelOrder($order->load('items.variant'), 'Cancelado por el cliente');

    $client->refresh();
    expect((int) $client->points)->toBe(40);
});

it('does not create cash movement when cancelling a non-cash paid order', function () {
    $cashRegister = \App\Models\CashRegister::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'open',
        'opening_balance' => 500,
    ]);

    $data = [
        'items' => [
            [
                'product_id' => $this->product->id,
                'variant_id' => $this->variant->id,
                'quantity' => 2,
                'price' => 25.00,
            ],
        ],
        'payment_method' => 'yape',
        'type' => 'dine_in',
    ];

    $order = $this->orderService->createOrder($data, $this->user->id);
    $this->orderService->markOrderPaid($order, 'yape');

    expect(\App\Models\CashMovement::count())->toBe(0);

    $this->orderService->cancelOrder($order->load('items.variant'), 'Cancelado voluntario');

    expect(\App\Models\CashMovement::count())->toBe(0);
});
