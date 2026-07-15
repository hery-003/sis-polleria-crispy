<?php

use App\Models\Mesa;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

it('renders the mesas page', function () {
    Mesa::factory(4)->create();

    $response = $this->get(route('mesas.index'));

    $response->assertStatus(200);
});

it('creates a new mesa', function () {
    $response = $this->post(route('mesas.store'), [
        'name' => 'Mesa Principal',
        'number' => 1,
        'capacity' => 6,
        'is_active' => true,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('mesas', ['name' => 'Mesa Principal', 'number' => 1]);
});

it('updates a mesa', function () {
    $mesa = Mesa::factory()->create(['number' => 1]);

    $response = $this->put(route('mesas.update', $mesa), [
        'name' => 'Mesa VIP',
        'number' => 1,
        'capacity' => 4,
        'is_active' => true,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('mesas', ['name' => 'Mesa VIP']);
});

it('deletes a mesa', function () {
    $mesa = Mesa::factory()->create();

    $response = $this->delete(route('mesas.destroy', $mesa));

    $response->assertSessionHas('success');
    $this->assertSoftDeleted('mesas', ['id' => $mesa->id]);
});

it('shows mesa status correctly', function () {
    Mesa::factory()->create(['name' => 'Mesa 1', 'number' => 1, 'is_active' => true]);

    $response = $this->get(route('mesas.index'));

    $response->assertStatus(200);
    $response->assertSee('Mesa 1');
});
