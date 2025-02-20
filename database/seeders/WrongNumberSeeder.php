<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WrongNumber;

class WrongNumberSeeder extends Seeder
{
    public function run(): void
    {
        WrongNumber::factory()->count(10)->create();
    }
}
