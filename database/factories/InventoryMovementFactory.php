<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryMovementFactory extends Factory
{

    public function definition()
    {
        $quantity = $this->faker->numberBetween(1, 20);
        $unit_price = $this->faker->numberBetween(2,9) * 10;
        return [
            'supplier_user_id' => User::factory(),
            'product_id' => Product::factory(),
            'type' => $this->faker->randomElement(['addition', 'deduction']),
            'quantity' => $quantity,
            'total_price' => $quantity * $unit_price,
            'description' => $this->faker->sentence(),
        ];
    }
}
