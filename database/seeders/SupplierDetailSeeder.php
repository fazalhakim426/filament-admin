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
        echo "Seeding Supplier Details...\n";

        SupplierDetail::factory(10)->create()->each(function ($supplierDetail) {
            echo "Created Supplier: {$supplierDetail->id}\n";

            // Assign role to supplier
            $supplierDetail->user->assignRole('supplier');
            echo "Assigned 'supplier' role to User: {$supplierDetail->user->id}\n";

            // Create products for the supplier
            $products = Product::factory()->count(7)->make()->toArray();
            $supplierDetail->user->products()->createMany($products);
            echo "Created 7 products for Supplier: {$supplierDetail->id}\n";

            // Add inventory movements for each product
            foreach ($supplierDetail->user->products as $product) {
                InventoryMovement::factory()->create([
                    'supplier_user_id' => $supplierDetail->user->id,
                    'product_id' => $product->id,
                    'type' => 'addition',
                    'quantity' => mt_rand(50, 200),
                    'unit_price' => mt_rand(50, 300),
                ]);
                echo "Added inventory movement for Product: {$product->id}\n";
            }
            // Create customers
            User::factory(3)->create()->each(function ($user) use ($supplierDetail) {
                $user->assignRole('customer');
                echo "Created Customer: {$user->id} and assigned 'customer' role\n";

                // Create addresses for customers
                $addresses = Address::factory(2)->create(['user_id' => $user->id]);
                echo "Created 2 addresses for Customer: {$user->id}\n";

                // Fetch random products
                $products = Product::inRandomOrder()->take(3)->get();
                $quantity = mt_rand(1, 4);

                // Create orders
                Order::factory(3)->create([
                    'customer_user_id' => $user->id,
                    'recipient_id' => $addresses[1]->id,
                    'sender_id' => $addresses[0]->id,
                    'total_price' => $products->sum('unit_selling_price'),
                ])->each(function ($order) use ($user, $supplierDetail, $quantity, $products) {
                    echo "Created Order: {$order->id} for Customer: {$user->id}\n";

                    foreach ($products as $product) {
                        $order->products()->syncWithoutDetaching([
                            $product->id => [
                                'supplier_user_id' => $supplierDetail->user_id,
                                'quantity' => $quantity,
                                'price' => $product->unit_selling_price,
                            ]
                        ]);
                        
                        echo "Added Product: {$product->id} to Order: {$order->id}\n";

                        // Add inventory deduction
                        $item = $order->products()->where('product_id', $product->id)->first();
                        if ($item) {
                            InventoryMovement::factory()->create([
                                'supplier_user_id' => $supplierDetail->user->id,
                                'order_item_id' => $item->pivot->id,
                                'product_id' => $product->id,
                                'type' => 'deduction',
                                'quantity' => $quantity,
                                'unit_price' => $product->unit_selling_price,
                                'total_price' => $product->unit_selling_price * $quantity
                            ]);
                            echo "Recorded inventory deduction for Product: {$product->id} in Order: {$order->id}\n";
                        }
                    }

                    // Create deposits
                    Deposit::factory()->create([
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'amount' => $products->sum('total_price'),
                        'deposit_type' => 'debit',
                        'description' => "Payment for {$order->warehouse_number}"
                    ]);
                    echo "Added Deposit for Order: {$order->id}, Amount: {$products->sum('total_price')}\n";
                });
            });
        });

        echo "Seeding Completed!\n";
    }
}
