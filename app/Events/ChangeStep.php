<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChangeStep implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;
    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('step-changes'),
        ];
    }


    public function broadcastWith()
    {
        return [
            'company_name' => $this->data['company_name_en'] ?? 'Unknown Company',
            'last_step' => $this->data['last_step'] ?? 'N/A',
            'new_step' => $this->data['new_step'] ?? 'N/A',
            'organization_name' => $this->data['organization_name_en'] ?? 'Unknown Organization',
        ];
    }
}
