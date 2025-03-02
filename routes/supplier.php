<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('supplier')->group(function () {

    Route::apiResource('products', \App\Http\Controllers\Api\Supplier\ProductController::class);
    Route::patch('products/{product}/deactivate', [\App\Http\Controllers\Api\Supplier\ProductController::class, 'deactivate']);
    //inventory
    Route::apiResource('inventroy-movements', \App\Http\Controllers\Api\Supplier\InventoryMovementController::class);
    Route::get('{product}/inventroy-movements',[\App\Http\Controllers\Api\Supplier\InventoryMovementController::class,'getInventoryMovement']);



    Route::get('/orders', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'index']); // View all orders
    //confirm orders and thier items.
    // Route::patch('/orders/{order}/confirm', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'confirmOrder']); // Confirm order
    Route::patch('/order-items/{order_item}/confirm', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'confirmOrderItem']); // Confirm order
    //reject orders or order items.
    // Route::patch('/orders/{order}/reject', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'rejectOrder']); // Confirm order
    // Route::patch('/order-items/{order_item}/reject', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'rejectOrderItem']); // Confirm order

    //pay and refunded orders
    // Route::patch('/orders/{order}/pay', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'payOrder']); // Confirm order
    // Route::patch('/orders/{order}/refunded', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'refundOrder']); // Confirm order

    //dispatch , airwaybill and deliver the order.
    Route::patch('/orders/{order}/dispatch', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'dispatchOrder']); // Confirm order
    Route::patch('/orders/{order}/airway-bill', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'downloadAirwayBill']); // Download airway bill
    Route::patch('/orders/{order}/deliver', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'deliverOrder']); // Confirm order


    Route::get('/analytics/products-sales', [\App\Http\Controllers\Api\Supplier\AnalyticsController::class, 'getProductSales']);
    Route::get('/analytics/revenue-history', [\App\Http\Controllers\Api\Supplier\AnalyticsController::class, 'getRevenueHistory']);
});

// // Product Routes

// // Order Routes
// Route::get('orders', [OrderController::class, 'index']);
// Route::get('orders/{order}', [OrderController::class, 'show']);
// Route::patch('orders/{order}/confirm', [OrderController::class, 'confirm']);
// Route::patch('orders/{order}/reject', [OrderController::class, 'reject']);
// Route::patch('orders/{order}/dispatch', [OrderController::class, 'dispatch']);
// Route::get('orders/{order}/airway-bill', [OrderController::class, 'generateAirwayBill']);

// // Payment Routes
// Route::get('payments', [PaymentController::class, 'index']);
// Route::get('payments/weekly-invoice', [PaymentController::class, 'weeklyInvoice']);
// Route::get('payments/status', [PaymentController::class, 'paymentStatus']);

// // Notification Routes
// Route::get('notifications', [NotificationController::class, 'index']);
// Route::post('notifications/send', [NotificationController::class, 'sendNotification']);

// // Analytics Routes
// Route::get('analytics/products-sales', [AnalyticsController::class, 'productsSales']);
// Route::get('analytics/revenue-history', [AnalyticsController::class, 'revenueHistory']);



 

// // Dashboard Routes
// Route::get('dashboard', [DashboardController::class, 'index']);
// Route::get('dashboard/leaderboard', [DashboardController::class, 'leaderboard']);

// // Product Routes
// Route::get('products', [ProductController::class, 'index']);
// Route::get('products/{product}', [ProductController::class, 'show']);
// Route::post('products/{product}/share', [ProductController::class, 'shareProduct']);

// // Order Routes
// Route::post('orders', [OrderController::class, 'store']);
// Route::get('orders/{order}', [OrderController::class, 'show']);

// // Payment Routes
// Route::get('payments', [PaymentController::class, 'index']);
// Route::get('payments/history', [PaymentController::class, 'paymentHistory']);

// // Referral Routes
// Route::get('referrals', [ReferralController::class, 'index']);
// Route::get('referrals/earnings', [ReferralController::class, 'earnings']);

// // Notification Routes
// Route::get('notifications', [NotificationController::class, 'index']);

// // Support Routes
// Route::post('support/whatsapp', [SupportController::class, 'whatsapp']);
// Route::post('support/email', [SupportController::class, 'email']);
