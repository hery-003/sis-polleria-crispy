<?php

use App\Models\Client;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

it('renders the clients page', function () {
    Client::factory(3)->create();

    $response = $this->get(route('clients.index'));

    $response->assertStatus(200);
});

it('creates a new client', function () {
    $response = $this->post(route('clients.store'), [
        'name' => 'Juan Pérez',
        'phone' => '77712345',
        'email' => 'juan@example.com',
        'document_number' => '1234567',
        'address' => 'Av. Principal #123',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('clients', ['name' => 'Juan Pérez', 'phone' => '77712345']);
});

it('validates required name on store', function () {
    $response = $this->post(route('clients.store'), []);

    $response->assertSessionHasErrors(['name']);
});

it('updates a client', function () {
    $client = Client::factory()->create(['name' => 'Original']);

    $response = $this->put(route('clients.update', $client), [
        'name' => 'Actualizado',
        'phone' => '77799999',
        'email' => 'actualizado@example.com',
        'document_number' => '7654321',
        'address' => 'Av. Nueva',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('clients', ['name' => 'Actualizado', 'phone' => '77799999']);
});

it('soft-deletes a client', function () {
    $client = Client::factory()->create();

    $response = $this->delete(route('clients.destroy', $client));

    $response->assertSessionHas('success');
    $this->assertSoftDeleted($client);
});

it('searches clients by name', function () {
    Client::factory()->create(['name' => 'Carlos Lopez']);
    Client::factory()->create(['name' => 'Maria Garcia']);

    $response = $this->get(route('clients.search', ['q' => 'Carlos']));

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'Carlos Lopez']);
    $response->assertJsonMissing(['name' => 'Maria Garcia']);
});

it('searches clients by phone', function () {
    Client::factory()->create(['name' => 'Pedro', 'phone' => '77711111']);

    $response = $this->get(route('clients.search', ['q' => '77711111']));

    $response->assertStatus(200);
    $response->assertJsonFragment(['name' => 'Pedro']);
});

it('returns client orders', function () {
    $client = Client::factory()->create();

    $response = $this->get(route('clients.orders', $client));

    $response->assertStatus(200);
    $response->assertJsonIsArray();
});

it('denies waiter access to clients page', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $this->actingAs($waiter);

    $response = $this->get(route('clients.index'));

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('error');
});
