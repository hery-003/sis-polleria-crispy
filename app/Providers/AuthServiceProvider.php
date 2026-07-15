<?php

namespace App\Providers;

use App\Models\CashRegister;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Policies\CashRegisterPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Order::class => OrderPolicy::class,
        Product::class => ProductPolicy::class,
        User::class => UserPolicy::class,
        CashRegister::class => CashRegisterPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
