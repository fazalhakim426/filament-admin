<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SupplierDetail;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\Category;
use App\Models\Deposit;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class SupplierDetailSeeder extends Seeder
{
    public function run()
    {
        SupplierDetail::factory(10)->create()->each(function ($supplierDetail){

            $supplierDetail->user->products()->createMany(
                Product::factory()->count(7)->make()->toArray()
            );

            User::factory(3)->create(['role_id' => Role::where('name','customer')->first()->id])->each(function ($user) use ($supplierDetail){
                $totalPrice = mt_rand(100, 500);

                Order::factory(3)->create([
                    'customer_user_id' => $user->id,
                    'total_price' => $totalPrice, 
                    'status' => 'shipped',
                ])->each(function ($order) use ($user, $supplierDetail,$totalPrice) { 
                    $quantity = mt_rand(1, 4);

                    $order->products()->attach(
                        Product::inRandomOrder()->take(3)->pluck('id')->toArray(),
                        [
                            'supplier_user_id' => $supplierDetail->user->id, 
                            'quantity' => $quantity,
                            'price' => number_format($totalPrice / $quantity, 2), // Random price for each product
                        ]
                    );

                    Deposit::factory()->create([
                        'user_id' => $user->id,
                        'order_id' => $order->id, 
                        'amount' => $totalPrice,
                        'deposit_type'=>'debit',
                        'description'=> "Payment for $order->name "
                    ]);
                });
            });


        });
    }
}
