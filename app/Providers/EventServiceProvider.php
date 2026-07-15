<?php

namespace App\Providers;

use App\Events\LowStockAlert;
use App\Events\OrderCreated;
use App\Events\OrderUpdated;
use App\Events\SecurityAlert;
use App\Listeners\LowStockAlertListener;
use App\Listeners\OrderCreatedListener;
use App\Listeners\OrderUpdatedListener;
use App\Listeners\SecurityAlertListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderCreated::class => [
            OrderCreatedListener::class,
        ],
        OrderUpdated::class => [
            OrderUpdatedListener::class,
        ],
        LowStockAlert::class => [
            LowStockAlertListener::class,
        ],
        SecurityAlert::class => [
            SecurityAlertListener::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
