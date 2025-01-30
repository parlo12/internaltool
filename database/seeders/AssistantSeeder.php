<?php

namespace Database\Seeders;

use App\Models\Assistant;
use Illuminate\Database\Seeder;

class AssistantSeeder extends Seeder
{
    public function run()
    {
        // Create 10 sample assistants
        Assistant::factory()->count(10)->create();
    }
}
