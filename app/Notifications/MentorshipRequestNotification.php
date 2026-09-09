<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use App\Broadcasting\WhatsAppChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Services\GlobalNotificationSettingsService;
use App\Traits\ConfiguresSmtp;
use Throwable;

class MentorshipRequestNotification extends Notification implements ShouldQueue
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
     * Notification data.
     *
     * @var array
     */
    public $data;

    /**
     * Reminder flag.
     *
     * @var string|null
     */
    public $reminder;
    /**
     * Create a new notification instance.
     */
    public function __construct($data, $reminder = null)
    {
        $this->data = $data;
        $this->reminder = $reminder;
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
        $orgData = $this->buildEmailOrganizationData($org);

        if($this->reminder == 'Reminder'){
            return (new MailMessage)
            ->subject( __('emails.mentorship_request_reminder.subject', ['startup' => $this->data['company_name']]))
            ->markdown('emails.mentorship_request_reminder', array_merge(['data' => $this->data], $orgData));
           Log::info('Mail data:', ['company_name' => $this->data['company_name']]);
        }else{
            return (new MailMessage)
            ->subject(__('admin.mentorship_request_subject', ['startup' => $this->data['company_name']]))
            ->markdown('emails.mentorship_request', array_merge(['data' => $this->data], $orgData));
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => __('admin.mentorship_request_subject', ['startup' => $this->data['company_name']]),
            'user_id' => $notifiable->id,
        ];
    }

     public function toDatabase(object $notifiable): array
    {
       $isMentor = method_exists($notifiable, 'hasRole') && ($notifiable->hasRole('Mentor') || $notifiable->hasRole('mentor'));
       if ($isMentor) {
           $message = ($this->data['company_name'] ?? 'N/A') . 
                      ' has sent you a mentorship request. They are eager to learn from your expertise and guidance.';
       } else {
           $mentorName = $this->data['Mentorname'] ?? __('messages.Mentors');
           $locale = method_exists($notifiable, 'preferredLocale') ? $notifiable->preferredLocale() : 'en';
           $message = __("messages.mentorship_session_action", ['action' => __('messages.Approved', [], $locale), 'name' => $mentorName], $locale);
       }
       return [
        'message' => $message,
       ]; 
    }

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        return __('notifications.mentorship_request.whatsapp', [
            'startup' => $this->data['company_name'] ?? '',
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => __('admin.mentorship_request_subject', ['startup' => $this->data['company_name'] ?? '']),
            'message' => __('notifications.mentorship_request.broadcast', ['startup' => $this->data['company_name'] ?? '']),
            'time' => now()->toDateTimeString(),
        ]);
    }

    public function afterSend($notifiable)
    {
        Log::info("Notification sent to user {$notifiable->id}", [
            'type' => static::class,
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
        Log::error('MentorshipRequestNotification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'data' => $this->data,
            'reminder' => $this->reminder,
        ]);
    }
}
