<?php

use App\Models\MetodoPago;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

it('renders the metodos de pago page', function () {
    MetodoPago::factory(3)->create();

    $response = $this->get(route('metodos-pago.index'));

    $response->assertStatus(200);
});

it('creates a new metodo de pago', function () {
    $response = $this->post(route('metodos-pago.store'), [
        'name' => 'QR Test',
        'slug' => 'qr-test',
        'is_active' => true,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('metodos_pago', ['name' => 'QR Test', 'slug' => 'qr-test']);
});

it('validates required fields on store', function () {
    $response = $this->post(route('metodos-pago.store'), []);

    $response->assertSessionHasErrors(['name', 'slug']);
});

it('validates unique slug on store', function () {
    MetodoPago::factory()->create(['slug' => 'ya-existe']);

    $response = $this->post(route('metodos-pago.store'), [
        'name' => 'Test',
        'slug' => 'ya-existe',
    ]);

    $response->assertSessionHasErrors(['slug']);
});

it('updates a metodo de pago', function () {
    $metodoPago = MetodoPago::factory()->create(['name' => 'Original', 'slug' => 'original']);

    $response = $this->put(route('metodos-pago.update', $metodoPago), [
        'name' => 'Actualizado',
        'slug' => 'original',
        'is_active' => true,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('metodos_pago', ['name' => 'Actualizado']);
});

it('deletes a metodo de pago', function () {
    $metodoPago = MetodoPago::factory()->create();

    $response = $this->delete(route('metodos-pago.destroy', $metodoPago));

    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('metodos_pago', ['id' => $metodoPago->id]);
});

it('denies cashier access to metodos de pago', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $this->actingAs($cashier);

    $response = $this->get(route('metodos-pago.index'));

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('error');
});
