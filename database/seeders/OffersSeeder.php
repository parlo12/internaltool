<?php

namespace Database\Seeders;

use App\Models\offers;
use App\Models\Workflow;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OffersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            // Ensure there are some workflows in the database

            // Create contacts associated with existing workflows
            offers::factory()->count(20)->create();
    }
}
