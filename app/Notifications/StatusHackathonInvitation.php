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
        $orgData = $this->emailBrandingData();

            return (new MailMessage)
            ->subject(__('admin.Status_Invitation'))
            ->markdown('emails.acceptInviteHackathon', array_merge([
                'user' => $this->user,
                'teams' => $this->teams,
                'status' => $this->status,
                // The recipient is the team leader; $this->user is the member who
                // acted. Greeting the recipient by the member's name was the
                // "dear test member" line in GV-7355.
                'leaderName' => $this->displayName($notifiable),
                'leaderGender' => $notifiable->gender ?? null,
                'memberName' => $this->displayName($this->user),
            ], $orgData));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        // $userteam is null when the invitation targeted an email that has no
        // registered user yet (placeholder Team_member with null user_id), so
        // guard every id read to avoid "Attempt to read property id on null".
        return [
            'subject' => __('admin.Status_Invitation'),
            'message' => $this->statusMessage(),
            'user'   => $this->user?->id,
            'userteam'   => $this->userteam?->id,
            'member' => $this->teams?->id,
            'status' => $this->status,
        ];
    }

    public function toWhatsapp(object $notifiable): string
    {
        // This notification has no $teamLeader property -- the recipient IS the
        // leader. Reading it threw as soon as the WhatsApp channel was enabled.
        return $this->statusMessage();
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => __('admin.Status_Invitation'),
            'message' => $this->statusMessage(),
            'time' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Human-readable name for a user in the current locale, falling back to the
     * other locale and then the email so the greeting is never blank.
     */
    private function displayName($user): string
    {
        if (!$user) {
            return '';
        }

        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $other = $locale === 'ar' ? 'en' : 'ar';

        $first = $user->{'first_name_'.$locale} ?: $user->{'first_name_'.$other};
        $last = $user->{'last_name_'.$locale} ?: $user->{'last_name_'.$other};

        return trim($first.' '.$last) ?: (string) ($user->email ?? '');
    }

    /**
     * The sentence shown in the bell / broadcast / WhatsApp. Previously every
     * status stored the bare label admin.Status_Invitation ("حالة الدعوة"),
     * which is what the leader saw instead of real content (GV-7355).
     */
    private function statusMessage(): string
    {
        $key = match ($this->status) {
            'Accept' => 'accepted',
            'Reject' => 'rejected',
            'cancelled' => 'cancelled',
            default => null,
        };

        if ($key === null) {
            return __('admin.Status_Invitation');
        }

        return __('notifications.hackathon_status.'.$key, [
            'member' => $this->displayName($this->user),
            'team' => $this->teams->name ?? '',
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
