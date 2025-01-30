<?php

namespace Database\Seeders;

use App\Models\CancelledContracts;
use App\Models\ClosedDeal;
use App\Models\Workflow;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClosedDealSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
             // Ensure there are some workflows in the database

             // Create contacts associated with existing workflows
              ClosedDeal::factory()->count(20)->create();
    }
}
