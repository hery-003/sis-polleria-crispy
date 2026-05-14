<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->defineGates();
        Vite::prefetch(concurrency: 3);

        // Invalido la caché del POS cuando cambian productos o categorías
        $posClear = function ($model) {
            \Illuminate\Support\Facades\Cache::forget('pos_categories');
            
            // Si el modelo es una variante, verificamos el stock para alertas
            if ($model instanceof \App\Models\ProductVariant) {
                if ($model->stock !== null && $model->stock <= $model->stock_threshold) {
                    \App\Events\LowStockAlert::dispatch($model);
                }
            }
        };
        \App\Models\Product::saved(fn() => \Illuminate\Support\Facades\Cache::forget('pos_categories'));
        \App\Models\Product::deleted(fn() => \Illuminate\Support\Facades\Cache::forget('pos_categories'));
        \App\Models\Category::saved(fn() => \Illuminate\Support\Facades\Cache::forget('pos_categories'));
        \App\Models\Category::deleted(fn() => \Illuminate\Support\Facades\Cache::forget('pos_categories'));
        
        \App\Models\ProductVariant::saved($posClear);
        \App\Models\ProductVariant::deleted(fn() => \Illuminate\Support\Facades\Cache::forget('pos_categories'));

        // Invalido la caché de métodos de pago
        \App\Models\MetodoPago::saved(fn() => \Illuminate\Support\Facades\Cache::forget('pos_metodos_pago'));
        \App\Models\MetodoPago::deleted(fn() => \Illuminate\Support\Facades\Cache::forget('pos_metodos_pago'));

        // Invalido la caché de mesas
        \App\Models\Mesa::saved(fn() => \Illuminate\Support\Facades\Cache::forget('pos_mesas'));
        \App\Models\Mesa::deleted(fn() => \Illuminate\Support\Facades\Cache::forget('pos_mesas'));

        // Invalido la caché del Dashboard cuando hay movimientos
        $dashboardClear = function () {
            \Illuminate\Support\Facades\Cache::forget('dashboard_data');
        };
        \App\Models\Order::saved($dashboardClear);
        \App\Models\Order::deleted($dashboardClear);
        \App\Models\CashRegister::saved($dashboardClear);
        \App\Models\CashMovement::saved($dashboardClear);
    }

    protected function defineGates(): void
    {
        Gate::define('manage-orders', fn($user) => in_array($user->role, ['admin', 'cashier']));
        Gate::define('manage-products', fn($user) => in_array($user->role, ['admin', 'cashier']));
        Gate::define('manage-clients', fn($user) => in_array($user->role, ['admin', 'cashier']));
        Gate::define('manage-users', fn($user) => $user->role === 'admin');
        Gate::define('manage-cash-register', fn($user) => $user->role === 'admin');
        Gate::define('manage-reports', fn($user) => $user->role === 'admin');
        Gate::define('manage-mesas', fn($user) => $user->role === 'admin');
        Gate::define('manage-metodos-pago', fn($user) => $user->role === 'admin');
        Gate::define('manage-audit-logs', fn($user) => $user->role === 'admin');
        Gate::define('view-kitchen', fn($user) => in_array($user->role, ['admin', 'cashier', 'waiter']));
        Gate::define('view-waiter', fn($user) => in_array($user->role, ['admin', 'cashier', 'waiter']));
    }
}
