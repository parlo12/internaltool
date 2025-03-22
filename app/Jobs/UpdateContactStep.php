<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Step;
use App\Models\Workflow;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;


class UpdateContactStep implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contact;

    /**
     * Create a new job instance.
     *
     * @param Contact $contact
     */
    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $contact = $this->contact;

        if ($contact->response === "No") {
            $workflow = Workflow::find($contact->workflow_id);
            $steps_flow_array = explode(',', $workflow->steps_flow);
            $current_step = $contact->current_step;
            $current_step_key = array_search($current_step, $steps_flow_array);

            if ($current_step_key !== false && $current_step_key < count($steps_flow_array) - 1) {
                // Move to the next step
                $next_step = $steps_flow_array[$current_step_key + 1];
                $step = Step::find($next_step);

                if ($step) {
                    $now = Carbon::now();

                    $contact->update([
                        'current_step' => $next_step,
                        'can_send' => 1,
                        'can_send_after' => $now->addSeconds($step->delay * 60)->toDateTimeString(),
                        'status' => "WAITING_FOR_QUEUE",
                    ]);
                }
            } elseif ($current_step_key === count($steps_flow_array) - 1) {
            } else {
                Log::info("Step {$current_step} is not available in workflow {$workflow->id}.");
            }
        }
    }
}
