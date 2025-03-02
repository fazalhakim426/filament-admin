<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth:sanctum'])->prefix('customer')->group(function (){
    Route::apiResource('products',\App\Http\Controllers\Api\Customer\ProductController::class);
    Route::apiResource('orders',\App\Http\Controllers\Api\Customer\PlaceOrderController::class)->only('index','show','store','destroy');
    Route::post('orders/{order}/update-product-quantity',[\App\Http\Controllers\Api\Customer\PlaceOrderController::class,'updateProductQantity']);
    Route::delete('order-items/{order-item}',[\App\Http\Controllers\Api\Customer\PlaceOrderController::class,'removeItems']);
});