<?php

namespace Database\Factories;

use App\Models\SendingServer;
use Illuminate\Database\Eloquent\Factories\Factory;

class SendingServerFactory extends Factory
{
    protected $model = SendingServer::class;

    public function definition()
    {
        return [
            'server_name'=>$this->faker->text(10),
            'purpose'=> $this->faker->randomElement(['calling', 'texting']),
            'service_provider' => $this->faker->randomElement(['twilio', 'signalwire']),
            'signalwire_space_url' => $this->faker->url,
            'signalwire_api_token' => $this->faker->uuid,
            'signalwire_project_id' => $this->faker->uuid,
            'twilio_auth_token' => $this->faker->uuid,
            'twilio_account_sid' => $this->faker->uuid,
            'user_id' => 1, // Adjust based on existing users
            'websockets_api_url' => $this->faker->url,
            'websockets_auth_token' => $this->faker->uuid,
            'websockets_device_id' => $this->faker->uuid,
            'organisation_id' => 1, // Adjust based on existing organisations
        ];
    }
}

