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

    public $mentorMessage;
    public $userMessage;

    /**
     * Create a new event instance.
     */
    public function __construct($mentorMessage, $userMessage)
    {
        $this->mentorMessage = $mentorMessage;
        $this->userMessage = $userMessage;
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
          'message' => [
            'mentor' => $this->mentorMessage,
            'user' => $this->userMessage,
          ],
       ];
    }
}
