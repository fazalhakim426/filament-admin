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
use App\Models\Image;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\VariantOption;

class SupplierDetailSeeder extends Seeder
{
    public function run()
    {
        echo "Seeding Supplier Details...\n";

        SupplierDetail::factory(3)->create()->each(function ($supplierDetail) {
            echo "Created Supplier: {$supplierDetail->id}\n";
 
            $supplierDetail->user->assignRole('supplier');
            echo "Assigned 'supplier' role to User: {$supplierDetail->user->id}\n";
 
            $products = Product::factory()->count(3)->make()->toArray();
            $supplierDetail->user->products()->createMany($products);
            echo "Created 7 products for Supplier: {$supplierDetail->id}\n";
 
                foreach ($supplierDetail->user->products as $product) {

                    for ($i = 0; $i < 3; $i++) {
                        $variant = ProductVariant::create([
                            'product_id' => $product->id,
                            'sku' => $product->id . '-VAR' . ($i + 1),
                            'unit_selling_price' => 50,
                            'stock_quantity' => 0,
                        ]);
        
                        VariantOption::create([
                            'product_variant_id' => $variant->id,
                            'attribute_name' => 'Size',
                            'attribute_value' =>  'Large',
                        ]); 
                        VariantOption::create([
                            'product_variant_id' => $variant->id,
                            'attribute_name' => 'Color',
                            'attribute_value' =>  'Red',
                        ]); 
                        $randomImageNumber = rand(1, 12);
                        $imagePath = "/products/media/{$randomImageNumber}.jpg"; // Ensure these images exist in `public/storage/images/products/`
                        
                        Image::create([
                            'imageable_id' => $variant->id,
                            'imageable_type' => ProductVariant::class,
                            'url' => $imagePath,
                        ]);
                    }
                }
            foreach ($supplierDetail->user->products as $product) { 
                    foreach($product->productVariants as $variant){ 
                        
                     InventoryMovement::factory()->create([
                        'supplier_user_id' => $supplierDetail->user->id, 
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'type' => 'addition',
                        'quantity' => mt_rand(200, 300),
                        'unit_price' => mt_rand(50, 300),
                    ]);  
                    echo "Added inventory movement for Product: {$product->id}\n";
                }

            }
            // Create customers
            User::factory(1)->create()->each(function ($user) use ($supplierDetail) {
                $user->assignRole('customer');
                echo "Created Customer: {$user->id} and assigned 'customer' role\n";
            
                // Create addresses for customers
                $addresses = Address::factory(2)->create(['user_id' => $user->id]);
                echo "Created 2 addresses for Customer: {$user->id}\n";
            
                // Fetch random product variants
                $variants = ProductVariant::inRandomOrder()->take(1)->get();
                $quantity = mt_rand(1, 4);
            
                // Create orders
                Order::factory(10)->create([
                    'customer_user_id' => $user->id,
                    'supplier_user_id' => $supplierDetail->user_id,
                    'recipient_id' => $addresses[1]->id,
                    'sender_id' => $addresses[0]->id,
                    'shipping_cost' => 10,
                ])->each(function ($order) use ($user, $supplierDetail, $quantity, $variants) {
                    echo "Created Order: {$order->id} for Customer: {$user->id}\n";
            
                    foreach ($variants as $variant){
                        $order->items()->create([
                            'product_variant_id' => $variant->id,
                            'product_id' => $variant->product_id,
                            'supplier_user_id' => $supplierDetail->user_id,
                            'quantity' => $quantity,
                            'price' => $variant->unit_selling_price,
                        ]);            
                        Review::factory(fake()->numberBetween(3, 15))->create([
                            'order_id' => $order->id,
                            'product_id' => $variant->product_id, // Ensure the review is tied to the variant
                            'user_id' => $order->customer_user_id, // Ensure the review belongs to the correct user
                        ]);            
                        echo "Added Product Variant: {$variant->id} to Order: {$order->id}\n";
                        echo "total Quantity: {$variant->quantity}\n";
                        echo "requested Quantity: {$quantity}\n";
            
                        // Add inventory deduction
                        InventoryMovement::factory()->create([
                            'supplier_user_id' => $supplierDetail->user->id,
                            'order_item_id' => $order->items()->where('product_variant_id', $variant->id)->first()->id,
                            'product_variant_id' => $variant->id,
                            'product_id' => $variant->product_id, // Keep product reference if needed
                            'type' => 'deduction',
                            'quantity' => $quantity,
                            'unit_price' => $variant->unit_selling_price,
                            'total_price' => $variant->unit_selling_price * $quantity
                        ]);
            
                        echo "Recorded inventory deduction for Product Variant: {$variant->id} in Order: {$order->id}\n";
                    }
                });
            });
            
        });

        echo "Seeding Completed!\n";
    }
}
