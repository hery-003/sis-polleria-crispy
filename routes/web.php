<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CancellationController;
use App\Http\Controllers\CashRegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaiterController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {

    // POS - Cajeros y Admin
    Route::middleware(['role:admin,cashier'])->group(function () {
        Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
        Route::post('/pos', [POSController::class, 'store'])->name('pos.store');
    });

    // Cocina - Admin, Cajeros y Meseros
    Route::middleware(['role:admin,cashier,waiter'])->group(function () {
        Route::get('/kitchen', [KitchenController::class, 'index'])->name('kitchen.index');
        Route::patch('/kitchen/{order}/status', [KitchenController::class, 'updateStatus'])->name('kitchen.updateStatus');
    });

    // Mesero - Admin, Cajeros y Meseros
    Route::middleware(['role:admin,cashier,waiter'])->group(function () {
        Route::get('/waiter', [WaiterController::class, 'index'])->name('waiter.index');
        Route::post('/waiter/{order}/deliver', [WaiterController::class, 'markDelivered'])->name('waiter.deliver');
    });

    // Caja - Solo Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/cash-register', [CashRegisterController::class, 'index'])->name('cash-register.index');
        Route::post('/cash-register/open', [CashRegisterController::class, 'open'])->name('cash-register.open');
        Route::post('/cash-register/close', [CashRegisterController::class, 'close'])->name('cash-register.close');
        Route::post('/cash-register/movement', [CashRegisterController::class, 'storeMovement'])->name('cash-register.movement');
        Route::get('/cash-register/{register}/summary', [CashRegisterController::class, 'getSummary'])->name('cash-register.summary');
        Route::get('/cash-register/{register}/pdf', [CashRegisterController::class, 'exportPdf'])->name('cash-register.pdf');
    });

    // Reportes - Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export-pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
        Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    });

    // Imprimir pedido - Admin, Cajeros y Meseros
    Route::middleware(['role:admin,cashier,waiter', 'throttle:30,1'])->group(function () {
        Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
        Route::get('/orders/{order}/receipt', [PrintController::class, 'receipt'])->name('orders.receipt');
        Route::get('/orders/{order}/reprint', [PrintController::class, 'reprint'])->name('orders.reprint');
        Route::get('/orders/{order}/kitchen', [PrintController::class, 'kitchen'])->name('orders.kitchen');
        Route::get('/orders/{order}/pdf', [PrintController::class, 'pdf'])->name('orders.pdf');
        Route::post('/orders/{order}/auto-print', [PrintController::class, 'autoPrint'])->name('orders.auto-print');
    });

    // Usuarios - Solo Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    });

    // Productos - Admin y Cajeros
    Route::middleware(['role:admin,cashier'])->group(function () {
        Route::get('/products/stock/bulk', [ProductController::class, 'stockIndex'])->name('products.stock');
        Route::post('/products/stock/bulk', [ProductController::class, 'stockUpdate'])->name('products.stock.update');
        Route::resource('products', ProductController::class);
    });

    // Mesas - Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/mesas', [MesaController::class, 'index'])->name('mesas.index');
        Route::post('/mesas', [MesaController::class, 'store'])->name('mesas.store');
        Route::put('/mesas/{mesa}', [MesaController::class, 'update'])->name('mesas.update');
        Route::delete('/mesas/{mesa}', [MesaController::class, 'destroy'])->name('mesas.destroy');
    });

    // Metodos de pago - Solo Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/metodos-pago', [MetodoPagoController::class, 'index'])->name('metodos-pago.index');
        Route::post('/metodos-pago', [MetodoPagoController::class, 'store'])->name('metodos-pago.store');
        Route::put('/metodos-pago/{metodoPago}', [MetodoPagoController::class, 'update'])->name('metodos-pago.update');
        Route::delete('/metodos-pago/{metodoPago}', [MetodoPagoController::class, 'destroy'])->name('metodos-pago.destroy');
    });

    // Categorías - Admin y Cajeros
    Route::middleware(['role:admin,cashier'])->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::patch('/categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
    });

    // Auditoria - Solo Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // Cancelaciones - Solo Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/cancellations', [CancellationController::class, 'index'])->name('cancellations.index');
    });

    // Configuración - Solo Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });

    // Clientes - Admin y Cajeros
    Route::middleware(['role:admin,cashier'])->group(function () {
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
        Route::get('/clients/search', [ClientController::class, 'search'])->name('clients.search');
        Route::get('/clients/{client}/orders', [ClientController::class, 'orders'])->name('clients.orders');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Página 404 personalizada
Route::fallback(function () {
    return Inertia::render('Error404')->toResponse(request())->setStatusCode(404);
});
