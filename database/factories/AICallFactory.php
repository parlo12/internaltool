<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AICall>
 */
class AICallFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'call_id' => $this->faker->uuid,
            'calling_phone' => $this->faker->phoneNumber,
            'called_phone' => $this->faker->phoneNumber,
            'call_summary' => $this->faker->sentence,
        ];
    }
}
