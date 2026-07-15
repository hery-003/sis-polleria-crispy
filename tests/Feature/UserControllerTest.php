<?php

use App\Models\AuditLog;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

it('renders the users page', function () {
    User::factory(3)->create();

    $response = $this->get(route('users.index'));

    $response->assertStatus(200);
});

it('creates a new user', function () {
    $response = $this->post(route('users.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'cashier',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

it('deletes a user with audit log', function () {
    $user = User::factory()->create();

    $response = $this->delete(route('users.destroy', $user));

    $response->assertSessionHas('success');
    $this->assertSoftDeleted($user);
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'user_deleted',
    ]);
});

it('prevents self-deletion', function () {
    $response = $this->delete(route('users.destroy', $this->admin));

    $response->assertSessionHas('error');
});

it('restores a soft-deleted user', function () {
    $user = User::factory()->create(['deleted_at' => now()]);

    $response = $this->patch(route('users.restore', $user->id));

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('audit_logs', [
        'action' => 'user_restored',
    ]);
});
