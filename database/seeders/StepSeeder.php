<?php

namespace Database\Seeders;

use App\Models\MessageTypes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Step;
use App\Models\Workflow;

class StepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure there are some workflows in the database


        // Create steps associated with existing workflows
        Step::factory()->count(30)->create();
    }
}
