<?php

use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

 
Route::middleware(['auth:sanctum'])->prefix('supplier')->group(function () {
    Route::apiResource('products', \App\Http\Controllers\Api\Supplier\ProductController::class);
    Route::patch('products/{product}/deactivate', [\App\Http\Controllers\Api\Supplier\ProductController::class, 'deactivate']);
    //inventory
    Route::apiResource('inventroy-movements', \App\Http\Controllers\Api\Supplier\InventoryMovementController::class);
    Route::get('{product}/inventroy-movements', [\App\Http\Controllers\Api\Supplier\InventoryMovementController::class, 'getInventoryMovement']);

    Route::get('/orders', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'index']);
    //confirm orders and thier items.
    // Route::patch('/orders/{order}/confirm', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'confirmOrder']); // Confirm order
    // Route::patch('/order-items/{order_item}/confirm', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'confirmOrderItem']); // Confirm order
    //reject orders or order items.
    // Route::patch('/orders/{order}/reject', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'rejectOrder']); // Confirm order
    // Route::patch('/order-items/{order_item}/reject', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'rejectOrderItem']); // Confirm order

    //pay and refunded orders
    // Route::patch('/orders/{order}/pay', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'payOrder']); // Confirm order
    // Route::patch('/orders/{order}/refunded', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'refundOrder']); // Confirm order


    Route::get('/change-order-status/{order}/accepted', [\App\Http\Controllers\Api\Supplier\OrderStatusController::class, 'accepted']);
    Route::get('/change-order-status/{order}/processing', [\App\Http\Controllers\Api\Supplier\OrderStatusController::class, 'processing']);
    Route::get('/change-order-status/{order}/rejected', [\App\Http\Controllers\Api\Supplier\OrderStatusController::class, 'rejected']);
    Route::get('/change-order-status/{order}/ready-to-dispatched', [\App\Http\Controllers\Api\Supplier\OrderStatusController::class, 'readyToDispatched']);
    Route::get('/change-order-status/{order}/dispatched', [\App\Http\Controllers\Api\Supplier\OrderStatusController::class, 'dispatched']);
    Route::get('/change-order-status/{order}/intransit', [\App\Http\Controllers\Api\Supplier\OrderStatusController::class, 'intransit']);
    Route::get('/change-order-status/{order}/delivered', [\App\Http\Controllers\Api\Supplier\OrderStatusController::class, 'delivered']);
    Route::get('/change-order-status/{order}/returned', [\App\Http\Controllers\Api\Supplier\OrderStatusController::class, 'returned']);
    Route::get('/change-order-status/{order}/canceled', [\App\Http\Controllers\Api\Supplier\OrderStatusController::class, 'canceled']);
    Route::get('/any-order-status/{order}/{status}', [\App\Http\Controllers\Api\Supplier\OrderStatusController::class, 'anyStatus']);
    Route::get('/orders/{order}/download-airway-bill', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'downloadAirwayBill']);


    Route::get('/reviews', [\App\Http\Controllers\Api\Supplier\ReviewController::class, 'index']);
    Route::get('/dashboard-metrics', [\App\Http\Controllers\Api\Supplier\AnalyticsController::class, 'getMetrics']);
    Route::get('/revenue-graph', [\App\Http\Controllers\Api\Supplier\AnalyticsController::class, 'getRevenueGraphData']);


    Route::get('/analytics/products-sales', [\App\Http\Controllers\Api\Supplier\AnalyticsController::class, 'getProductSales']);
    Route::get('/analytics/revenue-history', [\App\Http\Controllers\Api\Supplier\AnalyticsController::class, 'getRevenueHistory']);
    Route::get('/analytics/summary', [\App\Http\Controllers\Api\Supplier\AnalyticsController::class, 'getDashboardStats']);

});
 