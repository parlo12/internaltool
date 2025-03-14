<?php

namespace Database\Factories;

use App\Models\NumberPool;
use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NumberPool>
 */
class NumberPoolFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pool_name' => $this->faker->word(5),
            'pool_messages' =>$this->faker->randomNumber(2),
            'pool_time' => $this->faker->randomNumber(2),
            'pool_time_units' => $this->faker->randomElement(['seconds', 'minutes', 'hours', 'days']),
            'organisation_id' => Organisation::inRandomOrder()->first()->id,
        ];
    }
}
