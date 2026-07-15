<?php

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

it('renders the categories page', function () {
    Category::factory(3)->create();

    $response = $this->get(route('categories.index'));

    $response->assertStatus(200);
});

it('creates a new category', function () {
    $response = $this->post(route('categories.store'), [
        'name' => 'Nueva Categoría',
        'slug' => 'nueva-categoria',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('categories', ['name' => 'Nueva Categoría', 'slug' => 'nueva-categoria']);
});

it('validates required fields on store', function () {
    $response = $this->post(route('categories.store'), []);

    $response->assertSessionHasErrors(['name', 'slug']);
});

it('validates unique slug on store', function () {
    Category::factory()->create(['slug' => 'ya-existe']);

    $response = $this->post(route('categories.store'), [
        'name' => 'Test',
        'slug' => 'ya-existe',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

it('updates a category', function () {
    $category = Category::factory()->create(['name' => 'Original', 'slug' => 'original']);

    $response = $this->put(route('categories.update', $category), [
        'name' => 'Actualizada',
        'slug' => 'original',
        'is_active' => true,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('categories', ['name' => 'Actualizada']);
});

it('soft-deletes a category with audit log', function () {
    $category = Category::factory()->create();

    $response = $this->delete(route('categories.destroy', $category));

    $response->assertSessionHas('success');
    $this->assertSoftDeleted($category);
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'category_deleted',
    ]);
});

it('restores a soft-deleted category', function () {
    $category = Category::factory()->create(['deleted_at' => now()]);

    $response = $this->patch(route('categories.restore', $category->id));

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'category_restored',
    ]);
    $this->assertDatabaseHas('categories', ['id' => $category->id, 'deleted_at' => null]);
});

it('renders page with deleted categories', function () {
    Category::factory()->create(['name' => 'Visible', 'deleted_at' => now()]);
    Category::factory()->create(['name' => 'Activa']);

    $response = $this->get(route('categories.index'));

    $response->assertStatus(200);
});

it('allows cashier to view categories', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $this->actingAs($cashier);

    $response = $this->get(route('categories.index'));

    $response->assertStatus(200);
});

it('denies waiter access to categories', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $this->actingAs($waiter);

    $response = $this->get(route('categories.index'));

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('error');
});
