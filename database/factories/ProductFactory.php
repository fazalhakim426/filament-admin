<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\VariantOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $subCategory = SubCategory::inRandomOrder()->first();
        $price = $this->faker->numberBetween(2, 9) * 10;
        $percentage = $price > 150;
    

        return [
            'name' => $this->faker->randomElement([
                'Wireless Earbuds', 'Gaming Keyboard', 'Smartphone Stand',
                'LED Desk Lamp', 'Portable Blender', 'Bluetooth Speaker',
                'Fitness Tracker', 'USB-C Hub', 'Noise-Canceling Headphones'
            ]),
            'description' => $this->faker->sentence(12),   
            'category_id' => $subCategory->category_id,
            'sub_category_id' => $subCategory->id,
            'is_active' => $this->faker->boolean(70),
            'sponsor' => $this->faker->boolean(50),
            'manzil_choice' => $this->faker->boolean(50), 
        ];
    }
    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            // Create 3 random specifications per product
            ProductSpecification::factory()->count(3)->create([
                'product_id' => $product->id,
            ]);
        });
    }
}
