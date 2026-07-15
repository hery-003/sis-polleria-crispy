<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CashRegisterController;
use App\Http\Controllers\Api\MesaController;
use App\Http\Controllers\Api\MetodoPagoController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Products - lectura todos, escritura admin/cashier
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::get('/categories', [ProductController::class, 'categories']);
    Route::middleware(['role:admin,cashier'])->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    });

    // Orders - solo lectura para todos, escritura según rol
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::middleware(['role:admin,cashier,waiter'])->group(function () {
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    });
    Route::middleware(['role:admin,cashier'])->group(function () {
        Route::post('/orders', [OrderController::class, 'store']);
        Route::patch('/orders/{order}/payment', [OrderController::class, 'markPaid']);
    });

    // Cash Register - solo admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/cash-register', [CashRegisterController::class, 'index']);
        Route::post('/cash-register/open', [CashRegisterController::class, 'open']);
        Route::post('/cash-register/close', [CashRegisterController::class, 'close']);
        Route::post('/cash-register/movement', [CashRegisterController::class, 'movement']);
        Route::get('/cash-register/summary', [CashRegisterController::class, 'summary']);
    });

    // Reports - solo admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/reports', [ReportController::class, 'index']);
    });

    // Mesas - lectura todos
    Route::get('/mesas', [MesaController::class, 'index']);

    // Metodos de Pago - lectura todos
    Route::get('/metodos-pago', [MetodoPagoController::class, 'index']);
});

// API Fallback
Route::fallback(function () {
    return response()->json(['message' => 'Not Found.'], 404);
});
