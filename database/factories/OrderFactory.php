<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
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
            'order_status' => $this->faker->randomElement(['new','processing', 'confirmed', 'shipped', 'delivered', 'canceled']),
            'payment_status' => $this->faker->randomElement(['unpaid']),
        ];
    }

    /**
     * Create an order with associated products.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $customer
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withProducts($customer)
    {
        return $this->afterCreating(function (Order $order) use ($customer) {
            $products = Product::inRandomOrder()->take(3)->get(); // Get 3 random products
            foreach ($products as $product) {
                $order->products()->attach($product->id, [
                    'quantity' => $this->faker->numberBetween(1, 5),
                    'price' => $product->selling_price,
                ]);

            }
            $order->updateQuietly([
                'total_price' => $order->items()->sum(DB::raw('price * quantity')),
            ]);
        });
    }
}
