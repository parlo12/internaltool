<?php

namespace Database\Seeders;

use App\Models\executedContracts;
use App\Models\Workflow;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExecutedContractsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
             // Ensure there are some workflows in the database

             // Create contacts associated with existing workflows
             executedContracts::factory()->count(20)->create();
    }
}
