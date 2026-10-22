<?php

namespace App\Notifications;

use App\Broadcasting\WhatsAppChannel;
use App\Services\GlobalNotificationSettingsService;
use App\Traits\ConfiguresSmtp;
use App\Traits\ResolvesMentorshipOrganization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MentorshipSessionMentor extends Notification implements ShouldQueue
{
  use Queueable, ConfiguresSmtp, ResolvesMentorshipOrganization;

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
    public $data;

    /**
     * Action text.
     *
     * @var string
     */
    public $ActionText = 'Open VI portal';

    /**
     * Action URL.
     *
     * @var string|null
     */
    public $ActionUrl = null;

    /**
     * Create a new notification instance.
     */
   public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $org = $this->sessionOrganization($notifiable, null, $this->data['organizationId'] ?? null);
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
        $orgData = $this->emailBrandingData();
        $this->data['orgName'] = $orgData['org_name'] ?? config('app.name');

            $mail = (new MailMessage)
                ->subject($this->data['subject'])
                ->view('emails.mentorship_request_status', array_merge([
                    'data' => $this->data
                ], $orgData));

 $attachments = $this->data['attachments'] ?? [];
 \Log::info($attachments);


if (!empty($attachments)) {

    $disk = Storage::disk('UsersDiskInviteMentor');

    foreach ($attachments as $file) {
        $filePath = is_array($file) ? $file['path'] : $file;
        $fileName = basename($filePath);

        if ($disk->exists($filePath)) {
            $mail->attach($disk->path($filePath));
        } else {
            logger()->error('InviteMentor attachment not found on UsersDiskInviteMentor', [
                'file' => $filePath,
                'disk_root' => config('filesystems.disks.UsersDiskInviteMentor.root'),
            ]);
        }
    }
}

    return $mail;

    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $userType   = $this->data['user']; // mentor | registered
        $action     = $this->data['action'] ?? null;
        $status     = $this->data['status'] ?? null;
        $date       = $this->data['sessionDate'] ?? 'N/A';
        
        $name = $userType == 'mentor'
            ? $this->data['startupName']
            : $this->data['mentorName'];

            $message = "You have a new mentorship session request from {$name} | Date: {$date}";
        if ($status === 'Yes') {
            $message = "Your mentorship session is approved with {$name} | Date: {$date}";
        } elseif ($status === 'No') {
            $message = "Your mentorship session is rejected with {$name} | Date: {$date}";
        }
        if ($action === 'modifySession') {
            $message = "Mentorship session Rescheduled with {$name} | Date: {$date}";
        }
        return [
            'message' => $message,
            'details' => $this->data,
        ];
        //return [
         //   'message' => 'New mentorship session created with ' . $this->data['startupName'],
          //  'details' => $this->data,
        //];
    }

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        // Expecting $this->Message indices: [0]=subject/title, [1]=greeting, [2]=line, [3]=url, [4]=database message
        return __('notifications.mentorship_session.whatsapp', [
            'greeting' => $this->data['title'] ?? '',
            'line' => $this->data['sessionDate'] . ' / company name: ' . $this->data['startupName'] ?? '',
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        //return new BroadcastMessage([
         //   'subject' => $this->data['title'] ?? __('notifications.mentorship_session.broadcast', ['title' => '']),
         //   'message' => __('notifications.mentorship_session.broadcast', ['title' => $this->data['title'] . ' - ' . $this->data['startupName'] ?? '']),
         //   'time' => now()->toDateTimeString(),
      //  ]);
        return new BroadcastMessage([
            'subject' => $this->data['title'] ?? 'New Mentorship Session',
            'message' =>
                'Startup: ' . ($this->data['startupName'] ?? 'N/A') .
                ' | Date: ' . ($this->data['sessionDate'] ?? 'N/A'),
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
        Log::error('MentorshipSessionMentor notification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'message' => $this->data,
        ]);
    }
}
