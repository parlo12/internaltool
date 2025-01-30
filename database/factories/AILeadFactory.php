<?php

namespace Database\Factories;
use App\Models\AI_Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AI_Lead>
 */
class AILeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'zipcode' => $this->faker->postcode,
            'state' => $this->faker->state,
            'offer' => $this->faker->word,
            'address' => $this->faker->address,
            'gender' => $this->faker->randomElement(['Male', 'Female', 'Other']),
            'lead_score' => $this->faker->numberBetween(0, 100),
            'phone' => $this->faker->phoneNumber,
        ];
    }
}
