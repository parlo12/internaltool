<?php

namespace Database\Seeders;

use App\Models\MessageTypes;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MessageTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MessageTypes::factory()->count(10)->create();
    }
}
