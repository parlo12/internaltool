<?php

namespace Database\Factories;

use App\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Workflow;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ContactFactory extends Factory
{
    protected $model = \App\Models\Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currentStep = $this->faker->numberBetween(1, 5); // current step between 1 and 5
        $nextStep = $this->faker->numberBetween($currentStep + 1, 6); // next step between current step + 1 and 6
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();
        $randomDate = Carbon::createFromTimestamp(rand($startOfYear->timestamp, $endOfYear->timestamp));
        return [
            'uuid' => Str::uuid(),
            'current_step' => $currentStep,
            'workflow_id' => Workflow::factory(),
            'contact_communication_ids' => $this->faker->text(50),
            'phone' => $this->faker->phoneNumber,
            'can_send' => $this->faker->boolean ? '1' : '0',
            'can_send_after' => $this->faker->date,
            'contact_name' => $this->faker->firstName." ".$this->faker->lastName,
            'response' => $this->faker->boolean ? '1' : '0',
            'status'=>"WAITING_FOR_QUEAUE",
            'valid_lead'=>0,
            'offer_made'=>0,
            'contract_executed'=>0,
            'contract_cancelled'=>0,
            'deal_closed'=>0,
            'cost' => $this->faker->numberBetween(1, 10),
            'created_at' => $randomDate,
            'updated_at' => $randomDate,
            'organisation_id'=> Organisation::inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'zipcode' => $this->faker->postcode,
            'state' => $this->faker->state,
            'city' => $this->faker->city,
            'subscribed'=>1
        ];
    }
}
