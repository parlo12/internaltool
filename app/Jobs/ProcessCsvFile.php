<?php

namespace App\Jobs;

use App\Events\CsvProcessingProgress;
use App\Models\Contact;
use App\Models\Step;
use App\Models\Workflow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use Illuminate\Support\Str;

class ProcessCsvFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $filePath;
    public int $workflowId;
    public int $folderId;
    public $user;
    public string $fileName;
    public ?int $newWorkflowId = null;

    public function __construct(string $filePath, int $workflowId, int $folderId, $user)
    {
        $this->filePath = $filePath;
        $this->workflowId = $workflowId;
        $this->folderId = $folderId;
        $this->user = $user;

        // Compute file name in constructor
        $fileName = basename($filePath);
        $fileName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $fileName);
        $this->fileName = strtolower(trim($fileName, '_'));
    }

    public function handle(): void
    {
     
        Log::info("Starting CSV processing job for file: " . $this->filePath);
        $path = $this->filePath;
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);
        $totalRecords = $csv->count() - 1;
        Log::info("Total records in CSV: " . $totalRecords);
        if ($totalRecords <= 0) {
            Log::error("CSV file is empty or has no valid records.");
            $this->broadcastProgress(
                'unknown',
                0,
                0,
                'failed',
                'CSV file is empty or has no valid records.'
            );
            return;
        }
        $workflow_progress = \App\Models\WorkflowProgress::create([
            'name' => $this->fileName,
            'user_id' => $this->user->id,
            'progress' => 0,
            'processed' => 0,
            'total' => $totalRecords,
        ]);
        $jobId = $workflow_progress->id;

        // Broadcast initialization
        $this->broadcastProgress($jobId, 0, 0, 0, 'initializing', 'Starting CSV processing');

        Log::info("Processing CSV in job: " . $this->filePath);
        Log::info("Workflow ID: " . $this->workflowId);
        Log::info("Folder ID: " . $this->folderId);

        $this->broadcastProgress($jobId, 0, 0, 0, 'processing', 'Creating contact group');

        $crm_api = new \App\Services\CRMAPIRequestsService($this->user->godspeedoffers_api);
        $response = $crm_api->createGroup($this->fileName);
        $content = json_decode($response->getContent(), true);
        Log::info("API response: " . json_encode($content));

        if ($content['data']['status'] == 'error') {
            Log::error("Error creating group. Retrying with a different name: " . json_encode($content));
            $this->fileName = $this->fileName . '_' . Str::random(5);
            $response = $crm_api->createGroup($this->fileName);
            $content = json_decode($response->getContent(), true);
            Log::info("API response after retry: " . json_encode($content));

            if ($content['data']['status'] == 'error') {
                Log::error("Error creating group after retry: " . json_encode($content));
                $this->broadcastProgress(
                    $jobId,
                    0,
                    0,
                    'failed',
                    'Failed to create contact group: ' . ($content['data']['message'] ?? 'Unknown error')
                );
                return;
            }
        }

        Log::info("Group created successfully: " . $this->fileName);
        $group_id = $content['data']['data']['uid'] ?? null;

        $this->broadcastProgress($jobId, 0, 0, 0, 'processing', 'Creating workflow');

        $old_workflow = Workflow::find($this->workflowId);
        $new_workflow = Workflow::create([
            'name' => $this->fileName,
            'contact_group' => $this->fileName,
            'active' => 0,
            'group_id' => $group_id,
            'voice' => $old_workflow->voice,
            'agent_number' => $old_workflow->agent_number,
            'texting_number' => $old_workflow->texting_number,
            'calling_number' => $old_workflow->calling_number,
            'number_pool_id' => $old_workflow->number_pool_id,
            'folder_id' => $old_workflow->folder_id,
            'organisation_id' => $this->user->organisation_id,
            'godspeedoffers_api' => $old_workflow->godspeedoffers_api,
            'generated_message' => $old_workflow->generated_message,
            'user_id' => $this->user->id,
            'folder_id' => $this->folderId,
        ]);

        $this->newWorkflowId = $new_workflow->id;

        $this->broadcastProgress($jobId, 0, 0, 0, 'processing', 'Workflow created, copying steps');

        if (!empty($old_workflow->steps_flow)) {
            $steps_flow_array = explode(',', $old_workflow->steps_flow);
            foreach ($steps_flow_array as $step_id) {
                try {
                    $step_to_copy = Step::findOrFail($step_id);
                    $new_step = Step::create([
                        'workflow_id' => $new_workflow->id,
                        'type' => $step_to_copy->type,
                        'content' => $step_to_copy->content,
                        'delay' => $step_to_copy->delay,
                        'name' => $step_to_copy->name,
                        'custom_sending' => $step_to_copy->custom_sending,
                        'start_time' => $step_to_copy->start_time,
                        'end_time' => $step_to_copy->end_time,
                        'batch_size' => $step_to_copy->batch_size,
                        'batch_delay' => $step_to_copy->batch_delay,
                        'step_quota_balance' => $step_to_copy->step_quota_balance,
                        'days_of_week' => $step_to_copy->days_of_week,
                        'generated_message' => $step_to_copy->generated_message,
                    ]);
                    $new_steps_flow = $new_workflow->steps_flow ? explode(',', $new_workflow->steps_flow) : [];
                    $new_steps_flow[] = $new_step->id;
                    $new_workflow->steps_flow = implode(',', $new_steps_flow);
                    $new_workflow->save();
                } catch (\Exception $e) {
                    Log::error("Error copying step ID {$step_id}: {$e->getMessage()}");
                    $this->broadcastProgress(
                        $jobId,
                        0,
                        0,
                        'warning',
                        "Error copying step {$step_id}: " . $e->getMessage()
                    );
                }
            }
        }

        $this->broadcastProgress($jobId, 0, 0, 0, 'processing', 'Processing CSV records');

        $path = $this->filePath;
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);
        $totalRecords = $csv->count() - 1;  // Exclude header row

        // Broadcast processing start with total records
        $this->broadcastProgress($jobId, 0, $totalRecords, 0, 'processing');

        $current = 0;
        $batchSize = max(1, min(50, (int)($totalRecords / 20))); // Broadcast every 50 records or 5%

        foreach ($csv->getRecords() as $record) {
            $current++;

            // Dispatch each record processing as a job
            ProcessCsvRecord::dispatch($record, $new_workflow, $this->user, $crm_api, $group_id, $workflow_progress, $current, $batchSize)
                ->delay(now()->addSeconds(1)); // Delay to avoid overwhelming the queue


        }



        // Broadcast completion
    }

    public function failed(\Throwable $exception)
    {
        $jobId = $this->job ? $this->job->getJobId() : 'unknown';
        $this->broadcastProgress(
            $jobId,
            0,
            0,
            'failed',
            'Job failed: ' . $exception->getMessage()
        );
        Log::error("CSV processing job failed: " . $exception->getMessage());
    }

    private function broadcastProgress($jobId, $current, $total, $progress, $status = 'processing', $message = null)
    {
        try {
            broadcast(new CsvProcessingProgress(
                $jobId,
                $this->user->id,
                $this->newWorkflowId,
                $this->fileName,
                $current,
                $total,
                $progress,
                $status,
                $message
            ));
        } catch (\Exception $e) {
            Log::error("Failed to broadcast progress: " . $e->getMessage());
        }
    }

    private function normalizePhoneNumber($phone)
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return $digits;
        }
        if (strlen($digits) === 10) {
            return '1' . $digits;
        }
        return null;
    }
}
