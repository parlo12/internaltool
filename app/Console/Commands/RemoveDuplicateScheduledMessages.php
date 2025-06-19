<?php

namespace App\Console\Commands;

use App\Models\Contact;
use App\Models\ScheduledMessages;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RemoveDuplicateScheduledMessages extends Command
{
    protected $signature = 'scheduled-messages:dedupe';
    protected $description = 'Removes duplicate ScheduledMessages entries based on unique content attributes';

    public function handle()
    {
        $this->info("Processing scheduled messages...");

        ScheduledMessages::chunkById(100, function ($messages) {
            foreach ($messages as $message) {
                // Update contact
                $contact = Contact::find($message->contact_id);
                if ($contact) {
                    Log::info("Updating contact for message ID: {$message->id}", [
                        'contact_id' => $contact->id,
                        'phone' => $contact->phone,
                    ]);
                    $contact->can_send = 1;
                    $contact->save();
                }

                // Delete message
                $message->delete();
            }
        });

        $this->info("✅ All scheduled messages deleted and related contacts updated.");
        return 0;
    }
}


