<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Broadcasting\WhatsAppChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;

class InviteToHackathon extends Notification implements ShouldQueue
{
    use Queueable;

    public $teamLeader;
    public $team_q;
    public $teamMember;

    public function __construct($teamLeader, $team_q, $teamMember)
    {
        $this->teamLeader = $teamLeader;
        $this->team_q = $team_q;
        $this->teamMember = $teamMember;
    }

   public function via(object $notifiable): array
    {

 
        $channels = [];
        // Always store in database for audit/history
    

        // WhatsApp if enabled globally AND user accepts it
        if (GlobalNotificationSettingsService::isWhatsAppEnabled()==true && $notifiable->accept_whatsapp ==true) {
            $channels[] = WhatsAppChannel::class;
        }

        // Pusher (broadcast) if enabled globally AND user accepts it
        if (GlobalNotificationSettingsService::isPusherEnabled()==true && $notifiable->accept_pusher==true) {
            $channels[] = 'broadcast';
        }

        // Email if enabled globally AND user accepts it OR as default when nothing else is active
        if (GlobalNotificationSettingsService::isEmailEnabled() ==true && $notifiable->accept_email ==true ) { // only 'database' present
            $channels[] = 'mail';
        }

        if (!empty($channels)) {
               $channels[] = 'database';
        }


        return $channels;
    }

    public function toMail($notifiable)
    {
        // return (new MailMessage)
        //     ->subject('Invitation to Join a Hackathon Team')
        //     ->greeting('Hello ' . $notifiable->first_name)
        //     ->line("You have been invited to join the team: {$this->teamName}")
        //     ->line("Invited by: {$this->teamLeader->first_name} {$this->teamLeader->last_name}")
        //     ->action('Accept Invitation', url('/invitations/accept')) // عدّل الرابط حسب مشروعك
        //     ->line('Thank you for using our platform!');

            return (new MailMessage)
            ->subject(__('admin.Invitation_to_Join_a_Hackathon_Team'))
            ->markdown('emails.hackathon_inviteTeam', ['data' => $this->teamLeader, 'teamName' => $this->team_q, 'teamMember' => $this->teamMember]);
    }

    /**
     * WhatsApp message.
     */
    public function toWhatsapp(object $notifiable): string
    {
        return __('notifications.hackathon_invite.whatsapp', [
            'leader' => ($this->teamLeader->first_name_en ?? '').' '.($this->teamLeader->last_name_en ?? ''),
            'team' => $this->team_q,
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => __('admin.Invitation_to_Join_a_Hackathon_Team'),
            'message' => __('notifications.hackathon_invite.broadcast', ['team' => $this->team_q]),
            'time' => now()->toDateTimeString(),
        ]);
    }
}
