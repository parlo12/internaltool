<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateScheduledMessages extends Command
{
    protected $signature = 'scheduled-messages:dedupe';
    protected $description = 'Removes duplicate ScheduledMessages entries based on unique content attributes';

    public function handle()
    {
        $this->info("🧹 Removing duplicate scheduled messages using raw SQL...");

        $deleted = DB::statement("
            DELETE sm1 FROM scheduled_messages sm1
            INNER JOIN scheduled_messages sm2
            ON
                sm1.phone = sm2.phone AND
                sm1.content = sm2.content AND
                sm1.workflow_id = sm2.workflow_id AND
                sm1.type = sm2.type AND
                sm1.contact_id = sm2.contact_id AND
                sm1.organisation_id = sm2.organisation_id AND
                sm1.dispatch_time = sm2.dispatch_time AND
                sm1.id > sm2.id
        ");

        $this->info("✅ Duplicates removed using SQL join. (Query completed; number of rows deleted may not be returned)");

        return 0;
    }
}
