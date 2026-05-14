<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'test@crispy.com',
        'password' => bcrypt('password'),
        'role' => 'cashier',
    ]);

    config()->set('app.admin_registration_token', 'test-token');
});

it('logs in via api', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'test@crispy.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['user', 'token']);
});

it('rejects invalid credentials via api', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'test@crispy.com',
        'password' => 'wrong_password',
    ]);

    $response->assertStatus(401)
        ->assertJson(['message' => 'Credenciales inválidas']);
});

it('registers a new user via api', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'New User',
        'email' => 'new@crispy.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'admin_token' => 'test-token',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['user', 'token']);

    expect($response->json('user.role'))->toBe('cashier');
});

it('accesses protected route with valid token', function () {
    $token = $this->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJson(['email' => 'test@crispy.com']);
});

it('rejects request without token', function () {
    $response = $this->getJson('/api/user');

    $response->assertStatus(401);
});

it('logs out via api', function () {
    $token = $this->user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', "Bearer {$token}")
        ->postJson('/api/logout');

    $response->assertStatus(200)
        ->assertJson(['message' => 'Sesión cerrada']);

    expect($this->user->tokens()->count())->toBe(0);
});

it('has role middleware protection', function () {
    $waiter = User::factory()->create(['role' => 'waiter']);
    $token = $waiter->createToken('test')->plainTextToken;

    // Waiters should not access admin areas via web routes
    $this->actingAs($waiter)
        ->get(route('cash-register.index'))
        ->assertRedirect();
});

it('allows only admin to cash register', function () {
    $this->actingAs($this->user)
        ->get(route('cash-register.index'))
        ->assertRedirect(); // cashier, not admin
});

it('allows admin access to cash register', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)
        ->get(route('cash-register.index'))
        ->assertStatus(200);
});
