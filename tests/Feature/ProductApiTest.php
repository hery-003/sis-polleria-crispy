<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Category;

beforeEach(function () {
    $this->user = User::factory()->create(['role' => 'admin']);
    $this->token = $this->user->createToken('test')->plainTextToken;
    $this->headers = ['Authorization' => "Bearer {$this->token}"];
});

it('lists products via api', function () {
    Product::factory(3)->create();

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/products');

    $response->assertStatus(200)
        ->assertJsonCount(3);
});

it('creates a product via api', function () {
    $category = Category::factory()->create();

    $response = $this->withHeaders($this->headers)
        ->postJson('/api/products', [
            'category_id' => $category->id,
            'name' => 'Pollo Nuevo',
            'slug' => 'pollo-nuevo',
            'description' => 'Delicioso pollo nuevo',
            'is_active' => true,
            'variants' => [
                ['name' => '1/4', 'price' => 25.00, 'stock' => 10],
            ],
        ]);

    $response->assertStatus(201)
        ->assertJson(['name' => 'Pollo Nuevo']);

    expect(Product::where('slug', 'pollo-nuevo')->exists())->toBeTrue();
});

it('requires name and category to create product', function () {
    $response = $this->withHeaders($this->headers)
        ->postJson('/api/products', []);

    $response->assertStatus(422);
});

it('shows a single product via api', function () {
    $product = Product::factory()->create();

    $response = $this->withHeaders($this->headers)
        ->getJson("/api/products/{$product->id}");

    $response->assertStatus(200)
        ->assertJson(['id' => $product->id]);
});

it('updates a product via api', function () {
    $product = Product::factory()->create();

    $response = $this->withHeaders($this->headers)
        ->putJson("/api/products/{$product->id}", [
            'name' => 'Pollo Actualizado',
        ]);

    $response->assertStatus(200)
        ->assertJson(['name' => 'Pollo Actualizado']);
});

it('deletes a product via api', function () {
    $product = Product::factory()->create();

    $response = $this->withHeaders($this->headers)
        ->deleteJson("/api/products/{$product->id}");

    $response->assertStatus(200);
    expect(Product::find($product->id))->toBeNull();
});

it('lists categories via api', function () {
    Category::factory(3)->create();

    $response = $this->withHeaders($this->headers)
        ->getJson('/api/categories');

    $response->assertStatus(200)
        ->assertJsonCount(3);
});
