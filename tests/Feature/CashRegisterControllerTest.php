<?php

use App\Models\CashRegister;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

it('renders the cash register page', function () {
    CashRegister::factory()->create([
        'opened_at' => now(),
        'closed_at' => null,
    ]);

    $response = $this->get(route('cash-register.index'));

    $response->assertStatus(200);
});

it('opens a new cash register', function () {
    $response = $this->post(route('cash-register.open'), [
        'opening_balance' => 500.00,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('cash_registers', [
        'opening_balance' => 500.00,
        'closed_at' => null,
    ]);
});

it('prevents opening a register when one is already open', function () {
    CashRegister::factory()->create([
        'opened_at' => now(),
        'closed_at' => null,
    ]);

    $response = $this->post(route('cash-register.open'), [
        'opening_balance' => 100.00,
    ]);

    $response->assertSessionHas('error');
});

it('closes an open cash register', function () {
    $register = CashRegister::factory()->create([
        'opening_balance' => 500,
        'opened_at' => now(),
        'closed_at' => null,
    ]);

    $response = $this->post(route('cash-register.close'), [
        'cash_register_id' => $register->id,
        'closing_balance' => 1500.00,
        'notes' => 'Cierre de prueba',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('cash_registers', [
        'id' => $register->id,
        'closing_balance' => 1500.00,
    ]);
});

it('validates closing balance is positive', function () {
    $register = CashRegister::factory()->create([
        'opening_balance' => 500,
        'opened_at' => now(),
        'closed_at' => null,
    ]);

    $response = $this->post(route('cash-register.close'), [
        'cash_register_id' => $register->id,
        'closing_balance' => -100,
    ]);

    $response->assertSessionHasErrors(['closing_balance']);
});

it('stores a cash movement (income)', function () {
    $register = CashRegister::factory()->create([
        'opened_at' => now(),
        'closed_at' => null,
    ]);

    $response = $this->post(route('cash-register.movement'), [
        'type' => 'in',
        'amount' => 200.00,
        'description' => 'Venta adicional',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('cash_movements', [
        'cash_register_id' => $register->id,
        'type' => 'in',
        'amount' => 200.00,
    ]);
});

it('stores a cash movement (expense)', function () {
    $register = CashRegister::factory()->create([
        'opened_at' => now(),
        'closed_at' => null,
    ]);

    $response = $this->post(route('cash-register.movement'), [
        'type' => 'out',
        'amount' => 50.00,
        'description' => 'Compra de insumos',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('cash_movements', [
        'cash_register_id' => $register->id,
        'type' => 'out',
        'amount' => 50.00,
    ]);
});

it('validates movement type is in or out', function () {
    CashRegister::factory()->create([
        'opened_at' => now(),
        'closed_at' => null,
    ]);

    $response = $this->post(route('cash-register.movement'), [
        'type' => 'invalid',
        'amount' => 100,
        'description' => 'Test',
    ]);

    $response->assertSessionHasErrors(['type']);
});

it('gets register summary', function () {
    $register = CashRegister::factory()->create([
        'opening_balance' => 500,
        'opened_at' => now()->subHours(4),
        'closed_at' => null,
    ]);

    $response = $this->get(route('cash-register.summary', $register));

    $response->assertStatus(200);
    $response->assertJsonIsObject();
});

it('denies cashier access to cash register', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $this->actingAs($cashier);

    $response = $this->get(route('cash-register.index'));

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('error');
});

it('renders page with register history', function () {
    CashRegister::factory()->count(3)->closed()->create([
        'opened_at' => now()->subDays(1),
        'closed_at' => now(),
    ]);

    $response = $this->get(route('cash-register.index'));

    $response->assertStatus(200);
});
