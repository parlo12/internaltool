<?php

namespace Database\Seeders;

use App\Models\NumberPool;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NumberPoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NumberPool::factory()->count(10)->create();
    }
}
