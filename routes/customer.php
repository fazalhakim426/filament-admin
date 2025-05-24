<?php

use App\Http\Controllers\Api\Customer\PlaceOrderController;
use App\Http\Controllers\Api\Customer\ProductController;
use App\Http\Controllers\Api\Customer\ReviewController;
use Illuminate\Support\Facades\Route; 



Route::middleware(['auth:sanctum'])->prefix('customer')->group(function () {
    Route::apiResource('products', ProductController::class);
    Route::apiResource('orders', PlaceOrderController::class)->only('index', 'show', 'store', 'destroy');
    Route::post('orders/{order}/update-product-quantity', [PlaceOrderController::class, 'updateProductQuantity']);
    Route::delete('order-items/{order-item}', [PlaceOrderController::class, 'removeItems']);
    Route::post('reviews', [ReviewController::class, 'store']);
});
