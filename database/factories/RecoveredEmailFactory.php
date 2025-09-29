<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RecoveredEmail>
 */
class RecoveredEmailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone' => $this->faker->phoneNumber,
            'contact_name' => $this->faker->name,
            'workflow_id' => '101',
            'organisation_id' => $this->faker->randomNumber(),
            'user_id' => '1',
            'zipcode' => $this->faker->postcode,
            'state' => $this->faker->stateAbbr,
            'city' => $this->faker->city,
            'address' => $this->faker->address,
            'offer' => $this->faker->word,
            'age' => $this->faker->numberBetween(18, 99),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'lead_score' => $this->faker->numberBetween(0, 100),
            'agent' => $this->faker->name,
            'novation' => $this->faker->word,
            'creative_price' => $this->faker->randomFloat(2, 1000, 100000),
            'monthly' => $this->faker->randomFloat(2, 100, 10000),
            'downpayment' => $this->faker->randomFloat(2, 1000, 50000),
            'generated_message' => $this->faker->sentence,
            'list_price' => $this->faker->randomFloat(2, 1000, 100000),
            'no_second_call' => $this->faker->boolean,
            'earnest_money_deposit' => $this->faker->randomFloat(2, 500, 20000),
            'email' => $this->faker->safeEmail,
        ];
    }
}
