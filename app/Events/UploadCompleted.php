<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UploadCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $file;
    public int $inserted;
    public int $updated;
    public int $skipped;

    public function __construct($file, $inserted, $updated, $skipped)
    {
        $this->file = $file;
        $this->inserted = $inserted;
        $this->updated = $updated;
        $this->skipped = $skipped;
    }

    public function broadcastOn(): array
    {
        return new \Illuminate\Broadcasting\Channel('csv-upload');
    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}
