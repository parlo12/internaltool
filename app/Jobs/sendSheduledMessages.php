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

class sendSheduledMessages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected $phone;
    protected $content;
    protected $workflow_id;
    protected $contact_id;
    protected $organisation_id;
    protected $message_id;
    protected $type;
    public function __construct($phone, $content, $workflow_id, $contact_id, $organisation_id, $message_id, $type)
    {
        $this->phone = $phone;
        $this->content = $content;
        $this->workflow_id = $workflow_id;
        $this->contact_id = $contact_id;
        $this->organisation_id = $organisation_id;
        $this->message_id = $message_id;
        $this->type = $type;
    }


    /**
     * Execute the job.
     */
    public function handle(ContactController $controller): void
    {
        // Log::info("sendSheduledMessages started", [
        //     'phone' => '+'.$this->phone,
        //     'content' => $this->content,
        //     'workflow_id' => $this->workflow_id,
        //     'contact_id' => $this->contact_id,
        //     'organisation_id' => $this->organisation_id
        // ]);
        // $message = ScheduledMessages::find($this->message_id);

        // if ($message) {
        //     $message->delete();
        // }
        // $controller->send_message('+'.$this->phone, $this->content, $this->workflow_id, $this->type, $this->contact_id, $this->organisation_id);

        // Log::info("Message sent and deleted from scheduled messages", [
        //     'message_id' => $this->message_id
        // ]);
    }
}
