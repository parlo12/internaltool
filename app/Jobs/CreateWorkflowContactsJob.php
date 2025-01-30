<?php

namespace App\Jobs;

use App\Http\Controllers\WorkflowController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateWorkflowContactsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $contact_uid;
    protected $contact_group;
    protected $workflow_id;
    protected $phone;
    protected $organisation;
    public function __construct($contact_uid,$contact_group,$workflow_id,$phone,$organisation)
    {
        $this->contact_uid=$contact_uid;
        $this->contact_group=$contact_group;
        $this->workflow_id=$workflow_id;
        $this->phone=$phone;
        $this->organisation=$organisation;
    }

    /**
     * Execute the job.
     */
    public function handle(WorkflowController $controller): void
    {
        $controller->create_contacts_for_workflows($this->contact_uid, $this->contact_group,$this->workflow_id,$this->phone,$this->organisation);

    }
}
