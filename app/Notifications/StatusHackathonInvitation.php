<?php

namespace App\Notifications;

use App\Services\GlobalNotificationSettingsService;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use App\Broadcasting\WhatsAppChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Log;
use Throwable;

class StatusHackathonInvitation extends Notification implements ShouldQueue
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
     * User instance.
     *
     * @var mixed
     */
    public $user;

    /**
     * User team.
     *
     * @var mixed
     */
    public $userteam;

    /**
     * Teams.
     *
     * @var mixed
     */
    public $teams;

    /**
     * Status.
     *
     * @var string
     */
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

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('StatusHackathonInvitation notification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'status' => $this->status,
        ]);
    }
}
