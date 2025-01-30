<?php

namespace Database\Seeders;

use App\Models\AI_Lead;
use Illuminate\Database\Seeder;

class AILeadSeeder extends Seeder
{
    public function run()
    {
        AILead::factory()->count(50)->create();
    }
}
