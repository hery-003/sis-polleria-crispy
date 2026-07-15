<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

it('renders the reports page', function () {
    $response = $this->get(route('reports.index'));

    $response->assertStatus(200);
});

it('exports csv', function () {
    $response = $this->get(route('reports.export.csv', [
        'start_date' => now()->subMonth()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
});

it('exports pdf', function () {
    $response = $this->get(route('reports.export.pdf', [
        'start_date' => now()->subMonth()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});

it('exports excel', function () {
    $response = $this->get(route('reports.export.excel', [
        'start_date' => now()->subMonth()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]));

    $response->assertStatus(200);
});

it('exports with comparison dates', function () {
    $response = $this->get(route('reports.index', [
        'start_date' => now()->subMonth()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
        'compare_start' => now()->subMonths(2)->format('Y-m-d'),
        'compare_end' => now()->subMonth()->format('Y-m-d'),
    ]));

    $response->assertStatus(200);
});

it('denies cashier access to reports', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    $this->actingAs($cashier);

    $response = $this->get(route('reports.index'));

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('error');
});
