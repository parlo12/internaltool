<?php

namespace Database\Factories;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Number>
 */
class NumberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone_number' => $this->faker->phoneNumber(),
            'purpose' => $this->faker->randomElement(['calling', 'texting']),
            'provider' => $this->faker->randomElement(['twilio', 'signalwire']),
            'organisation_id' => Organisation::inRandomOrder()->first()->id,

        ];
    }
}
