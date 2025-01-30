<?php

namespace Database\Factories;

use App\Models\Assistant;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssistantFactory extends Factory
{
    protected $model = Assistant::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'prompt' => $this->faker->paragraph(),
            'file1' => $this->faker->filePath(),
            'file2' => $this->faker->filePath(),
            'file1_id' => $this->faker->filePath(),
            'file2_id' => $this->faker->filePath(),
            'openAI_id' => $this->faker->filePath(),
            'max_wait_time' => $this->faker->filePath(),
            'min_wait_time' => $this->faker->filePath(),
            'maximum_messages' => $this->faker->filePath(),
            'sleep_time' => $this->faker->filePath(),
            'openAI' => $this->faker->filePath(),
        ];
    }
}
