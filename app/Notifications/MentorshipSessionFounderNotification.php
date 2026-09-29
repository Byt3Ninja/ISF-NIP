<?php

namespace App\Notifications;

use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Broadcasting\WhatsAppChannel;
use App\Services\GlobalNotificationSettingsService;
use Log;

class MentorshipSessionFounderNotification extends Notification implements ShouldQueue
{
    use Queueable, ConfiguresSmtp;

    public $tries = 3;
    public $backoff = 60;

    public $sessionData;

    /**
     * Create a new notification instance.
     *
     * @param array $sessionData
     */
    public function __construct(array $sessionData)
    {
        $this->sessionData = $sessionData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param object $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $org = $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;

        // Configure SMTP if settings exist
        $this->configureSmtp($smtpSettings);

        $orgAcceptEmail = $smtpSettings?->accept_email ?? true;

        $channels = [];

        // Email if enabled globally AND organization/user accepts it
        if (GlobalNotificationSettingsService::isEmailEnabled() 
            && $orgAcceptEmail 
            && $notifiable->accept_email) {
            $channels[] = 'mail';
        }

        // Always store in database for audit/history
        if (!empty($channels)) {
            $channels[] = 'database';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param object $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $org = $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;

        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->buildEmailOrganizationData($org);

        $locale = $notifiable->preferredLocale() === 'ar' ? 'ar' : 'en';

        $mentorName = $this->sessionData['mentor_name'];
        
        if ($locale === 'ar') {
            $subject = "جلسة إرشاد مع {$mentorName}.";
        } else {
            $subject = "Mentorship Session with {$mentorName}.";
        }

        return (new MailMessage)
            ->from($from['address'], $from['name'])
            ->subject($subject)
            ->view('emails.mentorship_session_founder', array_merge([
                'party' => $notifiable,
                'locale' => $locale,
                'mentorName' => $mentorName,
                'sessionDate' => $this->sessionData['session_date'],
                'startTime' => $this->sessionData['start_time'],
                'endTime' => $this->sessionData['end_time'],
                'sessionType' => $this->sessionData['session_type'],
                'location' => $this->sessionData['location'] ?? null,
                'meetingLink' => $this->sessionData['meeting_link'] ?? null,
            ], $orgData));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $locale = $notifiable->preferredLocale() === 'ar' ? 'ar' : 'en';
        $mentorName = $this->sessionData['mentor_name'];
        $date = $this->sessionData['session_date'];

        if ($locale === 'ar') {
            $message = "لديك جلسة إرشاد جديدة مع {$mentorName} | التاريخ: {$date}";
        } else {
            $message = "You have a new mentorship session with {$mentorName} | Date: {$date}";
        }

        return [
            'message' => $message,
            'details' => $this->sessionData,
        ];
    }
}
