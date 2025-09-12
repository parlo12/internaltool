<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ContactImportProgress implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $progress;
    public int $userId;

    public function __construct(int $userId, int $progress)
    {
        $this->progress = $progress;
        $this->userId= $userId;

        Log::info('Event created', [
            'progress' => $this->progress,
            'userId' => $this->userId
        ]);
    }

    public function broadcastOn(): Channel
    {
        return new Channel("import.progress.{$this->userId}");
    }
    public function broadcastAs(): string
    {
        return 'ContactImportProgress';
    }
}