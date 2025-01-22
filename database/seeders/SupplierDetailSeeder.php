<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SupplierDetail;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\Category;
use App\Models\Deposit;
use App\Models\InventoryMovement;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class SupplierDetailSeeder extends Seeder
{
    public function run()
    {
        SupplierDetail::factory(10)->create()->each(function ($supplierDetail) {
            // Create products for the supplier
            $supplierDetail->user->assignRole('supplier');
            $supplierDetail->user->products()->createMany(
                Product::factory()->count(7)->make()->toArray()
            )->each(function ($product) use ($supplierDetail){
                // Add an inventory movement for product addition
                InventoryMovement::factory()->create([
                    'supplier_user_id' => $supplierDetail->user->id,
                    'product_id' => $product->id,
                    'type' => 'addition',
                    'quantity' => mt_rand(50, 200),
                    'unit_cost_price' => mt_rand(50, 300) , // Example cost price between 10 and 50
                ]);
            });

            // Create users and assign the "customer" role
            User::factory(3)->create()->each(function ($user) use ($supplierDetail) {
                // Assign the "customer" role to the user
                $user->assignRole('customer'); // Assign role using Spatie's method

                $totalPrice = mt_rand(100, 500);

                // Create orders for the customer
                Order::factory(3)->create([
                    'customer_user_id' => $user->id,
                    'total_price' => $totalPrice,
                    'status' => 'shipped',
                ])->each(function ($order) use ($user, $supplierDetail, $totalPrice) {
                    $quantity = mt_rand(1, 4);
                    $unit_selling_price = number_format($totalPrice / $quantity, 2);
                    $unit_cost_price = $unit_selling_price - 10; // Price is 10 rupees per dollar
                    $profit = ($unit_selling_price - $unit_cost_price) * $quantity;

                    // Attach products to the order
                    $products = Product::inRandomOrder()->take(3)->get();

                    foreach ($products as $product) {
                        $order->products()->attach($product->id, [
                            'supplier_user_id' => $supplierDetail->user->id,
                            'quantity' => $quantity,
                            'price' => $totalPrice,
                            'profit' => $profit,
                            'unit_cost_price' => $unit_cost_price,
                            'unit_selling_price' => $unit_selling_price,
                        ]);
                        InventoryMovement::factory()->create([
                            'supplier_user_id' => $supplierDetail->user->id,
                            'product_id' => $product->id,
                            'type' => 'deduction',
                            'quantity' => $quantity,
                            'unit_cost_price' => $unit_cost_price
                        ]);
                    }

                    // Add deposits for the order
                    Deposit::factory()->create([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'amount' => $totalPrice,
                        'deposit_type' => 'debit',
                        'description' => "Payment for $order->name "
                    ]);
                });
            });
        });
    }
}
