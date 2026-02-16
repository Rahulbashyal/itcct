<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatCallSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $fromUser;
    public $toUserId;
    public $type; // 'audio' or 'video'
    public $channelId;

    public function __construct($fromUser, $toUserId, $type, $channelId)
    {
        $this->fromUser = $fromUser;
        $this->toUserId = $toUserId;
        $this->type = $type;
        $this->channelId = $channelId;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('user.' . $this->toUserId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ChatCallSent';
    }
}
