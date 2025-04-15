<?php

namespace Database\Factories; 

use App\Models\ProductSpecification;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductSpecificationFactory extends Factory
{
    protected $model = ProductSpecification::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->randomElement([
                'Color', 'Weight', 'Size', 'Material', 'Battery Life', 'Warranty'
            ]),
            'value' => $this->faker->word,
        ];
    }
}

