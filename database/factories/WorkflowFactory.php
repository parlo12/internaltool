<?php

namespace Database\Factories;

use App\Models\Folder;
use App\Models\NumberPool;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workflow>
 */
class WorkflowFactory extends Factory
{
    protected $model = \App\Models\Workflow::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'steps' => $this->faker->text(100),
            'steps_flow' => $this->faker->text(100),
            'contact_group' => $this->faker->word,
            'name' => $this->faker->sentence(3),
            'group_id' => $this->faker->text(100),
            'active' => '0',
            'voice' => 'knrPHWnBmmDHMoiMeP3l',
            'agent_number' => '+1234567890',
            'calling_number' => '+12334567890',
            'texting_number' => '+1234567890',
            'folder_id' => Folder::factory(),
            'godspeedoffers_api' => 'api_key',
            'organisation_id' => Organisation::inRandomOrder()->first()->id,
            'user_id' => User::inRandomOrder()->first()->id,
            'number_pool_id' => NumberPool::inRandomOrder()->first()->id,
            'generated_message'=>random_int(1,0)
        ];
    }
}
