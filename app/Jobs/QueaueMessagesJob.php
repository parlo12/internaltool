<?php

namespace App\Jobs;

use App\Http\Controllers\ContactController;
use App\Models\ScheduledMessages;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class QueaueMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $phone;
    protected $content;
    protected $workflow_id;
    protected $type;
    protected $contact_id;
    protected $organisation_id;
    protected $dispatch_time;
    /**
     * Create a new job instance.
     */
    public function __construct($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $dispatch_time)
    {
        $this->phone = $phone;
        $this->content = $content;
        $this->workflow_id = $workflow_id;
        $this->type = $type;
        $this->contact_id = $contact_id;
        $this->organisation_id = $organisation_id;
        $this->dispatch_time = $dispatch_time;
    }

    /**
     * Execute the job.
     */
    public function handle(ContactController $controller): void
    {
        $rawPhone = $this->phone;
        $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone); // Keep digits only
        $formattedPhone = '+' . $cleanPhone;
        Log::info("QueaueMessagesJob started", [
            'phone' => $formattedPhone,
            'content' => $this->content,
            'workflow_id' => $this->workflow_id,
            'type' => $this->type,
            'contact_id' => $this->contact_id,
            'organisation_id' => $this->organisation_id,
            'dispatch_time' => $this->dispatch_time
        ]);
        ScheduledMessages::Create([
            'phone' => $formattedPhone,
            'content' => $this->content,
            'workflow_id' => $this->workflow_id,
            'type' => $this->type,
            'contact_id' => $this->contact_id,
            'organisation_id' => $this->organisation_id,
            'dispatch_time' => $this->dispatch_time
        ]);
        //$controller->send_message($this->phone, $this->content,$this->workflow_id,$this->type,$this->contact_id,$this->organisation_id);
    }
}
