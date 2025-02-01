<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SupplierDetail;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\Deposit;
use App\Models\InventoryMovement;
use App\Models\Address;

class SupplierDetailSeeder extends Seeder
{
    public function run()
    {
        SupplierDetail::factory(10)->create()->each(function ($supplierDetail) {
            // Create products for the supplier
            $supplierDetail->user->assignRole('supplier');
            $supplierDetail->user->products()->createMany(
                Product::factory()->count(7)->make()->toArray()
            )->each(function ($product) use ($supplierDetail) {
                // Add an inventory movement for product addition
                InventoryMovement::factory()->create([
                    'supplier_user_id' => $supplierDetail->user->id,
                    'product_id' => $product->id,
                    'type' => 'addition',
                    'quantity' => mt_rand(50, 200),
                    'unit_price' => mt_rand(50, 300), // Example cost price between 10 and 50
                ]);
            });
            // Create users and assign the "customer" role
            User::factory(3)->create()->each(function ($user) use ($supplierDetail) {
                $user->assignRole('customer');
                $addresses = Address::factory(2)->create([
                    'user_id' => $user->id,
                ]);
                $quantity = mt_rand(1, 4);
                $products = Product::inRandomOrder()->take(3)->get();   
                // Create orders for the customer
                Order::factory(3)->create([
                    'customer_user_id' => $user->id,
                    'recipient_id' => $addresses[1]->id,
                    'sender_id' => $addresses[0]->id,
                    'total_price' => $products->sum('unit_selling_price'),
                ])->each(function ($order) use ($user, $supplierDetail, $quantity, $products) {
                    foreach ($products as $product) { 
                        $order->products()->syncWithoutDetaching([
                            $product->id => [
                                'supplier_user_id' => $supplierDetail->user_id,
                                'quantity' => $quantity,
                                'price' => $product->unit_selling_price,
                            ]
                        ]);
                         
                        $item = $order->products()->where('product_id', $product->id)->first(); 
                        
                        if ($item) {
                            InventoryMovement::factory()->create([
                                'supplier_user_id' => $supplierDetail->user->id,
                                'order_item_id' => $item->pivot->id, // Use pivot table ID
                                'product_id' => $product->id,
                                'type' => 'deduction',
                                'quantity' => $quantity,
                                'unit_price' => $product->unit_selling_price,
                                'total_price' => $product->unit_selling_price * $quantity
                            ]);
                        }
                    }

                    // Add deposits for the order
                    Deposit::factory()->create([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'amount' => $products->sum('total_price'),
                        'deposit_type' => 'debit',
                        'description' => "Payment for $order->warehouse_number "
                    ]);
                });
            });
        });
    }
}
