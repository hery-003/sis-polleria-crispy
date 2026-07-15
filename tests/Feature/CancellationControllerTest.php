<?php

use App\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
    $this->withoutVite();
});

it('renders the cancellations page', function () {
    Order::factory(3)->create(['status' => 'cancelled', 'cancellation_reason' => 'Cliente no vino']);
    Order::factory(2)->create(['status' => 'completed']);

    $response = $this->get(route('cancellations.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Cancellations/Index')
        ->has('orders.data', 3)
        ->has('stats')
    );
});

it('filters cancellations by date range', function () {
    Order::factory()->create([
        'status' => 'cancelled',
        'cancellation_reason' => 'Test',
        'created_at' => now()->subDays(5),
    ]);
    Order::factory()->create([
        'status' => 'cancelled',
        'cancellation_reason' => 'Test 2',
        'created_at' => now(),
    ]);

    $response = $this->get(route('cancellations.index', [
        'date_from' => now()->subDay()->format('Y-m-d'),
        'date_to' => now()->format('Y-m-d'),
    ]));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Cancellations/Index')
        ->has('orders.data', 1)
    );
});

it('cancellation stats show correct totals', function () {
    Order::factory(3)->create(['status' => 'cancelled', 'cancellation_reason' => 'Razon A', 'total_amount' => 50]);
    Order::factory(2)->create(['status' => 'cancelled', 'cancellation_reason' => 'Razon B', 'payment_status' => 'refunded', 'total_amount' => 30]);

    $response = $this->get(route('cancellations.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Cancellations/Index')
        ->where('stats.total_cancelled', 5)
        ->has('stats.top_reasons')
    );
});
