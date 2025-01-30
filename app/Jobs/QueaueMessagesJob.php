<?php

namespace App\Jobs;

use App\Http\Controllers\ContactController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class QueaueMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $phone;
    protected $content;
    protected $workflow_id;
    protected $type;
    protected $contact_id;
    protected $organisation_id;
    /**
     * Create a new job instance.
     */
    public function __construct($phone,$content,$workflow_id,$type,$contact_id,$organisation_id)
    {
        $this->phone=$phone;
        $this->content=$content;
        $this->workflow_id=$workflow_id;
        $this->type=$type;
        $this->contact_id=$contact_id;
        $this->organisation_id=$organisation_id;
        }

    /**
     * Execute the job.
     */
    public function handle(ContactController $controller): void
    {
        $controller->send_message($this->phone, $this->content,$this->workflow_id,$this->type,$this->contact_id,$this->organisation_id);
    }
}
