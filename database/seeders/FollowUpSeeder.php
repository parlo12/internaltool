<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FollowUp;

class FollowUpSeeder extends Seeder
{
    public function run(): void
    {
        FollowUp::factory()->count(50)->create();
    }
}
