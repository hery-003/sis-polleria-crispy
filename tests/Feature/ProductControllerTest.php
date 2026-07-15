<?php

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
    $this->category = Category::factory()->create();
});

it('renders the products page', function () {
    Product::factory(3)->create();

    $response = $this->get(route('products.index'));

    $response->assertStatus(200);
});

it('renders the create product page', function () {
    $response = $this->get(route('products.create'));

    $response->assertStatus(200);
});

it('creates a product with variants', function () {
    $response = $this->post(route('products.store'), [
        'name' => 'Pollo Entero',
        'slug' => 'pollo-entero',
        'description' => 'Delicioso pollo entero',
        'category_id' => $this->category->id,
        'is_active' => true,
        'variants' => [
            ['name' => 'Entero', 'price' => 75.00, 'is_active' => true],
            ['name' => 'Medio', 'price' => 40.00, 'is_active' => true],
        ],
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('products', ['name' => 'Pollo Entero', 'slug' => 'pollo-entero']);
    $this->assertDatabaseHas('product_variants', ['name' => 'Entero', 'price' => 75.00]);
    $this->assertDatabaseHas('product_variants', ['name' => 'Medio', 'price' => 40.00]);
});

it('validates required fields on store', function () {
    $response = $this->post(route('products.store'), []);

    $response->assertSessionHasErrors(['name', 'category_id']);
});

it('creates product with image', function () {
    $file = UploadedFile::fake()->image('producto.jpg', 300, 300);

    $response = $this->post(route('products.store'), [
        'name' => 'Con Imagen',
        'slug' => 'con-imagen',
        'category_id' => $this->category->id,
        'is_active' => true,
        'image' => $file,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('products', ['name' => 'Con Imagen', 'slug' => 'con-imagen']);
});

it('shows a product edit page', function () {
    $product = Product::factory()->create();

    $response = $this->get(route('products.edit', $product));

    $response->assertStatus(200);
});

it('updates a product', function () {
    $product = Product::factory()->create(['name' => 'Original', 'slug' => 'original']);

    $response = $this->put(route('products.update', $product), [
        'name' => 'Actualizado',
        'slug' => 'original',
        'category_id' => $this->category->id,
        'is_active' => true,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('products', ['name' => 'Actualizado']);
});

it('updates a product with variants', function () {
    $product = Product::factory()->create(['slug' => 'test-product']);
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'name' => 'Vieja Variante',
        'price' => 50.00,
    ]);

    $response = $this->put(route('products.update', $product), [
        'name' => 'Actualizado',
        'slug' => 'test-product',
        'category_id' => $this->category->id,
        'is_active' => true,
        'variants' => [
            ['id' => $variant->id, 'name' => 'Nueva Variante', 'price' => 60.00, 'is_active' => true],
        ],
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('product_variants', ['name' => 'Nueva Variante', 'price' => 60.00]);
    $this->assertDatabaseMissing('product_variants', ['name' => 'Vieja Variante']);
});

it('soft-deletes a product with audit log', function () {
    $product = Product::factory()->create();

    $response = $this->delete(route('products.destroy', $product));

    $response->assertSessionHas('success');
    $this->assertSoftDeleted($product);
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'product_deleted',
    ]);
});

it('renders stock bulk page', function () {
    Product::factory(2)->hasVariants(2)->create();

    $response = $this->get(route('products.stock'));

    $response->assertStatus(200);
});

it('updates stock in bulk', function () {
    $product = Product::factory()->create();
    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
        'stock' => 10,
    ]);

    $response = $this->post(route('products.stock.update'), [
        'updates' => [
            ['id' => $variant->id, 'stock' => 25],
        ],
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 25]);
});

it('validates stock bulk update', function () {
    $response = $this->post(route('products.stock.update'), [
        'updates' => [
            ['id' => 9999, 'stock' => 5],
        ],
    ]);

    $response->assertSessionHasErrors(['updates.0.id']);
});

it('allows cashier to view products', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $this->actingAs($cashier);

    $response = $this->get(route('products.index'));

    $response->assertStatus(200);
});
