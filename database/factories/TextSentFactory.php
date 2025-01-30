<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Organisation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TextSent>
 */
class TextSentFactory extends Factory
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
            'name' => $this->faker->name,
            'contact_communication_id' => $this->faker->text(50),
            'cost' => $this->faker->numberBetween(1, 10),
            'created_at' => $randomDate,
            'updated_at' => $randomDate,
            'organisation_id' => Organisation::inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'zipcode' => $this->faker->postcode,
            'marketing_channel' => $this->faker->randomElement(['VoiceMMS', 'SMS', 'VoiceMail', 'VoiceCall']), // Example channels
            'response' => $this->faker->randomElement(['yes', 'no']),
            'sending_number' => $this->faker->phoneNumber,
            'state' => $this->faker->state,
            'city' => $this->faker->city,

        ];
    }
}
