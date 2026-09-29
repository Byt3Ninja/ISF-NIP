<?php

namespace App\Notifications;

use App\Settings;
use App\Broadcasting\WhatsAppChannel;
use App\Services\WhatsAppService;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

class MentorshipSessionRequestNotification extends Notification implements ShouldQueue
{
    use Queueable, ConfiguresSmtp;

    public $startupName;
    public $requestedMentor;
    public $dateTime;
    public $sessionNotes;
    public $founder;
    public $startup;
    public $sessionType;
    public $meetingLink;
    public $passcode;
    public $locationName;
    public $locationUrl;
    public $address;
    public $attachments;

    public WhatsAppService $whatsAppService;


    /**
     * Create a new notification instance.
     *
     * @param  \App\User|null  $founder  the requesting founder, for a profile link
     * @param  \App\Company|null  $startup  the founder's startup, for a profile link
     */
    public function __construct(
        $startupName,
        $requestedMentor,
        $dateTime,
        $sessionNotes = null,
        $founder = null,
        $startup = null,
        $sessionType = null,
        $meetingLink = null,
        $passcode = null,
        $locationName = null,
        $locationUrl = null,
        $address = null,
        $attachments = []
    ) {
        $this->startupName = $startupName;
        $this->requestedMentor = $requestedMentor;
        $this->dateTime = $dateTime;
        $this->sessionNotes = $sessionNotes;
        $this->founder = $founder;
        $this->startup = $startup;
        $this->sessionType = $sessionType;
        $this->meetingLink = $meetingLink;
        $this->passcode = $passcode;
        $this->locationName = $locationName;
        $this->locationUrl = $locationUrl;
        $this->address = $address;
        $this->attachments = $attachments ?? [];
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $this->locale($notifiable->language ?: 'en');

        $channels = [];
        // Always store in database
        $channels[] = 'database';

        // WhatsApp if enabled for user and channel available
        if (!empty($notifiable->accept_whatsapp)) {
            $channels[] = WhatsAppChannel::class;
        }

        // Pusher (broadcast) if enabled
        if (!empty($notifiable->accept_pusher)) {
            $channels[] = 'broadcast';
        }

        // Email if user accepts OR default to email if nothing else (besides database) selected
        if (!empty($notifiable->accept_email) || count($channels) === 1) { // only 'database' present
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $setting = Settings::first();
        $platformName = $setting ? ( $setting->Platform_Name ?? config('app.name') ) : config('app.name');
        $org = $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;

        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->buildEmailOrganizationData($org);

        $this->configureSmtp($smtpSettings);

        // The subject and sign-off name the founder / the organization
        // managing the program -- not the platform default -- per the
        // "New Mentorship Session Request – [Founder Name]" / "[Organization
        // Name] Team" template.
        $founderName = $this->founder->name ?? '';
        $signOffName = $orgData['org_name'] ?? $platformName;

        $preferredTime = isset($this->dateTime['start_time'], $this->dateTime['end_time'])
            ? $this->dateTime['start_time'] . ' – ' . $this->dateTime['end_time']
            : ($this->dateTime['start_time'] ?? '');

        return (new MailMessage)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject( __('email.mentorship_request_email.subject') . ' ' . $founderName )
            ->view('emails.mentorship_session_request_email', array_merge([
                'party'             => $notifiable,
                'startupName'       => $this->startupName,
                'requestedMentor'   => $this->requestedMentor,
                'sessionNotes'      => $this->sessionNotes,
                'preferredDate'     => $this->dateTime['date'] ?? '',
                'preferredTime'     => $preferredTime,
                'platformName'      => $signOffName,
                'founder'           => $this->founder,
                'startup'           => $this->startup,
                'sessionType'       => $this->sessionType,
                'meetingLink'       => $this->meetingLink,
                'passcode'          => $this->passcode,
                'locationName'      => $this->locationName,
                'locationUrl'       => $this->locationUrl,
                'address'           => $this->address,
                'attachments'       => $this->attachments,
                'reviewUrl'         => route('mentorships.sessions_list'),
            ], $orgData));
    }

    /**
     * Summary of toWhatsapp
     * @param object $notifiable
     * @return array
     */
    public function toWhatsapp(object $notifiable): string
    {
        return " " . __('email.welcome_email.dear') . ' ' . ($notifiable->first_name_en ?? '') .
               ' ' . ($notifiable->last_name_en ?? '') . "\n" .
               __('email.welcome_email.m1') . "\n" .
               __('email.welcome_email.m2') . ' ' . ($notifiable->email ?? '');
    }


    /**
     * Summary of toBroadcast
     * @param object $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast(object $notifiable) 
    {
        return new BroadcastMessage([]);
    }

    /**
     * Get the array representation of the notification.
     * This is used for the 'database' channel to store the notification data in the database.
     * @param object $notifiable
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => __('notifications.mentorship_request.database', [
                'startup_name' => $this->startupName,
                'mentor_name' => $this->requestedMentor,
                'session_date' => $this->dateTime['date'] ?? '',
                'start_time' => $this->dateTime['start_time'] ?? '',
                'end_time' => $this->dateTime['end_time'] ?? ''
            ])
        ];
    }
    
}
