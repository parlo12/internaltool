<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCsvFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $filePath;
    public int $workflowId;
    public int $folderId;
    public function __construct(string $filePath, int $workflowId, int $folderId)
    {
        $this->filePath = $filePath;
        $this->workflowId = $workflowId;
        $this->folderId = $folderId;
    }


    public function handle(): void
    {
        // Further processing logic here, e.g., parsing, inserting to DB, analytics...
        Log::info("Processing CSV in job: " . $this->filePath);
        Log::info("Workflow ID: " . $this->workflowId);
        Log::info("Folder ID: " . $this->folderId);
    }
}
