<?php

namespace App\Jobs;

use App\Http\Controllers\ContactController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ResponseCheckJob implements ShouldQueue
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
        $controller->contact_response_check($this->contact);
    }
}
