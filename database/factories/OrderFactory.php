<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_status' => $this->faker->randomElement(['new', 'processing', 'confirmed', 'shipped', 'delivered', 'canceled']),
            'payment_status' => $this->faker->randomElement(['unpaid']),
        ];
    }

    /**
     * Create an order with associated product variants.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $customer
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withProductVariants($customer)
    {
        return $this->afterCreating(function (Order $order) use ($customer) {
            $variants = ProductVariant::inRandomOrder()->take(3)->get(); // Get 3 random product variants
            
            foreach ($variants as $variant) {
                $order->items()->create([
                    'product_variant_id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'quantity' => $this->faker->numberBetween(1, 5),
                    'price' => $variant->unit_selling_price,
                ]);
            }

            // Update total price after adding items
            $order->update([
                'items_cost' => $order->items()->sum(DB::raw('price * quantity')),
            ]);
        });
    }
}
