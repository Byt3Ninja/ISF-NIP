<?php

namespace App\Notifications;

use App\Traits\ConfiguresSmtp;
use App\Services\GlobalNotificationSettingsService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Throwable;

class TemplateEmail extends Notification implements ShouldQueue
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
     * Message array.
     *
     * @var array
     */
    public $Message;

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
     * Preferred locale for the notification.
     *
     * @var string|null
     */
    public $preferredLocale = null;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(array $Message = [], ?string $locale = null)
    {
        $this->Message = $Message;
        $this->preferredLocale = $locale;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        Log::info('via() called for user: ' . $notifiable->id);
        $org = $notifiable->organization ?? null;
        $smtpSettings = $org?->organizationSettings ?? null;
        $this->configureSmtp($smtpSettings);

        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $org = $notifiable->organization ?? null;
        $smtpSettings = $org?->organizationSettings ?? null;
        $from = $this->getFromAddress($smtpSettings);

        $locale = $this->preferredLocale 
            ?? (method_exists($notifiable, 'preferredLocale') ? $notifiable->preferredLocale() : ($notifiable->language ?? 'en'));

        $url = is_null($this->ActionUrl) ? url($this->Message[3]) : $this->ActionUrl;

        $mail = (new MailMessage)
            ->subject($this->Message[0])
            ->greeting($this->Message[1])
            ->level('info')
            ->line($this->Message[2])
            ->action($this->ActionText, $url)
            ->line(__('admin.email_thank_you', [], $locale))
            ->salutation(__('admin.email_salutation', [], $locale));

        if (!empty($from['address'])) {
            $mail->from($from['address'], $from['name'] ?? config('app.name'));
        }

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase(object $notifiable): array
    {

        Log::info('TemplateEmail Notification toDatabase', [
            'notifiable' => $notifiable->id,
            'message' => $this->Message,
        ]);
        return [
            'message' => $this->Message[4],
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
        Log::error('TemplateEmail notification failed after all retries', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'message' => $this->Message,
        ]);
    }
}
