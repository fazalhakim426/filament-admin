<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\SubCategory;
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

        $subCategory = SubCategory::inRandomOrder()->first();
        $price = $this->faker->numberBetween(2, 9) * 10;
        $percentage = $price > 150;
        return [
            'name' => $this->faker->randomElement([
                'Wireless Earbuds',
                'Gaming Keyboard',
                'Smartphone Stand',
                'LED Desk Lamp',
                'Portable Blender',
                'Bluetooth Speaker',
                'Fitness Tracker',
                'USB-C Hub',
                'Noise-Canceling Headphones',
                'Smart Water Bottle',
                'Ergonomic Office Chair',
                'Adjustable Laptop Stand',
                'Smart Light Bulb',
                'Electric Toothbrush',
                'Portable Power Bank',
                'Dash Cam Recorder',
                'Car Phone Mount',
                'Wireless Charger',
                'Digital Drawing Tablet',
                'Smart Thermostat',
                'Mechanical Gaming Mouse',
                'Foldable Treadmill',
                'Mini Projector',
                'Wireless Security Camera',
                'Smart Door Lock',
                'Massage Gun',
                'Air Purifier',
                'Cordless Vacuum Cleaner',
                'Multi-Port Charger',
                'Smartwatch with GPS',
                'USB Microphone',
                'VR Headset',
                'Streaming Webcam',
                'Compact Drone',
                'Kitchen Stand Mixer',
                'Automatic Soap Dispenser',
                'Adjustable Dumbbells',
                'Resistance Bands Set',
                'Electric Kettle',
                'Digital Photo Frame',
                'Smart Garden Kit',
                'E-Reader Tablet',
                'Portable Espresso Machine',
                'Smart Bike Trainer',
                'Outdoor Solar Lights',
                'Digital Meat Thermometer',
                'Robot Vacuum Cleaner',
                'Compact Air Fryer',
                'Smart Baby Monitor',
                'Noise-Isolating Earphones'
            ]),
            'description' => $this->faker->sentence(12),  
            'unit_selling_price' => $price,
            'stock_quantity' => $this->faker->numberBetween(1, 100), // Random stock quantity
            'sku' => $this->faker->unique()->lexify('P????'), // Unique SKU
            'category_id' => $subCategory->category_id,
            'sub_category_id' => $subCategory->id,
            'is_active' => $this->faker->boolean(70), // 70% chance of being active
            'sponsor' => $this->faker->boolean(50), // 50% chance of being active
            'manzil_choice' => $this->faker->boolean(50), // 50% chance of being active
            'referral_reward_value' => $percentage ? 10 : 30,
            'referral_reward_type' => $percentage ? 'percentage' : 'fixed',
        ];
    }
}
