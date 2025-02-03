<?php

use App\Models\Deposit;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('', function () {
 
    return redirect('/admin');
});

Route::get('/order-test',function ()  {
      // Create orders
       
      dump(['referralDeposits'=>Deposit::referralDeposits()->count()]);
      dump(['CashInOut'=>Deposit::cashInOut()->count()]);
      dump(['OrderDeposits'=>Deposit::orderDeposits()->count()]);
      dd(Deposit::count());
});
require __DIR__.'/auth.php';
