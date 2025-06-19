<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ScheduledMessages;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateScheduledMessages extends Command
{
    protected $signature = 'scheduled-messages:dedupe';
    protected $description = 'Removes duplicate ScheduledMessages entries based on unique content attributes';

    public function handle()
    {
        $this->info("Scanning for duplicate scheduled messages...");

        $duplicates = ScheduledMessages::select(
                'phone',
                'content',
                'workflow_id',
                'type',
                'contact_id',
                'organisation_id',
                'dispatch_time',
                DB::raw('MIN(id) as keep_id')
            )
            ->groupBy(
                'phone',
                'content',
                'workflow_id',
                'type',
                'contact_id',
                'organisation_id',
                'dispatch_time'
            )
            ->get();

        $keepIds = $duplicates->pluck('keep_id')->toArray();

        // Optional: confirm count before delete
        $toDelete = ScheduledMessages::whereNotIn('id', $keepIds)
            ->whereIn(DB::raw('(phone, content, workflow_id, type, contact_id, organisation_id, dispatch_time)'), function ($query) {
                $query->select(
                        'phone',
                        'content',
                        'workflow_id',
                        'type',
                        'contact_id',
                        'organisation_id',
                        'dispatch_time'
                    )
                    ->from('scheduled_messages')
                    ->groupBy(
                        'phone',
                        'content',
                        'workflow_id',
                        'type',
                        'contact_id',
                        'organisation_id',
                        'dispatch_time'
                    )
                    ->havingRaw('COUNT(*) > 1');
            });

        $count = $toDelete->count();
        $this->warn("Found $count duplicate messages to delete...");

        if ($count > 0) {
            $deleted = $toDelete->delete();
            $this->info("✅ Deleted $deleted duplicate scheduled messages.");
        } else {
            $this->info("No duplicates found.");
        }

        return 0;
    }
}
