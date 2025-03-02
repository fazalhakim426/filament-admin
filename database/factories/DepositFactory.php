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
        return [
             'transaction_reference' => $this->faker->unique()->uuid(),  
        ];
    }
}
