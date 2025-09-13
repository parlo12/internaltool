<?php

namespace App\Jobs;

use App\Models\ContactImport;
use App\Models\ContactImportProgress;
use App\Models\User;
use App\Models\Workflow;
use Database\Factories\ContactImportProgressFactory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


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

        $crm_api = new \App\Services\CRMAPIRequestsService($user->godspeedoffers_api);
        $response = $crm_api->createGroup($import->filename);
        $content = json_decode($response->getContent(), true);
        Log::info("API response: " . json_encode($content));
        if ($content['data']['status'] == 'error') {
            Log::error("Error creating group. Retrying with a different name: " . json_encode($content));
            $import->filename = $import->filename . '_' . Str::random(5);
            $response = $crm_api->createGroup($import->filename);
            $content = json_decode($response->getContent(), true);
            Log::info("API response after retry: " . json_encode($content));
            if ($content['data']['status'] == 'error') {
                foreach($import->contactData as $contactData) {
                    ContactImportProgressFactory::create([
                        'user_id' => $import->user_id,
                        'error' => 'Failed to create contact group: ' . ($content['data']['message'] ?? 'Unknown error'),
                        'phone' => $contactData['phone'] ?? null,
                        'contact_name' => $contactData['contact_name'] ?? null,
                    ]); 
                }
                Log::error("Error creating group after retry: " . json_encode($content));
                return;
            }
        }
        Log::info("Group created successfully: " . $import->filename);
        $group_id = $content['data']['data']['uid'] ?? null;
        $old_workflow = Workflow::find($import->workflow_id);
        $new_workflow = Workflow::create([
            'name' => $import->filename,
            'contact_group' => $import->filename,
            'active' => 0,
            'group_id' => $group_id,
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
            CreateContactJob::dispatch($import->user_id, $contactData, $new_workflow->id,$group_id);
            //  $progress->increment('processed_contacts');
        }
    }
}
