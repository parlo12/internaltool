<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Services\DynamicTagsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PrepareMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $uuid;
    protected $group_id;
    protected $api_key;
    protected $step;
    protected $contact;
    protected $dispatchTime;

    public function __construct($uuid, $group_id, $api_key, $step, $contact, $dispatchTime)
    {
        $this->uuid = $uuid;
        $this->group_id = $group_id;
        $this->api_key = $api_key;
        $this->step = $step;
        $this->contact = $contact;
        $this->dispatchTime = $dispatchTime;
    }

    public function handle()
    {
        $DynamicTagsService = new DynamicTagsService($this->api_key);

        // Compose and spintax the message
        $message =  $DynamicTagsService->composeMessage($this->contact, $this->step->content);
        $message =  $DynamicTagsService->spintax($message);

        // Dispatch the QueaueMessagesJob with the composed message
        QueaueMessagesJob::dispatch(
            '+'.$this->contact->phone,
            $message,
            $this->step->workflow_id,
            $this->step->type,
            $this->contact->id,
            $this->contact->organisation_id
        )->delay($this->dispatchTime);

        // Update the contact's status and next step time
        $step_delay = (int)$this->step->delay;
        $next_step_after = Carbon::parse($this->dispatchTime)->addSeconds($step_delay * 60);

        $contactModel = Contact::find($this->contact->id);
        $contactModel->can_send_after = $next_step_after;
        $contactModel->status = "QUEUED";
        $contactModel->can_send = 0;
        $contactModel->save();
        if ($contactModel->can_send_after) {
            UpdateContactStep::dispatch($contactModel)->delay(Carbon::parse($contactModel->can_send_after));
            Log::info("Dispatched UpdateContactStep", [
                'contact_id' => $contactModel->id,
                'can_send_after' => $contactModel->can_send_after
            ]);
        } else {
            Log::warning("Skipping UpdateContactStep due to missing can_send_after", [
                'contact_id' => $contactModel->id
            ]);
        }
        Log::info('Scheduled contact: ' . $contactModel->id . ' at ' . $this->dispatchTime);
        Log::info("The contact will move to the next step on $next_step_after");
        Log::info($message);
    }
}

