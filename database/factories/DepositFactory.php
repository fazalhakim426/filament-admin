<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class DepositFactory  extends Factory
{
   
    public function definition(): array
    {
        $type = $this->faker->randomElement(['debit', 'credit']);
        return [
            'user_id' => User::factory(),
            'order_id' => null, // Nullable for deposits not related to orders
            'amount' => $this->faker->randomFloat(2, 50, 500), // Random amount between 50 and 500
            'transaction_type' => $type,
            'deposit_type' => $type=='debit' ? 'wallet' : $this->faker->randomElement(['card', 'bank', 'admin']),
            'transaction_reference' => $this->faker->unique()->uuid(),  
        ];
    }
}
