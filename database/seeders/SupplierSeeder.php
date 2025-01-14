<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder; 
use App\Models\Supplier;
use App\Models\Product;
use App\Models\User;
use App\Models\Order; 
use App\Models\Referral;
use App\Models\Category;
use App\Models\Deposit;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        // Create Categories (example) 
        // Create 10 suppliers
        Supplier::factory(10)->create()->each(function ($supplier) {
            // Create 10 products for each supplier
            $supplier->products()->createMany([
                [
                    'name' => 'Product 1',
                    'description' => 'Description of product 1',
                    'selling_price' => 100.00,
                    'price' => 80.00,
                    'stock_quantity' => 10,
                    'sku' => 'P1-SKU',
                    'category_id' => Category::inRandomOrder()->first()->id,
                    'is_active' => true
                ],
                [
                    'name' => 'Product 2',
                    'description' => 'Description of product 2',
                    'selling_price' => 200.00,
                    'price' => 150.00,
                    'stock_quantity' => 10,
                    'sku' => 'P2-SKU',
                    'category_id' => Category::inRandomOrder()->first()->id,
                    'is_active' => true
                ],
                [
                    'name' => 'Product 2',
                    'description' => 'Description of product 2',
                    'selling_price' => 200.00,
                    'price' => 150.00,
                    'stock_quantity' => 10,
                    'sku' => 'P2-SKU',
                    'category_id' => Category::inRandomOrder()->first()->id,
                    'is_active' => true
                ],
                [
                    'name' => 'Product 2',
                    'description' => 'Description of product 2',
                    'selling_price' => 200.00,
                    'price' => 150.00,
                    'stock_quantity' => 10,
                    'sku' => 'P2-SKU',
                    'category_id' => Category::inRandomOrder()->first()->id,
                    'is_active' => true
                ],
                [
                    'name' => 'Product 3',
                    'description' => 'Description of product 2',
                    'selling_price' => 200.00,
                    'price' => 150.00,
                    'stock_quantity' => 10,
                    'sku' => 'P2-SKU',
                    'category_id' => Category::inRandomOrder()->first()->id,
                    'is_active' => true
                ],
                [
                    'name' => 'Product 4',
                    'description' => 'Description of product 2',
                    'selling_price' => 200.00,
                    'price' => 150.00,
                    'stock_quantity' => 10,
                    'sku' => 'P2-SKU',
                    'category_id' => Category::inRandomOrder()->first()->id,
                    'is_active' => true
                ],
                [
                    'name' => 'Product 5',
                    'description' => 'Description of product 2',
                    'selling_price' => 200.00,
                    'price' => 150.00,
                    'stock_quantity' => 10,
                    'sku' => 'P2-SKU',
                    'category_id' => Category::inRandomOrder()->first()->id,
                    'is_active' => true
                ],
                // Add 8 more products as needed
            ]);

            // Create 3 users for each supplier
            User::factory(3)->create(['role'=>'customer'])->each(function ($user) {
                $totalPrice = mt_rand(100, 500);

                Order::factory(3)->create([
                    'user_id' => $user->id,
                    'total_price' => $totalPrice,
                    'status' => 'shipped',
                ])->each(function ($order) use ($user, $totalPrice) {
                    // Add random products to each order
                    $quantity = mt_rand(1, 4);

                    $order->products()->attach(
                        Product::inRandomOrder()->take(3)->pluck('id')->toArray(),
                        [
                            'quantity' => $quantity,
                            'price' => number_format($totalPrice / $quantity, 2), // Random price for each product
                        ]
                    );

                    Deposit::factory()->create([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'amount' => $totalPrice
                    ]);
                });
            });


            // Create referral product for one user
            // Referral::create([
            //     'user_id' => $supplier->users()->first()->id,
            //     'supplier_id' => $supplier->id,
            //     'order_id' => $supplier->orders()->first()->id,
            //     'referral_code' => 'REFERRAL123',
            //     'reward_amount' => 20.00
            // ]);
        });
    }
}
