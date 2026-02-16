<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatCallResponse implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $status; // 'accepted' or 'rejected'
    public $toUserId;

    public function __construct($status, $toUserId)
    {
        $this->status = $status;
        $this->toUserId = $toUserId;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('user.' . $this->toUserId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ChatCallResponse';
    }
}
