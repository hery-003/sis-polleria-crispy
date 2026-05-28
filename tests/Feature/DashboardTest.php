<?php

use App\Models\User;

it('shows dashboard to authenticated user', function () {
    $user = User::factory()->create(['role' => 'admin']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertStatus(200);
});

it('redirects guest to login', function () {
    $response = $this->get(route('dashboard'));

    $response->assertRedirect(route('login'));
});

it('shows pos to cashier', function () {
    $user = User::factory()->create(['role' => 'cashier']);

    $this->actingAs($user);
    $this->assertAuthenticated();

    $this->get(route('pos.index'))
        ->assertStatus(200);
});

it('shows kitchen to all authenticated', function () {
    $user = User::factory()->create(['role' => 'waiter']);

    $this->actingAs($user)
        ->get(route('kitchen.index'))
        ->assertStatus(200);
});

it('redirects waiter from admin-only routes', function () {
    $user = User::factory()->create(['role' => 'waiter']);

    $this->actingAs($user)
        ->get(route('cash-register.index'))
        ->assertRedirect();
});

it('shows waiter view to authenticated user', function () {
    $user = User::factory()->create(['role' => 'waiter']);

    $this->actingAs($user)
        ->get(route('waiter.index'))
        ->assertStatus(200);
});

it('shows empty waiter view when no orders', function () {
    $user = User::factory()->create(['role' => 'waiter']);

    $this->actingAs($user)
        ->get(route('waiter.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Waiter/Index')
            ->has('orders', 0)
        );
});
