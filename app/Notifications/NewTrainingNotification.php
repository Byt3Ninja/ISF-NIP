<?php

namespace App\Notifications;

use App\Settings;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Log;
use App\Broadcasting\WhatsAppChannel;
use App\Services\GlobalNotificationSettingsService;
use Throwable;

class NewTrainingNotification extends Notification implements ShouldQueue
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
        $org = $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;

        // Get from address and name
        $from = $this->getFromAddress($smtpSettings);

        // Branded with the organization running the training (its cycle's
        // organization), not the recipient's own organization relation.
        $orgData = $this->emailBrandingData();

        $setting = Settings::first();
        $locale = in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';
        $platformName = $setting?->Platform_Name ?: config('app.name');
        $orgName = $orgData['org_name'] ?? $platformName;

        $firstName = $locale === 'ar' ? trim((string) $notifiable->first_name_ar) : '';
        if ($firstName === '') {
            $firstName = trim((string) $notifiable->first_name_en);
        }

        $type = strtolower(trim((string) ($this->data['type'] ?? '')));
        $isOnline = in_array($type, ['online', 'sessions'], true);

        // Keys are named distinctly from 'platformName': a global view composer
        // (AppServiceProvider) sets that key on every view and would override it.
        return (new MailMessage)
            ->subject(__('email.new_training_email.subject', [], $locale))
            ->view('emails.new_training', array_merge([
                'trainingLocale' => $locale,
                'trainingRecipientName' => $firstName,
                'trainingOrgName' => $orgName,
                'trainingTitle' => $this->data['title'] ?? '',
                'trainingDate' => $this->data['date'] ?? '',
                'trainingStartTime' => $this->data['start_time'] ?? '',
                'trainingEndTime' => $this->data['end_time'] ?? '',
                'trainingIsOnline' => $isOnline,
                'trainingLink' => $this->data['join_url'] ?? '',
                'trainingMapLink' => $this->data['join_url'] ?? '',
                'trainingAddress' => $this->data['Address'] ?? '',
                'trainingTrainerName' => trim((string) ($this->data['trainer_name'] ?? '')),
                'trainingTrainerEmail' => trim((string) ($this->data['trainer_email'] ?? '')),
            ], $orgData));
    }

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        return __('notifications.new_training.whatsapp', [
            'title' => $this->data['title'] ?? '',
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => __('admin.new_training_message'),
            'message' => __('notifications.new_training.broadcast', ['title' => $this->data['title'] ?? '']),
            'time' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => __('admin.new_training_message', ['training' => $this->data['title']]),
            'user_id' => $notifiable->id,
        ];
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('NewTrainingNotification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'data' => $this->data,
        ]);
    }
}
