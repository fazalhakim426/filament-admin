<?php

namespace Database\Factories;
use App\Models\Review; 

use Illuminate\Database\Eloquent\Factories\Factory;
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition()
    {
        return [ 
            'rating_stars' => $this->faker->randomFloat(1, 1, 5),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
