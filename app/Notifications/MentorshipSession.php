<?php

namespace App\Notifications;

use App\Organization;
use App\Settings;
use App\Traits\ConfiguresSmtp;
use App\Traits\ResolvesMentorshipOrganization;
use Carbon\Carbon;
use App\Support\ReleasesNotificationClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Broadcasting\WhatsAppChannel;
use App\Services\GlobalNotificationSettingsService;
use Throwable;
use App\Services\TimezoneService;

class MentorshipSession extends Notification implements ShouldQueue
{
  use Queueable, ConfiguresSmtp, ResolvesMentorshipOrganization, ReleasesNotificationClaim;

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
   public function __construct(array $data = [])
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
        if (($this->data['action'] ?? '') === 'modifySession' && ($this->data['user'] ?? '') === 'user') {
            return $this->toFounderUpdatedMail($notifiable);
        }

        // $org = $this->sessionOrganization($notifiable, null, $this->data['organizationId'] ?? null);
        // $smtpSettings = $org?->organizationSettings;
        
        // // Get from address and name
        // $from = $this->getFromAddress($smtpSettings);
        // $url = is_null($this->ActionUrl) ? $this->ActionUrl : '';
        // return (new MailMessage)
        //     ->subject($this->data['subject'])
        //     ->view('emails.mentorship_request_status', ['data' => $this->data]);

           

            $mail = (new MailMessage)
                ->subject($this->data['subject'])
                ->view('emails.mentorship_request_status', array_merge([
                    'data' => $this->data
                ], $this->emailBrandingData()));


foreach ($this->data['attachments'] ?? [] as $attachment) {
        $filePath = $attachment['path'] ?? null;
        if (empty($filePath)) continue;
        $fullPath = storage_path('app/users/' . $filePath);
        if (file_exists($fullPath)) {
            $mail->attach($fullPath, [
                'as' => $attachment['name'] ?? basename($filePath),
            ]);
        } else {
            \Log::error('Attachment not found at: ' . $fullPath);
        }
    }

    return $mail;

    }

    /**
     * Founder "mentorship session updated" email: platform default language,
     * organization branding, and session-type specific details.
     */
    protected function toFounderUpdatedMail($notifiable): MailMessage
    {
        $d = $this->data;
        $setting = Settings::first();
        $locale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';
        $isRtl = $locale === 'ar';
        $platformName = $setting?->Platform_Name ?: config('app.name');

        $organization = ! empty($d['organizationId']) ? Organization::find($d['organizationId']) : null;
        $orgData = $this->emailBrandingData();
        $orgName = $organization
            ? (trim((string) $organization->getAttribute('name_' . $locale)) ?: $organization->getAttribute('name_en'))
            : $platformName;

        $firstName = $isRtl ? trim((string) $notifiable->first_name_ar) : '';
        if ($firstName === '') {
            $firstName = trim((string) $notifiable->first_name_en);
        }

        $startupName = $isRtl
            ? (trim((string) ($d['startupNameAr'] ?? '')) ?: trim((string) ($d['startupNameEn'] ?? '')))
            : trim((string) ($d['startupNameEn'] ?? ''));

        // GV-9566: startAt is platform wall-clock; show it in the founder's own timezone.
        $start = TimezoneService::forViewer(
            TimezoneService::wallClock($d['startAt'], TimezoneService::platform(! empty($d['organizationId']) ? (int) $d['organizationId'] : null)),
            $notifiable
        ) ?? Carbon::now();
        $end = $start->copy()->addMinutes((int) ($d['periodMinutes'] ?: 60));
        $sessionTime = $start->format('h:i A') . ' – ' . $end->format('h:i A');
        if ($isRtl) {
            $sessionTime = str_replace(['AM', 'PM'], ['صباحاً', 'مساءاً'], $sessionTime);
        }

        $isOnline = strtolower(trim((string) ($d['sessionType'] ?? 'online'))) !== 'offline';
        $dash = fn ($v) => trim((string) $v) !== '' ? trim((string) $v) : '-';

        $subject = $startupName !== ''
            ? __('email.founder_session_updated_email.subject', ['startup_name' => $startupName], $locale)
            : __('email.founder_session_updated_email.subject_plain', [], $locale);

        // Keys are named distinctly from 'platformName': a global view composer
        // (AppServiceProvider) sets that key on every view and would override it.
        return (new MailMessage)
            ->subject($subject)
            ->view('emails.mentorship_session_updated_founder', array_merge([
                'updLocale' => $locale,
                'updFounderName' => $firstName,
                'updGender' => strtolower(trim((string) $notifiable->gender)),
                'updOrgName' => $orgName,
                'updMentorName' => $dash($d['mentorName'] ?? ''),
                'updDate' => $dash($start->format('Y-m-d')),
                'updTime' => $sessionTime,
                'updDuration' => empty($d['periodMinutes']) ? '-' : __('email.founder_session_updated_email.duration_value', ['count' => $d['periodMinutes']], $locale),
                'updIsOnline' => $isOnline,
                'updMeetingLink' => $dash($d['joinUrl'] ?? ''),
                'updHasMeetingLink' => trim((string) ($d['joinUrl'] ?? '')) !== '',
                'updPasscode' => trim((string) ($d['passcode'] ?? '')),
                'updLocation' => $dash($d['offlineLocation'] ?? ''),
                'updMapLink' => trim((string) ($d['offlineMapLink'] ?? '')),
                'updAddress' => $dash($d['offlineAddress'] ?? ''),
                'updNotes' => trim((string) ($d['founderNotes'] ?? '')),
                'updAttachments' => $d['attachmentLinks'] ?? [],
                'updViewUrl' => route('mentorships.list_mine'),
            ], $orgData));
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

            
                    $message = "New mentorship session created with {$name} | Date: {$date}";
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
        $this->releaseClaim();
        Log::error('MentorshipSessionMentor notification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'message' => $this->data,
        ]);
    }
}
