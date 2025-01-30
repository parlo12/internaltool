<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organisation>
 */
class OrganisationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organisation_name' => $this->faker->company,
            'openAI' => "yyyy",
            'calling_service' => $this->faker->randomElement(['twilio', 'signalwire']),
            'texting_service' => $this->faker->randomElement(['twilio', 'signalwire']),
            'signalwire_texting_space_url' => $this->faker->optional()->url,
            'signalwire_texting_api_token' => $this->faker->optional()->uuid,
            'signalwire_texting_project_id' => $this->faker->optional()->uuid,
            'twilio_texting_auth_token' => $this->faker->optional()->uuid,
            'twilio_texting_account_sid' => $this->faker->optional()->uuid,
            'twilio_calling_account_sid' => $this->faker->optional()->uuid,
            'twilio_calling_auth_token' => $this->faker->optional()->uuid,
            'signalwire_calling_space_url' => $this->faker->optional()->url,
            'signalwire_calling_api_token' => $this->faker->optional()->uuid,
            'signalwire_calling_project_id' => $this->faker->optional()->uuid,
            'openAI'=>$this->faker->uuid,
            'user_id' => User::inRandomOrder()->first()->id,
            'auth_token' => $this->faker->uuid,
            'api_url' => $this->faker->uuid,
            'device_id' => $this->faker->uuid,
            'sending_email' => $this->faker->uuid,
            'email_password' => $this->faker->uuid,


        ];
    }
}
