<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class ProductFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $price = $this->faker->randomFloat(2, 40, 400);
        $percentage =$price>150;
        return [
            'name' => $this->faker->words(2, true), // Random product name
            'description' => $this->faker->sentence(), // Random description
            'unit_selling_price' => $price, // Random price between 40 and 400
            'stock_quantity' => $this->faker->numberBetween(1, 100), // Random stock quantity
            'sku' => $this->faker->unique()->lexify('P????'), // Unique SKU
            'category_id' => Category::inRandomOrder()->first()->id, // Random category
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
            'referral_reward_amount' => !$percentage?10:null,
            'referral_reward_percentage' => $percentage?10:null,
        ];
    }
}
