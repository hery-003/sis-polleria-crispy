<?php

namespace App\Providers;

use App\Events\LowStockAlert;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Category;
use App\Models\Client;
use App\Models\Mesa;
use App\Models\MetodoPago;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Observers\AuditObserver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Vite;
use App\Jobs\ProcessOrderFulfillment;
use App\Jobs\ProcessOrderPrinting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Laravel\Pulse\Pulse;

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
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal());

        $this->defineGates();
        $this->configurePulse();
        $this->configureQueueRouting();
        Vite::prefetch(concurrency: 3);

        // Invalido la caché del POS cuando cambian productos o categorías
        $posClear = function ($model) {
            Cache::forget('pos_categories');

            // Si el modelo es una variante, verificamos el stock para alertas
            if ($model instanceof ProductVariant) {
                if ($model->stock !== null && $model->stock <= $model->stock_threshold) {
                    LowStockAlert::dispatch($model);
                }
            }
        };
        Product::saved(fn () => Cache::forget('pos_categories'));
        Product::deleted(fn () => Cache::forget('pos_categories'));
        Category::saved(fn () => Cache::forget('pos_categories'));
        Category::deleted(fn () => Cache::forget('pos_categories'));

        ProductVariant::saved($posClear);
        ProductVariant::deleted(fn () => Cache::forget('pos_categories'));

        // Invalido la caché de métodos de pago
        MetodoPago::saved(fn () => Cache::forget('pos_metodos_pago'));
        MetodoPago::deleted(fn () => Cache::forget('pos_metodos_pago'));

        // Invalido la caché de mesas
        Mesa::saved(fn () => Cache::forget('pos_mesas'));
        Mesa::deleted(fn () => Cache::forget('pos_mesas'));

        // Auditoría automática para modelos que no tienen audit logging manual
        Mesa::observe(AuditObserver::class);
        MetodoPago::observe(AuditObserver::class);
        Client::observe(AuditObserver::class);
        CashRegister::observe(AuditObserver::class);
        CashMovement::observe(AuditObserver::class);
        Product::observe(AuditObserver::class);
        Category::observe(AuditObserver::class);
        ProductVariant::observe(AuditObserver::class);

        // Invalido la caché del Dashboard cuando hay movimientos
        $dashboardClear = function () {
            Cache::forget('dashboard_data');
        };
        Order::saved($dashboardClear);
        Order::deleted($dashboardClear);
        CashRegister::saved($dashboardClear);
        CashMovement::saved($dashboardClear);
    }

    protected function defineGates(): void
    {
        Gate::define('manage-orders', fn ($user) => in_array($user->role, ['admin', 'cashier']));
        Gate::define('manage-products', fn ($user) => in_array($user->role, ['admin', 'cashier']));
        Gate::define('manage-clients', fn ($user) => in_array($user->role, ['admin', 'cashier']));
        Gate::define('manage-users', fn ($user) => $user->role === 'admin');
        Gate::define('manage-cash-register', fn ($user) => $user->role === 'admin');
        Gate::define('manage-reports', fn ($user) => $user->role === 'admin');
        Gate::define('manage-mesas', fn ($user) => $user->role === 'admin');
        Gate::define('manage-metodos-pago', fn ($user) => $user->role === 'admin');
        Gate::define('manage-audit-logs', fn ($user) => $user->role === 'admin');
        Gate::define('view-kitchen', fn ($user) => in_array($user->role, ['admin', 'cashier', 'waiter']));
        Gate::define('view-waiter', fn ($user) => in_array($user->role, ['admin', 'cashier', 'waiter']));
        Gate::define('viewPulse', fn ($user) => $user->role === 'admin');
    }

    protected function configureQueueRouting(): void
    {
        Queue::route(ProcessOrderFulfillment::class, connection: 'database', queue: 'default');
        Queue::route(ProcessOrderPrinting::class, connection: 'database', queue: 'printing');
    }

    protected function configurePulse(): void
    {
        $pulse = app(Pulse::class);

        $pulse->user(fn ($user) => [
            'name' => $user->name,
            'extra' => $user->role,
            'avatar' => null,
        ]);

        $pulse->users(function ($ids) {
            return User::whereIn('id', $ids)->get()->mapWithKeys(fn ($user) => [
                $user->id => [
                    'name' => $user->name,
                    'extra' => $user->role,
                    'avatar' => null,
                ],
            ]);
        });
    }
}
