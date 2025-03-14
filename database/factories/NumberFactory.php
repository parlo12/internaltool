<?php

namespace Database\Factories;

use App\Models\NumberPool;
use App\Models\Organisation;
use App\Models\SendingServer;
use Carbon\Carbon;
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
            'sending_server_id' => SendingServer::inRandomOrder()->first()->id,
            'number_pool_id' => NumberPool::inRandomOrder()->first()->id,
            'can_refill_on' => Carbon::now(),
            'remaining_messages' => random_int(1,3),
        ];
    }
}
