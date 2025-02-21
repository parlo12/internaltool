<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnderContract;

class UnderContractSeeder extends Seeder
{
    public function run(): void
    {
        UnderContract::factory()->count(50)->create();
    }
}
