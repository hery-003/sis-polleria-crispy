<?php

use App\Models\Setting;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

it('renders the settings page', function () {
    $response = $this->get(route('settings.index'));

    $response->assertStatus(200);
});

it('updates settings', function () {
    $response = $this->put(route('settings.update'), [
        'app_name' => 'Mi Pollería',
        'session_lifetime' => 120,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('settings', ['key' => 'app_name', 'value' => 'Mi Pollería']);
    $this->assertDatabaseHas('settings', ['key' => 'session_lifetime', 'value' => '120']);
});

it('validates session_lifetime range', function () {
    $response = $this->put(route('settings.update'), [
        'app_name' => 'Test',
        'session_lifetime' => 0,
    ]);

    $response->assertSessionHasErrors(['session_lifetime']);
});

it('validates session_lifetime max', function () {
    $response = $this->put(route('settings.update'), [
        'app_name' => 'Test',
        'session_lifetime' => 9999,
    ]);

    $response->assertSessionHasErrors(['session_lifetime']);
});

it('validates app_name is required', function () {
    $response = $this->put(route('settings.update'), [
        'session_lifetime' => 60,
    ]);

    $response->assertSessionHasErrors(['app_name']);
});

it('denies cashier access to settings', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $this->actingAs($cashier);

    $response = $this->get(route('settings.index'));

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('error');
});

it('overwrites existing settings on update', function () {
    Setting::setValue('app_name', 'Original');

    $this->put(route('settings.update'), [
        'app_name' => 'Actualizado',
        'session_lifetime' => 60,
    ]);

    $this->assertDatabaseHas('settings', ['key' => 'app_name', 'value' => 'Actualizado']);
    $this->assertDatabaseMissing('settings', ['key' => 'app_name', 'value' => 'Original']);
});
