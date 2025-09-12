<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\ContactImport;
use App\Models\ContactImportFailure;
use App\Models\ContactImportProgress;
use App\Models\ImportContacts;
use Illuminate\Bus\Queueable as BusQueueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

use Illuminate\Support\Facades\Storage;

class ImportContactsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $importId) {}

    public function handle()
    {
        $import = ContactImport::findOrFail($this->importId);
        $data = json_decode(Storage::get($import->data_file), true);
        $mappings = json_decode($import->mappings, true);
        $progress = ContactImportProgress::find($import->progress_id);
        foreach ($data as $contactData) {
            CreateContactJob::dispatch($import->user_id, $contactData);
          //  $progress->increment('processed_contacts');
        }
    }
}
