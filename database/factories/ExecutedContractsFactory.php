<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Organisation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\executedContracts>
 */
class ExecutedContractsFactory extends Factory
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
            'created_at' => $randomDate,
            'updated_at' => $randomDate,
            'organisation_id' => Organisation::inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
'zipcode' => $this->faker->postcode,
            'state' => $this->faker->state,
            'city' => $this->faker->city,

        ];
    }
}
