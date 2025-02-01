<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
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

        $city = City::inRandomOrder()->first();
        $state = $city->state;
        $country = $state->country;
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),  
            'referral_code' => Str::upper(Str::random(8)), 
            'address'=> $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'whatsapp' => $this->faker->phoneNumber(),
            'street' => $this->faker->streetAddress(),

            'country_id' => $country->id,
            'city_id' => $city->id,
            'state_id' => $state->id,
            'zip' => $this->faker->postcode()
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
