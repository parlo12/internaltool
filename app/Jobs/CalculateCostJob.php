<?php

namespace App\Jobs;

use App\Http\Controllers\ContactController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateCostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $contact;

    /**
     * Create a new job instance.
     */
    public function __construct($contact)
    {
        $this->contact=$contact;
    }

    /**
     * Execute the job.
     */
    public function handle(ContactController $controller): void
    {
        $controller->get_and_record_cost($this->contact);
    }
}
