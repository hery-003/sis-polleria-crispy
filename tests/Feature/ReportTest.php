<?php

use App\Models\Order;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;

beforeEach(function () {
    $this->reportService = app(ReportService::class);
    $this->user = User::factory()->create(['role' => 'admin']);
});

it('returns empty stats when no orders exist', function () {
    $stats = $this->reportService->getStats(
        Carbon::now()->subDay(),
        Carbon::now()->addDay()
    );

    expect($stats['totalSales'])->toBe(0.0)
        ->and($stats['ordersCount'])->toBe(0)
        ->and($stats['cancellations'])->toBe(0);
});

it('calculates total sales correctly', function () {
    Order::factory()->paid()->create(['total_amount' => 100.00]);
    Order::factory()->paid()->create(['total_amount' => 50.00]);

    $stats = $this->reportService->getStats(
        Carbon::now()->subDay(),
        Carbon::now()->addDay()
    );

    expect($stats['totalSales'])->toBe(150.00)
        ->and($stats['ordersCount'])->toBe(2);
});

it('counts cancellations separately', function () {
    Order::factory()->paid()->create(['total_amount' => 100.00]);
    Order::factory()->cancelled()->create(['total_amount' => 50.00]);

    $stats = $this->reportService->getStats(
        Carbon::now()->subDay(),
        Carbon::now()->addDay()
    );

    expect($stats['cancellations'])->toBe(1);
});

it('calculates net income correctly', function () {
    Order::factory()->paid()->create(['total_amount' => 200.00]);

    $stats = $this->reportService->getStats(
        Carbon::now()->subDay(),
        Carbon::now()->addDay()
    );

    expect($stats['netIncome'])->toBe(200.00);
});

it('deducts refunds from net income', function () {
    Order::factory()->paid()->create(['total_amount' => 200.00]);
    Order::factory()->cancelled()->create([
        'total_amount' => 50.00,
        'payment_status' => 'refunded',
    ]);

    $stats = $this->reportService->getStats(
        Carbon::now()->subDay(),
        Carbon::now()->addDay()
    );

    expect($stats['netIncome'])->toBe(150.00);
});

it('calculates average ticket', function () {
    Order::factory()->paid()->create(['total_amount' => 100.00]);
    Order::factory()->paid()->create(['total_amount' => 50.00]);

    $stats = $this->reportService->getStats(
        Carbon::now()->subDay(),
        Carbon::now()->addDay()
    );

    expect($stats['avgTicket'])->toBe(75.00);
});

it('caches report results', function () {
    $stats1 = $this->reportService->getStats(Carbon::now()->startOfDay(), Carbon::now()->endOfDay());

    Order::factory()->paid()->create(['total_amount' => 999.00]);

    $stats2 = $this->reportService->getStats(Carbon::now()->startOfDay(), Carbon::now()->endOfDay());

    // Should be cached, same as before
    expect($stats1)->toEqual($stats2);
});

it('clears cache correctly', function () {
    $this->reportService->getStats(Carbon::now()->startOfDay(), Carbon::now()->endOfDay());

    Order::factory()->paid()->create(['total_amount' => 999.00]);

    $this->reportService->clearCache();

    $stats = $this->reportService->getStats(Carbon::now()->startOfDay(), Carbon::now()->endOfDay());

    expect($stats['totalSales'])->toBe(999.00);
});
