<?php

use App\Http\Controllers\Api\V1\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\V1\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\V1\Auth\TokenController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PurchaseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

 
    Route::middleware('throttle:api-public')->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/{product:slug}', [ProductController::class, 'show'])->name('products.show');
        Route::get('products/{product:slug}/recommendations', [ProductController::class, 'recommendations'])->name('products.recommendations');
    });

   
    Route::middleware('throttle:api-auth')->group(function () {
        Route::post('auth/login', [TokenController::class, 'store'])->name('auth.login');
    });

   
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('auth/logout', [TokenController::class, 'destroy'])->name('auth.logout');

        Route::middleware('throttle:api-user')->group(function () {
            Route::get('orders', [OrderController::class, 'index'])->name('orders.index');

                Route::middleware('throttle:api-purchase')
                ->post('products/{product:slug}/buy', [PurchaseController::class, 'store'])
                ->name('products.buy');
        });

        Route::middleware(['can:admin', 'throttle:api-user'])->prefix('admin')->name('admin.')->group(function () {
            Route::apiResource('products', AdminProductController::class);
            Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        });
    });
});
