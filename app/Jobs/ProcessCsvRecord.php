<?php

namespace App\Jobs;

use App\Events\CsvProcessingProgress;
use App\Models\Contact;
use App\Models\WorkflowProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessCsvRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $record, $new_workflow, $user, $crm_api, $group_id, $workflow_progress, $current, $batch_size;

    public function __construct(array $record, $new_workflow, $user, $crm_api, $group_id, $workflow_progress, $current, $batch_size)
    {
        $this->record = $record;
        $this->new_workflow = $new_workflow;
        $this->user = $user;
        $this->crm_api = $crm_api;
        $this->group_id = $group_id;
        $this->workflow_progress = $workflow_progress;
        $this->current = $current;
        $this->batch_size = $batch_size;
    }

    public function handle()
    {
        $phone = $this->normalizePhoneNumber($this->record['Phone number 1'] ?? null);

        // Create contact
        $contact = Contact::create([
            'uuid' => Str::uuid(),
            'workflow_id' => $this->new_workflow->id,
            'phone' => $phone,
            'can_send' => 1,
            'response' => 'No',
            'contact_name' => $this->record['Owner name'] ?? null,
            'status' => 'WAITING_FOR_QUEAUE',
            'cost' => 0,
            'subscribed' => 1,
            'organisation_id' => $this->user->organisation_id,
            'user_id' => $this->user->id,
            'zipcode' => $this->record['Property zip'] ?? null,
            'city' => $this->record['Property city'] ?? null,
            'state' => $this->record['Property state'] ?? null,
            'offer' => $this->record['Cash offer'] ?? null,
            'address' => $this->record['Property address'] ?? null,
            'agent' => $this->user->name,
            'email' => $this->record['email'] ?? null,
            'lead_score' => $this->record['Lead score'] ?? null,
            'gender' => $this->record['Gender'] ?? null,
            'age' => $this->record['Phone owner age 1'] ?? null,
            'novation' => $this->record['Novation offer'] ?? null,
            'creative_price' => $this->record['Creative price offer'] ?? null,
            'monthly' => $this->record['Monthly payment amount'] ?? null,
            'downpayment' => $this->record['Down payment amount'] ?? null,
            'list_price' => $this->record['List Price'] ?? null,
            'generated_message' => ""
        ]);

        $this->crm_api->createContact($this->group_id, [
            'PHONE' => $phone,
            'FIRST_NAME' => $this->record['Owner name'] ?? null,
            'ADDRESS' => $this->record['Property address'] ?? null,
            'CITY' => $this->record['Property city'] ?? null,
            'STATE' => $this->record['Property state'] ?? null,
            'ZIPCODE' => $this->record['Property zip'] ?? null,
            'OFFER_AMOUNT' => $this->record['Cash offer'] ?? null,
            'SALES_PERSON' => $this->user->name,
            'AGE' => $this->record['Phone owner age 1'] ?? null,
            'Gender' => $this->record['Gender'] ?? null,
            'LEAD_SCORE' => $this->record['Lead score'] ?? null,
            'NOVATION' => $this->record['Novation offer'] ?? null,
            'CREATIVEPRICE' => $this->record['Creative price offer'] ?? null,
            'MONTHLY' => $this->record['Monthly payment amount'] ?? null,
            'DOWNPAYMENT' => $this->record['Down payment amount'] ?? null,
            'EMAIL' => $this->record['email'] ?? null,
            'LIST_PRICE' => $this->record['List Price'] ?? null,
        ]);

        $workflow_progress = WorkflowProgress::find($this->workflow_progress->id);

        // Atomically increment and re-fetch
        $workflow_progress->increment('processed');
        $workflow_progress->refresh();

        if ($workflow_progress->total > 0) {
            $progress = min(round(($workflow_progress->processed / $workflow_progress->total) * 100, 2), 100);
        } else {
            $progress = 0;
        }

        $workflow_progress->progress = $progress;
        $workflow_progress->save();

        $status = ($workflow_progress->processed >= $workflow_progress->total) ? 'completed' : 'processing';
        $message = ($status === 'completed') ? 'CSV processing completed' : 'processing CSV record';
        $this->broadcastProgress(
            $workflow_progress->id,
            $workflow_progress->processed,
            $workflow_progress->total,
            $progress,
            $status,
            $message
        );
        Log::info("Processed record: {$workflow_progress->processed}, Total: {$workflow_progress->total}, Progress: {$workflow_progress->progress}%");

        $status = $workflow_progress->processed >= $workflow_progress->total ? 'completed' : 'processing';
        $message = $status === 'completed' ? 'CSV processing completed' : 'processing CSV record';

        $this->broadcastProgress(
            $workflow_progress->id,
            $workflow_progress->processed,
            $workflow_progress->total,
            $workflow_progress->progress,
            $status,
            $message
        );
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

    private function broadcastProgress($jobId, $current, $total, $progress, $status = 'processing', $message = null)
    {
        try {
            Log::info("progress is : $progress");

            broadcast(new CsvProcessingProgress(
                $jobId,
                $this->user->id,
                $this->new_workflow->id,
                $this->workflow_progress->name,
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
}
