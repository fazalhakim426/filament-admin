<?php

use App\Models\Deposit;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('', function () {
    return redirect('/admin');
});

Route::get('/order-test', function () {
    dump('new');
    dump(['referralDeposits' => Deposit::referralDeposits()->count()]);
    dump(['CashInOut' => Deposit::cashInOut()->count()]);
    dump(['OrderDeposits' => Deposit::orderDeposits()->count()]);
    dd(Deposit::count());
});
//auth middleware

Route::middleware(['auth:web'])->group(function () {
    Route::get('/orders/{order}/airway-bill', [\App\Http\Controllers\Api\Supplier\OrderController::class, 'streamAirwayBill'])->name('orders.stream-airway-bill');
});


// Custom Artisan commands for quick execution
Route::get('/artisan/optimize-clear', function () {
    Artisan::call('optimize:clear');
    return 'Optimize clear executed!';
});

Route::get('/artisan/db-seed', function () {
    Artisan::call('db:seed');
    return 'Database seeding executed!';
});

Route::get('/artisan/migrate-fresh', function () {
    Artisan::call('migrate:fresh');
    return 'Migrate fresh executed!';
});

Route::get('/artisan/migrate-refresh', function () {
    Artisan::call('migrate:refresh');
    return 'Migrate refresh executed!';
});

require __DIR__.'/auth.php';
