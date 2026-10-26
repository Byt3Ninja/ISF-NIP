<?php

namespace App\Notifications;

use App\Models\User;
use App\Settings;
use App\Services\GlobalNotificationSettingsService;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\WhatsApp\WhatsAppChannel;
use NotificationChannels\WhatsApp\WhatsAppMessage;
use Throwable;

class ChangeCycle extends Notification implements ShouldQueue
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
     * Create a new notification instance.
     *
     * @param array $data
     */
    public function __construct($data)
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
        $data = $this->data;
        $lang = app()->getLocale();
        if( in_array( strtolower( $notifiable->language ), ['en', 'ar'] ) )
        {
            $lang = $notifiable->language;
        }

        if( $data['status'] == 'in' )
        {
            // Follow the platform's default language (General Settings), not
            // the recipient's own -- same rule as every other phase email.
            $setting = Settings::first();
            $lang = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : $lang;

            // Organization::getNameAttribute() reads app()->getLocale(), so it
            // must be switched before resolving org branding, not just when
            // building this class's own strings via __($key, [], $lang).
            app()->setLocale($lang);

            // Branded with the organization running the program, not the
            // recipient's own organization relation.
            $programOrg = $data['organization'] ?? $notifiable->organization;
            $smtpSettings = $programOrg?->organizationSettings;
            $from = $this->getFromAddress($smtpSettings);
            $orgData = $this->emailBrandingData();
            $orgName = $orgData['org_name'] ?? ($setting?->Platform_Name ?: config('app.name'));

            $data['preferred_language'] = $lang;
            $data['organization_name'] = $orgName;

            return (new MailMessage)
                ->from($from['address'], $from['name'])
                ->subject(__('email.phase_advanced_email.subject', [], $lang))
                ->view('emails.change-cycle', array_merge([
                    'data' => $data,
                ], $orgData));
        }
        else
        {
            // Branded with the organization running the program, as above.
            $programOrg = $data['organization'] ?? $notifiable->organization;
            $smtpSettings = $programOrg?->organizationSettings;

            // Get from address and name
            $from = $this->getFromAddress($smtpSettings);
            $orgData = $this->emailBrandingData();

            $data['program_title'] = $data['program']->{'name_' . $lang} ?? '';
            $subject = $lang === 'en' ? "Update on Your {$data['program_title']} Application" : "تحديث بخصوص طلب انضمامكم إلى {$data['program_title']}";

            return (new MailMessage)
                ->from($from['address'], $from['name'])
                ->subject( $subject )
                ->view('emails.disapprove-startup', array_merge([
                    'data' => $data,
                ], $orgData));
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => __('notifications.change_cycle.database', [
                'company' => $this->data['company_name_en'] ?? 'N/A',
                'from' => $this->data['last_step'] ?? 'N/A',
                'to' => $this->data['new_step'] ?? 'N/A',
                'org' => $this->data['organization_name_en'] ?? 'N/A',
            ]),
        ];
    }

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        return __('notifications.change_cycle.whatsapp', [
            'company' => $this->data['company_name_en'] ?? 'N/A',
            'from' => $this->data['last_step'] ?? 'N/A',
            'to' => $this->data['new_step'] ?? 'N/A',
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => __('notifications.change_cycle.subject'),
            'message' => __('notifications.change_cycle.broadcast', [
                'company' => $this->data['company_name_en'] ?? 'N/A',
                'to' => $this->data['new_step'] ?? 'N/A',
            ]),
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
        Log::error('ChangeCycle notification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'data' => $this->data,
        ]);
    }
}
