<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Resources\UserResource;
use App\Models\Category;
use App\Models\User;

Route::middleware(['auth:sanctum'],)->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('register-customer', [AuthController::class, 'registerCustomer']);
Route::post('register-supplier', [AuthController::class, 'registerSupplier']);
Route::post('login/{role}', [AuthController::class, 'login']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('reset-password-auth', [AuthController::class, 'resetPasswordAuth'])->middleware(['auth:sanctum']);
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);

Route::get('categories', [CategoryController::class, 'index']);
Route::get('{category}/sub-categories', [CategoryController::class, 'subCategories']);
Route::get('dummy_users', function () {
    return response()->json(
        UserResource::collection(User::all())
    );
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('product/{product}/reviews', [\App\Http\Controllers\Api\Common\ProductReviewController::class, 'reviews']);
    Route::apiResource('users', \App\Http\Controllers\Api\Common\UserController::class)->only('index', 'show');
    Route::apiResource('banners', \App\Http\Controllers\Api\Common\HomeBannerController::class)->only('index', 'show');
});
Route::get('order/{warehouse}/trackings', [\App\Http\Controllers\Api\Supplier\OrderTrackingController::class, 'index']);

require('customer.php');
require('supplier.php');
