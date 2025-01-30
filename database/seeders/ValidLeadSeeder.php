<?php

namespace Database\Seeders;

use App\Models\ValidLead;
use App\Models\Workflow;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ValidLeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
             // Ensure there are some workflows in the database

             // Create contacts associated with existing workflows
             ValidLead::factory()->count(20)->create();
    }
}
