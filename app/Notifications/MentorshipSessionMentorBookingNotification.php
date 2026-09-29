<?php

namespace App\Notifications;

use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentorshipSessionMentorBookingNotification extends Notification implements ShouldQueue
{
    use Queueable, ConfiguresSmtp;

    public $tries = 3;
    public $backoff = 60;
    
    public $founder;
    public $sessions;

    /**
     * Create a new notification instance.
     *
     * @param \App\User $founder
     * @param array $sessions
     */
    public function __construct(\App\User $founder, array $sessions)
    {
        $this->founder = $founder;
        $this->sessions = $sessions;
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
        if ($orgAcceptEmail && $notifiable->accept_email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param object $notifiable
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $locale = $notifiable->preferredLocale();

        $founderName = $locale === 'ar'
            ? trim($this->founder->first_name_ar . ' ' . $this->founder->last_name_ar)
            : trim($this->founder->first_name_en . ' ' . $this->founder->last_name_en);

        if (empty($founderName)) {
            $founderName = $this->founder->name;
        }

        $mappedSessions = array_map(function($session) use ($locale, $founderName) {
            $session['founder_name'] = $founderName;

            if ($this->founder->company) {
                $session['startup_name'] = $locale === 'ar'
                    ? ($this->founder->company->name_ar ?? $this->founder->company->name_en)
                    : ($this->founder->company->name_en ?? $this->founder->company->name_ar);
            } else {
                $session['startup_name'] = '';
            }

            return $session;
        }, $this->sessions);

        if ($locale === 'ar') {
            $subject = "جلسة إرشاد مع {$founderName}.";
        } else {
            $subject = "Mentorship Session with {$founderName}.";
        }

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.mentorship_session_mentor_booking', [
                'founderName' => $founderName,
                'sessions' => $mappedSessions,
                'locale' => $locale,
            ]);
    }
}
