<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Freshlead>
 */
class FreshleadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone' => $this->faker->phoneNumber(),
            'contact_name' => $this->faker->name(),
            'workflow_id' => $this->faker->uuid(),
            'organisation_id' => $this->faker->randomNumber(5),
            'user_id' => $this->faker->randomNumber(5),
            'zipcode' => $this->faker->postcode(),
            'state' => $this->faker->state(),
            'city' => $this->faker->city(),
            'address' => $this->faker->address(),
            'offer' => $this->faker->randomFloat(2, 10000, 500000),
            'email' => $this->faker->safeEmail(),
            'age' => $this->faker->numberBetween(18, 80),
            'gender' => $this->faker->randomElement(['Male', 'Female', 'Other']),
            'lead_score' => $this->faker->numberBetween(1, 100),
            'agent' => $this->faker->name(),
            'novation' => $this->faker->boolean() ? 'Yes' : 'No',
            'creative_price' => $this->faker->randomFloat(2, 5000, 100000),
            'monthly' => $this->faker->randomFloat(2, 500, 5000),
            'downpayment' => $this->faker->randomFloat(2, 1000, 20000),
            'messages' => [
                'subject' => $this->faker->sentence(),
                'body' => $this->faker->paragraph(),
            ],
        ];
    }
}
