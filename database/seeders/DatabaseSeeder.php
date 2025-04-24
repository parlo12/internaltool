<?php

namespace Database\Seeders;

use App\Models\CancelledContracts;
use App\Models\Organisation;
use App\Models\Spintax;
use App\Models\TextSent;
use App\Models\User;
use App\Models\ValidLead;
use App\Models\AI_Lead;
use App\Models\Freshlead;
use App\Models\SendingServer;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(3)->create();

        User::factory()->create([
            'name' => 'eliud',
            'email' => 'testpass@gmail.com',
            'password' => 'testpass',
            'is_admin' => 1,
            'godspeedoffers_api' => "4|jXPTqiIGVtOSvNDua3TfSlRXLFU4lqWPcPZNgfN3f6bacce0"

        ]);
        $this->call([
            OrganisationSeeder::class,
            NumberPoolSeeder::class,
            SendingServerSeeder::class,
            FolderSeeder::class,
            WorkflowSeeder::class,
            AssistantSeeder::class,
            ContactSeeder::class,
            MessageTypesSeeder::class,
            CancelledContractsSeeder::class,
            ClosedDealSeeder::class,
            ExecutedContractsSeeder::class,
            OffersSeeder::class,
            ValidLeadSeeder::class,
            TextSentSeeder::class,
            CallsSentSeeder::class,
            SpintaxSeeder::class,
            NumberSeeder::class,
            AICallSeeder::class,
            CsvFileSeeder::class,
            KnowledgeBaseSeeder::class,
            WrongNumberSeeder::class,
            UnderContractSeeder::class,
            FollowUpSeeder::class,
            FreshleadSeeder::class,
            StepSeeder::class,
            AICallSeeder::class,

        ]);
    }
}
