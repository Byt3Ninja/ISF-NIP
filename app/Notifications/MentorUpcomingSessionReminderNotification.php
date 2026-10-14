<?php

namespace App\Notifications;

use App\Organization;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class MentorUpcomingSessionReminderNotification extends Notification implements ShouldQueue
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
     * The notification data.
     *
     * @var array
     */
    public $data;

    /**
     * Organization running the program; brands the email (logo, name, footer).
     *
     * @var Organization|null
     */
    public $organization;

    /**
     * Create a new notification instance.
     *
     * @param array $data
     * @param Organization|null $organization
     */
    public function __construct(array $data, ?Organization $organization = null)
    {
        $this->data = $data;
        $this->organization = $organization;
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

        // Configure SMTP settings if they exist
        $this->configureSmtp($smtpSettings);

        $orgAcceptEmail = $smtpSettings?->accept_email ?? true;

        $channels = ['database'];
        if ($orgAcceptEmail && $notifiable->accept_email) {
            $channels[] = 'mail';
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
        $mail = (new MailMessage)
            ->subject($this->data['subject'])
            ->view(
                'emails.mentor_upcoming_session_reminder',
                array_merge($this->data, $this->buildEmailOrganizationData($this->organization))
            );

        // Handle attachment attachment
        if (!empty($this->data['attachments'])) {
            $disk = Storage::disk('UsersDiskInviteMentor');
            foreach ($this->data['attachments'] as $attachment) {
                $filePath = $attachment['path'] ?? null;
                if ($filePath && $disk->exists($filePath)) {
                    $mail->attach($disk->path($filePath), [
                        'as' => $attachment['name'] ?? basename($filePath),
                    ]);
                }
            }
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->data['subject'] ?? 'Upcoming Mentorship Session Reminder',
            'details' => $this->data,
        ];
    }
}
