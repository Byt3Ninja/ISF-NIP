<?php

namespace App\Notifications;

use App\Services\GlobalNotificationSettingsService;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use App\Broadcasting\WhatsAppChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Throwable;

class InviteToHackathon extends Notification implements ShouldQueue
{
  use Queueable, ConfiguresSmtp;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

    /**
     * Team leader.
     *
     * @var mixed
     */
    public $teamLeader;

    /**
     * Team name.
     *
     * @var string
     */
    public $team_q;

    /**
     * Team member.
     *
     * @var mixed
     */
    public $teamMember;

    public function __construct($teamLeader, $team_q, $teamMember)
    {
        $this->teamLeader = $teamLeader;
        $this->team_q = $team_q;
        $this->teamMember = $teamMember;
    }

 /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $org = $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;
        
        // Configure SMTP if settings exist
        $this->configureSmtp($smtpSettings);
        
        // Organization channel toggles (default to true when not configured)
        $orgAcceptEmail = $smtpSettings?->accept_email ?? true;
        $orgAcceptWhatsapp = $smtpSettings?->accept_whatsapp ?? true;
        $orgAcceptPusher = $smtpSettings?->accept_pusher ?? true;

        $channels = [];

        // WhatsApp if enabled globally AND organization/user accepts it
        if (GlobalNotificationSettingsService::isWhatsAppEnabled() 
            && $orgAcceptWhatsapp 
            && $notifiable->accept_whatsapp) {
            $channels[] = WhatsAppChannel::class;
        }

        // Pusher (broadcast) if enabled globally AND organization/user accepts it
        if (GlobalNotificationSettingsService::isPusherEnabled() 
            && $orgAcceptPusher 
            && $notifiable->accept_pusher) {
            $channels[] = 'broadcast';
        }

        // Email if enabled globally AND organization/user accepts it
        if (GlobalNotificationSettingsService::isEmailEnabled() 
            && $orgAcceptEmail 
            && $notifiable->accept_email) {
            $channels[] = 'mail';
        }

        // Always store in database for audit/history if any channels are active
        if (!empty($channels)) {
            $channels[] = 'database';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        $org = $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;
        
        // Get from address and name
        $from = $this->getFromAddress($smtpSettings);
        // return (new MailMessage)
        //     ->subject('Invitation to Join a Hackathon Team')
        //     ->greeting('Hello ' . $notifiable->first_name)
        //     ->line("You have been invited to join the team: {$this->teamName}")
        //     ->line("Invited by: {$this->teamLeader->first_name} {$this->teamLeader->last_name}")
        //     ->action('Accept Invitation', url('/invitations/accept')) // عدّل الرابط حسب مشروعك
        //     ->line('Thank you for using our platform!');

        $orgData = $this->buildEmailOrganizationData($org);

            return (new MailMessage)
            ->subject(__('admin.Invitation_to_Join_a_Hackathon_Team'))
            ->markdown('emails.hackathon_inviteTeam', array_merge(['data' => $this->teamLeader, 'teamName' => $this->team_q, 'teamMember' => $this->teamMember], $orgData));
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

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('InviteToHackathon notification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'team' => $this->team_q,
        ]);
    }
}
