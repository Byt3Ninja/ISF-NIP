<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Broadcasting\WhatsAppChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class StatusHackathonInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;
    public $userteam;
    public $teams;
    public $status;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $userteam, $teams, $status)
    {
        $this->user = $user;
        $this->userteam = $userteam;
        $this->teams = $teams;
        $this->status = $status; // Accept or Reject
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        $channels = [];
        // Always store in database
        $channels[] = 'database';

        // WhatsApp if enabled
        if (!empty($notifiable->accept_whatsapp)) {
            $channels[] = WhatsAppChannel::class;
        }

        // Pusher (broadcast) if enabled
        if (!empty($notifiable->accept_pusher)) {
            $channels[] = 'broadcast';
        }

        // Email if user accepts OR default to email if nothing else (besides database) selected
        if (!empty($notifiable->accept_email) || count($channels) === 1) { // only 'database' present
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
            return (new MailMessage)
            ->subject(__('admin.Status_Invitation'))
            ->markdown('emails.acceptInviteHackathon', ['user' => $this->user, 'teams' => $this->teams, 'status' => $this->status]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'user'   => $this->user->id,
            'userteam'   => $this->userteam->id,
            'member' => $this->teams->id,
            'status' => $this->status,
        ];
    }

    public function toWhatsapp(object $notifiable): string
    {
        return __('notifications.hackathon_invite.whatsapp', [
            'leader' => ($this->teamLeader->first_name_en ?? '').' '.($this->teamLeader->last_name_en ?? ''),
            'team' => $this->userteam->{'first_name_en'} . ' ' . $this->userteam->{'last_name_en'},
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => __('admin.Status_Invitation'),
            'message' => __('notifications.hackathon_invite.broadcast', ['team' => $this->userteam]),
            'time' => now()->toDateTimeString(),
        ]);
    }
}
