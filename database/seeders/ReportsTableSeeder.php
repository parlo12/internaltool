<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReportsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groupNames = ['Alabama', 'Kentucky', 'Ohio'];
        $callStatus = ['ANSWERED', 'FAILED', 'RECORDING_PLAYED','VOICEMAIL_LEFT','CALL_TRANSFERRED','RECORDING_PLAYED_NOT_TRANSFERRED','SUCCESSFUL','QUEUED'];
        // Generating sample data
        for ($i = 0; $i < 100; $i++) {
            DB::table('reports')->insert([
                'contact_uid' => Str::uuid(),
                'group_name' => $groupNames[array_rand($groupNames)],
                'call_sid' => Str::uuid(),
                'contact_name' => 'contact'.$i,
                'call_status' => $callStatus[array_rand($callStatus)], // or any other status
                'phone' => '123-456-7890',
                'campaign_id' => Str::uuid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
