<?php

namespace Database\Seeders;

use App\Models\SendingServer;
use Illuminate\Database\Seeder;

class SendingServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SendingServer::factory()->count(5)->create();


    }
}
