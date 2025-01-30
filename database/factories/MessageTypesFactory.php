<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class MessageTypesFactory extends Factory
{
    protected $model = \App\Models\MessageTypes::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => $this->faker->phoneNumber,
            'name' => $this->faker->randomElement(['voice call', 'voicemail', 'SMS']),
        ];
    }
}

