<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Organisation;
use App\Models\User;
use Carbon\Carbon;
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
        // Generate a random date within the current year
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();
        $randomDate = Carbon::createFromTimestamp(rand($startOfYear->timestamp, $endOfYear->timestamp));

        return [
            'contact_id' => Contact::factory(),
            'organisation_id' => Organisation::inRandomOrder()->first()->id,
            'name' => $this->faker->name,
            'contact_communication_id' => $this->faker->text(50),
            'cost' => $this->faker->numberBetween(1, 10),
            'user_id' => User::inRandomOrder()->first()->id,
            'zipcode' => $this->faker->postcode,
            'state' => $this->faker->state,
            'city' => $this->faker->city,
            'marketing_channel' => 'AI_Call', // Example channels
            'response' => $this->faker->randomElement(['yes', 'no']),
            'sending_number' => $this->faker->phoneNumber,
            'created_at' => $randomDate,
            'updated_at' => $randomDate,
        ];
    }
}
