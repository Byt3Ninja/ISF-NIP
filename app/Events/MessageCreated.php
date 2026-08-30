<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Namu\WireChat\Models\Message;

class MessageCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        $sendable = $this->message->sendable;
        $senderName = null;
        $senderAvatar = null;
        $senderInitials = '?';
        if ($sendable instanceof \App\User) {
            $senderName = trim(($sendable->first_name_en ?: $sendable->first_name_ar) . ' ' .
                               ($sendable->last_name_en ?: $sendable->last_name_ar)) ?: $sendable->email;
            $senderAvatar = $sendable->img_url ? \App\User::image_handler($sendable->img_url) : null;
            $senderInitials = self::initialsFromName($senderName);
        }

        return [
            'message' => [
                'id' => $this->message->id,
                'body' => $this->message->body,
                'type' => $this->message->type,
                'sendable_id' => $this->message->sendable_id,
                'sendable_type' => $this->message->sendable_type,
                'conversation_id' => $this->message->conversation_id,
                'created_at' => $this->message->created_at,
                'sendable' => $this->message->sendable,
                'sender_name' => $senderName,
                'sender_avatar_url' => $senderAvatar,
                'sender_initials' => $senderInitials,
            ]
        ];
    }

    /** "User Name" => "UN"; single token => first two letters; empty => "?". Mirrors ChatController::initialsFor. */
    private static function initialsFromName(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') { return '?'; }
        $parts = preg_split('/\s+/', $name);
        if (count($parts) === 1) { return strtoupper(mb_substr($parts[0], 0, 2)); }
        return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts) - 1], 0, 1));
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'MessageCreated';
    }
}
