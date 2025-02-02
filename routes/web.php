<?php

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('', function () {
 
    return redirect('/admin');
});

Route::get('/order-test',function ()  {
      // Create orders
      Order::factory()->create([
        'customer_user_id' =>1,
        'recipient_id' => 1,
        'sender_id' =>1,
        'total_price' =>3,
      ]);
      dd(Order::all());
});
require __DIR__.'/auth.php';
