<?php

namespace App\Notifications;

use App\Enums\MentorshipApproval;
use App\Settings;
use Carbon\Carbon;
use App\Traits\ConfiguresSmtp;
use App\Traits\ResolvesMentorshipOrganization;
use App\Support\ReleasesNotificationClaim;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Broadcasting\WhatsAppChannel;
use App\Services\GlobalNotificationSettingsService;
use App\Services\FileManagerService;
use Throwable;
use App\Services\TimezoneService;

class MentorNotifyMentorshipNotification extends Notification implements ShouldQueue
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
        $org = $this->sessionOrganization($notifiable, $this->mentorship_session);
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
        $org = $this->sessionOrganization($notifiable, $this->mentorship_session);
        $smtpSettings = $org?->organizationSettings;

        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->emailBrandingData();

        $this->configureSmtp($smtpSettings);

        $mentorship_session = $this->mentorship_session;
        $cycle = $mentorship_session->cycle;
        // GV-9566: the session instant, shown in the recipient's own timezone.
        $start = TimezoneService::forViewer(
            TimezoneService::wallClock($mentorship_session->zoom_date, TimezoneService::platform($mentorship_session->organization_id)),
            $notifiable
        );
        $sessionDate = $start ? $start->format('Y-m-d') : '';
        $sessionTime = $start
            ? $start->format('h:i A') . ' - ' . $start->copy()->addMinutes((int) $mentorship_session->period)->format('h:i A')
            : '';
        if( app()->getLocale() == 'ar' )
        {
            $sessionTime = str_replace('AM', 'صباحاً', $sessionTime);
            $sessionTime = str_replace('PM', 'مساءاً', $sessionTime);
        }
        $sessionType = trim(strtolower($mentorship_session->session_type ?: 'online'));
        $locationName = $sessionType == 'offline' ? ($cycle->offline_location_address ?: null) : null;
        $mapLink = $sessionType == 'offline' ? ($cycle->offline_location_map ?: optional($cycle->organization)->google_map_url ?: null) : null;
        $address = $sessionType == 'offline' ? (optional($cycle->organization)->address ?: null) : null;
        $attachments = $mentorship_session->attachments;
        if( empty( $attachments ) )
        {
            $attachments = $mentorship_session->fileurl ? [
                [
                    'url' => app(FileManagerService::class)->getUrl($mentorship_session->fileurl, 'UsersDiskInviteMentor'),
                    'name' => basename($mentorship_session->fileurl),
                    'path' => $mentorship_session->fileurl
                ]
            ] : [];
        }

        $founder = $mentorship_session?->user;
        $startup = $founder?->company;
        // The founder's name in the email's language, falling back to the
        // English name so the subject never ends up with a blank name.
        $founderName = trim(($founder?->{'first_name_' . app()->getLocale()} ?? '') . ' ' . ($founder?->{'last_name_' . app()->getLocale()} ?? ''));
        if ($founderName === '') {
            $founderName = trim(($founder?->first_name_en ?? '') . ' ' . ($founder?->last_name_en ?? ''));
        }
        $subject = __('email.mentor_notify_email.subject', ['name' => $founderName]);

        return (new MailMessage)
            ->from($from['address'], $from['name'])
            ->subject($subject)
            ->view('emails.mentorship_notify_mentor_email', array_merge([
                'party'             => $notifiable,
                'founder'           => $founder,
                'startup'           => $startup,
                'startupName'       => $startup?->name_en ?? $startup?->name_ar ?? '',
                'sessionType'       => $sessionType,
                'sessionNotes'      => $mentorship_session?->comment ?? '',
                'sessionDate'       => $sessionDate,
                'sessionTime'       => $sessionTime,
                'sessionLink'       => $mentorship_session?->join_url ?? '',
                'passcode'          => $mentorship_session?->zoom_passcode ?? '',
                'platformName'      => $platformName,
                'locationName'      => $locationName,
                'mapLink'           => $mapLink,
                'address'           => $address,
                'attachments'       => $attachments,
                // A declined session has no meeting: hide its link / location.
                'isRejected'        => $mentorship_session?->approved === MentorshipApproval::REJECTED->value,
                'viewSessionUrl'    => route('mentorships.sessions_list_detailed'),
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
        $this->releaseClaim();
        Log::error('MentorshipSession notification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'message' => $this->Message,
        ]);
    }
}
