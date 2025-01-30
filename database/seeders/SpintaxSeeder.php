<?php

namespace Database\Seeders;

use App\Models\Spintax;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpintaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Spintax::factory()->count(5)->create();

    }
}
