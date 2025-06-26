<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FileDownloadReady implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $fileId;

    public function __construct($fileId)
    {
        $this->fileId = $fileId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('files');
    }

    public function broadcastAs()
    {
        return 'file.download.ready';
    }
}
