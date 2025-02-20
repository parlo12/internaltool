<?php

namespace Database\Factories;

use App\Models\WrongNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

class WrongNumberFactory extends Factory
{
    protected $model = WrongNumber::class;

    public function definition(): array
    {
        return [
            'phone' => $this->faker->unique()->e164PhoneNumber(),
            'contact_name' => $this->faker->name(),
            'workflow_id' => $this->faker->randomDigitNotNull(),
            'organisation_id' => $this->faker->randomDigitNotNull(),
            'user_id' => $this->faker->randomDigitNotNull(),
            'zipcode' => $this->faker->postcode(),
            'state' => $this->faker->state(),
            'city' => $this->faker->city(),
            'address' => $this->faker->address(),
            'offer' => $this->faker->randomFloat(2, 10000, 500000),
            'email' => $this->faker->unique()->safeEmail(),
            'age' => $this->faker->numberBetween(18, 80),
            'gender' => $this->faker->randomElement(['Male', 'Female', 'Other']),
            'lead_score' => $this->faker->numberBetween(1, 100),
            'agent' => $this->faker->name(),
            'novation' => $this->faker->boolean(),
            'creative_price' => $this->faker->randomFloat(2, 5000, 100000),
            'monthly' => $this->faker->randomFloat(2, 500, 5000),
            'downpayment' => $this->faker->randomFloat(2, 1000, 50000),
        ];
    }
}

