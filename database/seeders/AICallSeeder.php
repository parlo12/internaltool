<?php

namespace Database\Seeders;

use App\Models\AICall;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AICallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AICall::factory()->count(1000)->create();

    }
}
