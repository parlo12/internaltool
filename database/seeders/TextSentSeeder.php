<?php

namespace Database\Seeders;

use App\Models\TextSent;
use App\Models\Workflow;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TextSentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
             // Ensure there are some workflows in the database

             // Create contacts associated with existing workflows
             TextSent::factory()->count(200)->create();
    }
}
