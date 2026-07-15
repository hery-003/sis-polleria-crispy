<?php

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Order;
use App\Models\User;
use App\Services\CashRegisterService;

beforeEach(function () {
    $this->service = app(CashRegisterService::class);
    $this->user = User::factory()->create(['role' => 'admin']);
});

it('opens a cash register', function () {
    $register = $this->service->openRegister([
        'opening_balance' => 200.00,
    ], $this->user->id);

    expect($register)->toBeInstanceOf(CashRegister::class)
        ->and($register->status)->toBe('open')
        ->and((float) $register->opening_balance)->toBe(200.0)
        ->and($register->user_id)->toBe($this->user->id);
});

it('prevents opening a second register while one is open', function () {
    $this->service->openRegister(['opening_balance' => 100], $this->user->id);

    expect(fn () => $this->service->openRegister(['opening_balance' => 200], $this->user->id))
        ->toThrow(Exception::class, 'Ya existe una caja abierta');
});

it('closes a cash register', function () {
    $register = $this->service->openRegister(['opening_balance' => 100], $this->user->id);

    $closed = $this->service->closeRegister($register->id, 500.00, 'Cierre de turno');

    expect($closed->status)->toBe('closed')
        ->and((float) $closed->closing_balance)->toBe(500.0)
        ->and($closed->notes)->toBe('Cierre de turno');
});

it('calculates expected cash correctly', function () {
    $register = $this->service->openRegister(['opening_balance' => 100], $this->user->id);

    // Create a paid cash order
    $order = Order::factory()->create([
        'payment_method' => 'cash',
        'payment_status' => 'paid',
        'status' => 'completed',
        'total_amount' => 250.00,
    ]);

    $expected = $this->service->calculateExpectedCash($register->fresh());

    expect((float) $expected['opening_balance'])->toBe(100.0)
        ->and((float) $expected['expected_cash'])->toBe(350.0);
});

it('creates cash movements', function () {
    $register = $this->service->openRegister(['opening_balance' => 100], $this->user->id);

    $movement = $this->service->createMovement($register->id, [
        'type' => 'in',
        'amount' => 50.00,
        'description' => 'Ingreso extra',
    ], $this->user->id);

    expect($movement)->toBeInstanceOf(CashMovement::class)
        ->and($movement->type)->toBe('in')
        ->and((float) $movement->amount)->toBe(50.0);
});

it('prevents movements on closed register', function () {
    $register = CashRegister::factory()->closed()->create();

    expect(fn () => $this->service->createMovement($register->id, [
        'type' => 'in',
        'amount' => 50,
        'description' => 'Test',
    ], $this->user->id))->toThrow(Exception::class, 'La caja no está abierta');
});

it('calculates close summary with difference', function () {
    $register = $this->service->openRegister(['opening_balance' => 100], $this->user->id);

    $summary = $this->service->getCloseSummary($register->fresh(), 350.00);

    expect($summary)->toHaveKey('expected_cash')
        ->and($summary)->toHaveKey('actual_cash')
        ->and($summary)->toHaveKey('difference')
        ->and($summary)->toHaveKey('difference_status');
});

it('returns active register', function () {
    $register = $this->service->openRegister(['opening_balance' => 100], $this->user->id);

    $active = $this->service->getActiveRegister();

    expect($active->id)->toBe($register->id);
});

it('returns null when no active register', function () {
    expect($this->service->getActiveRegister())->toBeNull();
});
