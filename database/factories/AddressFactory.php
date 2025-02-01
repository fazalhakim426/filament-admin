<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\Address::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $city = City::inRandomOrder()->first();
        $state = $city->state;
        $country = $state->country;
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'street' => $this->faker->streetAddress(),
            'address' => $this->faker->address(),
            'country_id' => $country->id,
            'city_id' => $city->id,
            'state_id' => $state->id,
            'zip' => $this->faker->postcode(),
        ];
    }
}
