<?php

namespace Database\Factories;

use App\Models\MessageTypes;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Workflow;
class StepFactory extends Factory
{
    protected $model = \App\Models\Step::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workflow_id' => Workflow::factory(),
            'type' => MessageTypes::factory(),
            'content' => $this->faker->text(100),
            'name' => $this->faker->text(100),
            'delay' => $this->faker->numberBetween(1, 60) . ' minutes',
            'custom_sending' => $this->faker->boolean ? '1' : '0',
            'custom_sending_data'=>$this->faker->text(100),
            'end_time' => $this->faker->time('H:i'),
            'start_time'=>$this->faker->time('H:i'),
            'days_of_week' => $this->faker->dateTime()->format('l'),
            'batch_size'=>$this->faker->numberBetween(20,100),
            'batch_delay'=>$this->faker->numberBetween(20,100),
            'step_quota_balance'=>$this->faker->numberBetween(20,100),
            'offer_expiry' => $this->faker->dateTime()->format('l'),
            'email_subject' => $this->faker->text(10),
            'generated_message' => random_int(0,1),
        ];
    }
}

