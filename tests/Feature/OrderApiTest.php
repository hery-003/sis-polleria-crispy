<?php

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'cashier']);
    $this->token = $this->user->createToken('test')->plainTextToken;
    $this->headers = ['Authorization' => "Bearer {$this->token}"];

    $category = Category::factory()->create();
    $this->product = Product::factory()->create(['category_id' => $category->id]);
    $this->variant = ProductVariant::factory()->create([
        'product_id' => $this->product->id,
        'price' => 25.00,
        'stock' => 100,
    ]);
});

it('creates an order via api', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson('/api/orders', [
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
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['id', 'order_number', 'total_amount', 'status']);
});

it('lists orders via api', function () {
    Order::factory(3)->create();

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/orders');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

it('filters orders by status via api', function () {
    Order::factory()->create(['status' => 'pending']);
    Order::factory()->create(['status' => 'completed']);

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/orders?status=pending');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

it('shows order details via api', function () {
    $order = Order::factory()->create();

    $response = $this->withHeaders($this->headers)
        ->getJson("/api/orders/{$order->id}");

    $response->assertStatus(200)
        ->assertJsonStructure(['id', 'order_number', 'total_amount', 'status']);
});

it('cancels an order via api', function () {
    $order = Order::factory()->create(['status' => 'pending', 'payment_status' => 'pending']);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/orders/{$order->id}/cancel", [
            'reason' => 'Cliente no vino',
        ]);

    $response->assertStatus(200);
});

it('requires reason for cancellation via api', function () {
    $order = Order::factory()->create(['status' => 'pending']);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/orders/{$order->id}/cancel", []);

    $response->assertStatus(422);
});

it('marks order as paid via api', function () {
    $order = Order::factory()->create([
        'status' => 'pending',
        'payment_status' => 'pending',
        'payment_method' => 'cash',
        'total_amount' => 50.00,
    ]);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/orders/{$order->id}/payment", [
            'payment_method' => 'cash',
        ]);

    $response->assertStatus(200);
});

it('updates order status via api', function () {
    $order = Order::factory()->create([
        'status' => 'pending',
        'payment_status' => 'paid',
    ]);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/orders/{$order->id}/status", [
            'status' => 'cooking',
        ]);

    $response->assertStatus(200);
});

it('rejects invalid status transition via api', function () {
    $order = Order::factory()->create([
        'status' => 'completed',
        'payment_status' => 'paid',
    ]);

    $response = $this->withHeaders($this->headers)
        ->patchJson("/api/orders/{$order->id}/status", [
            'status' => 'pending',
        ]);

    $response->assertStatus(422);
});
