<?php

namespace App\Notifications;

use App\Settings;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Broadcasting\WhatsAppChannel;
use App\Services\GlobalNotificationSettingsService;
use Throwable;

class StartupNotifyMentorshipRejectionNotification extends Notification implements ShouldQueue
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
     * Message array.
     *
     * @var array
     */
    public $Message;

    /**
     * mentorship_session object.
     *
     * @var collection
     */
    public $mentorship_session;

    /**
     * Create a new notification instance.
     */
   public function __construct(array $Message = [], $mentorship_session)
    {
        $this->Message = $Message;
        $this->mentorship_session = $mentorship_session;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $org = $this->sendingOrganization($notifiable);
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
        $setting = Settings::first();
        $platformName = $setting ? ( $setting->Platform_Name ?? config('app.name') ) : config('app.name');
        $org = $this->sendingOrganization($notifiable);
        $smtpSettings = $org?->organizationSettings;

        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->buildEmailOrganizationData($org);

        $this->configureSmtp($smtpSettings);

        $signOffName = $orgData['org_name'] ?? $platformName;

        return (new MailMessage)
            ->from($from['address'], $from['name'])
            ->subject( __('email.startup_mentorship_session_notify_email.rejection.subject') )
            ->view('emails.mentorship_notify_startup_rejection_email', array_merge([
                'party'             => $notifiable,
                'platformName'      => $signOffName,
                'submitNewRequestUrl' => url('/dashboard/mentors?nav=menu'),
            ], $orgData));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' =>$this->Message[4],
        ];
    }

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        // Expecting $this->Message indices: [0]=subject/title, [1]=greeting, [2]=line, [3]=url, [4]=database message
        return __('notifications.mentorship_session.whatsapp', [
            'greeting' => $this->Message[1] ?? '',
            'line' => $this->Message[2] ?? '',
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => $this->Message[0] ?? __('notifications.mentorship_session.broadcast', ['title' => '']),
            'message' => __('notifications.mentorship_session.broadcast', ['title' => $this->Message[0] ?? '']),
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
        Log::error('MentorshipSession notification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'message' => $this->Message,
        ]);
    }

    /**
     * The organization running the session, not the founder's own
     * organization_id (often null for founders in several organizations).
     */
    protected function sendingOrganization($notifiable)
    {
        return $this->mentorship_session?->organization
            ?? $this->mentorship_session?->cycle?->organization
            ?? $notifiable->organization;
    }
}
