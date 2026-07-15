<?php

use App\Models\Category;
use App\Models\MetodoPago;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

it('renders the print page for an order', function () {
    $order = Order::factory()->create();

    $response = $this->get(route('orders.print', $order));

    $response->assertStatus(200);
});

it('renders receipt for an order', function () {
    $order = Order::factory()->create();

    $response = $this->get(route('orders.receipt', $order));

    $response->assertStatus(200);
});

it('renders kitchen ticket for an order', function () {
    $order = Order::factory()->create();

    $response = $this->get(route('orders.kitchen', $order));

    $response->assertStatus(200);
});

it('renders reprint for an order', function () {
    $order = Order::factory()->create();

    $response = $this->get(route('orders.reprint', $order));

    $response->assertStatus(200);
});

it('downloads pdf for an order', function () {
    $order = Order::factory()->create();

    $response = $this->get(route('orders.pdf', $order));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});

it('auto-prints an order', function () {
    $order = Order::factory()->create();

    $response = $this->post(route('orders.auto-print', $order), [
        'type' => 'receipt',
    ]);

    $response->assertStatus(200);
});

it('allows waiter to view print page', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $this->actingAs($waiter);
    $order = Order::factory()->create();

    $response = $this->get(route('orders.print', $order));

    $response->assertStatus(200);
});

it('prints order with mesa and metodo pago', function () {
    $order = Order::factory()->dineIn()->create();

    $response = $this->get(route('orders.print', $order));

    $response->assertStatus(200);
});
