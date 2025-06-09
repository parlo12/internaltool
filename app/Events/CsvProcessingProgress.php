<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CsvProcessingProgress implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $jobId;
    public $userId;
    public $workflowId;
    public $fileName;
    public $current;
    public $total;
    public $progress;
    public $status;
    public $message;

    public function __construct($jobId, $userId, $workflowId, $fileName, $current, $total,$progress, $status = 'processing', $message = null)
    {
        $this->jobId = $jobId;
        $this->userId = $userId;
        $this->workflowId = $workflowId;
        $this->fileName = $fileName;
        $this->current = $current;
        $this->total = $total;
        $this->progress = $progress;
        $this->status = $status;
        $this->message = $message;

        // Add logging
        Log::info('CsvProcessingProgress event dispatched', [
            'jobId'      => $this->jobId,
            'userId'     => $this->userId,
            'workflowId' => $this->workflowId,
            'fileName'   => $this->fileName,
            'current'    => $this->current,
            'total'      => $this->total,
            'progress'   => $this->progress,
            'status'     => $this->status,
            'message'    => $this->message,
        ]);
    }
   public function broadcastWith()
{
    $payload = [
        'jobId' => $this->jobId,
        'progress' => $this->progress,
        'status' => $this->status,
        'message' => $this->message,
        'fileName' => $this->fileName,
        'current' => $this->current,
        'total' => $this->total,
        'workflowId' => $this->workflowId,
        'userId' => $this->userId,
        'timestamp' => now()->toIso8601String(),
    ];

    Log::info('CsvProcessingProgress broadcast payload', $payload);

    return $payload;
}


    public function broadcastOn():Channel
    {
        return new Channel('csv-progress.user.' . $this->userId);
    }

    public function broadcastAs()
    {
        return 'csv.progress';
    }
}
