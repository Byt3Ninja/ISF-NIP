<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MentorshipSessionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $mentorId;
    public $userId;
    public $Message;
    public $status;

    /**
     * Create a new event instance.
     */
    public function __construct(int $mentorId, int $userId, array $Message, string $status)
    {
        $this->mentorId = $mentorId;
        $this->userId = $userId;
        $this->Message = $Message;
        $this->status = $status;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('mentorship-session'),
        ];
    }

    /**
     * Customize the broadcast data.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'message' => $this->Message['email'],
            'status' => $this->status,
        ];
    }
}
