<?php

namespace Database\Seeders;

use App\Models\CallsSent;
use App\Models\Workflow;
use Database\Factories\CallsSentFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CallsSentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Create contacts associated with existing workflows
        CallsSent::factory()->count(200)->create();
    }
}
