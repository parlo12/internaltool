<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\ContactImport;
use App\Models\ContactImportFailure;
use App\Models\ContactImportProgress;
use App\Models\ImportContacts;
use App\Models\User;
use App\Models\Workflow;
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
        $user = User::find($import->user_id);
        $mappings = json_decode($import->mappings, true);
        $progress = ContactImportProgress::find($import->progress_id);
        $old_workflow = Workflow::find($import->workflow_id);
        $new_workflow = Workflow::create([
            'name' => $import->filename,
            'contact_group' => $import->filename,
            'active' => 0,
            'group_id' => 1,
            'voice' => $old_workflow->voice,
            'agent_number' => $old_workflow->agent_number,
            'texting_number' => $old_workflow->texting_number,
            'calling_number' => $old_workflow->calling_number,
            'number_pool_id' => $old_workflow->number_pool_id,
            'folder_id' => $old_workflow->folder_id,
            'organisation_id' => $user->organisation_id,
            'godspeedoffers_api' => $old_workflow->godspeedoffers_api,
            'generated_message' => $old_workflow->generated_message,
            'user_id' => $user->id,
            'folder_id' => null,
        ]);
        foreach ($data as $contactData) {
            CreateContactJob::dispatch($import->user_id, $contactData, $new_workflow->id);
            //  $progress->increment('processed_contacts');
        }
    }
}
