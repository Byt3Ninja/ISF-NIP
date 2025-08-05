<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

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

    public function via($notifiable)
    {
        return ['mail'];
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
}
