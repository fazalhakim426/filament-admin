<?php

namespace Database\Factories;
 
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryMovementFactory extends Factory
{

    public function definition()
    {
        return [
            'supplier_user_id' => User::factory(),
            'product_id' => Product::factory(),
            'type' => $this->faker->randomElement(['addition', 'deduction']),
            'quantity' => $this->faker->numberBetween(1, 100),
            'unit_cost_price' => $this->faker->randomFloat(2, 10, 100),
            'description'=> $this->faker->sentence(),
        ];
    }
}
