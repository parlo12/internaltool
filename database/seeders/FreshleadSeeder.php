<?php

namespace Database\Seeders;

use App\Models\Freshlead;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FreshleadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Freshlead::factory()->count(10)->create();
    }
}
