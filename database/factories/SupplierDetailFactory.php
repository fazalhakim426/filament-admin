<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Category;
use App\Models\Role;
use App\Models\SubCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierDetailFactory extends Factory
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
        
        $subCategory = SubCategory::inRandomOrder()->first(); 
        return [
            'user_id' => User::factory(),
            'business_name' => $this->faker->company(),
            'contact_person' => $this->faker->name(),
            'website' => $this->faker->url(),
            'ecommerce_experience' => $this->faker->randomElement(['none', '1-3 years', '3-5 years', '5+ years']),
            'supplier_type' => $this->faker->randomElement(['wholesale', 'retail', 'distributor']),
            'category_id' =>$subCategory->category_id,
            'sub_category_id' => $subCategory->id,
            'product_available' => $this->faker->numberBetween(1, 100),
            'product_source' => $this->faker->randomElement(['imported', 'local']),
            'product_unit_quality' => $this->faker->word(),
            'self_listing' => $this->faker->boolean(50),
            'product_range' => $this->faker->word(),
            'using_daraz' => $this->faker->boolean(),
            'daraz_url' => $this->faker->url(),
            'term_agreed' => $this->faker->boolean(),
            'marketing_type' => $this->faker->randomElement([1, 2, 3]),
            'preferred_contact_time' => $this->faker->optional()->dateTimeThisYear(),
        ];
    }
}
