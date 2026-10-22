<?php

namespace App\Notifications;

use App\Settings;
use App\Step;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Log;
use App\Broadcasting\WhatsAppChannel;
use App\Services\GlobalNotificationSettingsService;
use Throwable;

/**
 * Notifies a judge that they have been assigned to a screening step
 * (initial screening, stype 2, or final screening, stype 5).
 *
 * Sent only for judges NEWLY added to a panel whose "Notify now" switch is on —
 * the caller does the added/retained diff, so this class just renders for
 * whoever it is handed (GV-7317, templates from GV-7237 / GV-7290 / GV-7291).
 */
class JudgeAssignedToScreeningNotification extends Notification implements ShouldQueue
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
     * The screening step the judge was assigned to.
     *
     * Stored as an id, not a model: this notification is queued, and a
     * serialized model would restore stale attributes (or fail outright if the
     * step is edited before the worker picks the job up).
     *
     * @var int
     */
    public $stepId;

    public function __construct(int $stepId)
    {
        $this->stepId = $stepId;
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

        if (GlobalNotificationSettingsService::isWhatsAppEnabled()
            && $orgAcceptWhatsapp
            && $notifiable->accept_whatsapp) {
            $channels[] = WhatsAppChannel::class;
        }

        if (GlobalNotificationSettingsService::isPusherEnabled()
            && $orgAcceptPusher
            && $notifiable->accept_pusher) {
            $channels[] = 'broadcast';
        }

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
    public function toMail(object $notifiable): MailMessage
    {
        $setting = Settings::first();
        $platformName = $setting ? ($setting->Platform_Name ?? config('app.name')) : config('app.name');
        $step = Step::with('cycle.organization')->find($this->stepId);

        // Sent through the organization running the program, not the judge's
        // own organization (judges often have none).
        $org = $step?->cycle?->organization ?? $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;

        $from = $this->getFromAddress($smtpSettings);

        $this->configureSmtp($smtpSettings);

        // GV-7237 asks for the platform's default language from general settings.
        //
        // Applied here rather than left to the ambient locale: User implements
        // HasLocalePreference, so Laravel otherwise restores each judge's own
        // language around this queued job. It must also run before ->view(),
        // because both this mail's blade and emails/layout/header read
        // app()->getLocale() lazily at render time.
        app()->setLocale($this->resolveLocale($setting, $notifiable));

        // After the locale switch, so the organization name is in that language.
        $orgData = $this->emailBrandingData();

        return (new MailMessage)
            ->from($from['address'], $from['name'])
            ->subject(__('email.judge_assigned_email.subject'))
            ->view('emails.judge_assigned_evaluation', array_merge([
                'judge'         => $notifiable,
                'programName'   => $this->programName($step, $platformName),
                'startDate'     => $this->formatDate($step?->from),
                'endDate'       => $this->formatDate($step?->to),
                'evaluationUrl' => url('/dashboard/judger/screening'),
                'platformName'  => $platformName,
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
            'message' => __('email.judge_assigned_email.database'),
        ];
    }

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        $step = Step::with('cycle')->find($this->stepId);

        return __('email.judge_assigned_email.whatsapp', [
            'name'         => trim($notifiable->name ?? ''),
            'program_name' => $this->programName($step, config('app.name')),
            'start_date'   => $this->formatDate($step?->from),
            'end_date'     => $this->formatDate($step?->to),
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'subject' => __('email.judge_assigned_email.subject'),
            'message' => __('email.judge_assigned_email.database'),
            'url'     => '/dashboard/judger/screening',
            'time'    => now()->toDateTimeString(),
        ]);
    }

    /**
     * Decide the language this email renders in.
     *
     * Order: the platform default from general settings (GV-7237), then the
     * recipient judge's own `users.language`, then the app locale. The judge's
     * preference sits in the middle because most rows have `language` NULL, so
     * a platform that has not configured a default still sends each judge
     * something they can read rather than defaulting everyone to English.
     *
     * The recipient — not the assigning admin — is the right fallback: this is
     * the judge's email, and the notification is queued, so there is no
     * authenticated session to read an admin's preference from at render time.
     */
    protected function resolveLocale($setting, object $notifiable): string
    {
        $candidates = [
            $setting?->Default_Language,
            $notifiable->language ?? null,
            config('app.locale', 'en'),
        ];

        foreach ($candidates as $locale) {
            if (in_array($locale, ['en', 'ar'], true)) {
                return $locale;
            }
        }

        return 'en';
    }

    /**
     * The program (cycle) name in the active locale.
     *
     * Cycles carry a single `title`, so there is no per-locale variant to pick;
     * the platform name stands in when a step has no cycle.
     */
    protected function programName(?Step $step, string $fallback): string
    {
        $title = $step?->cycle?->title;

        return filled($title) ? $title : $fallback;
    }

    /**
     * Render a judging-period date, or an em dash when the step has none set.
     */
    protected function formatDate($date): string
    {
        return $date ? $date->format('d-m-Y') : '—';
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Judge screening assignment notification failed after all retries', [
            'error'   => $exception->getMessage(),
            'step_id' => $this->stepId,
        ]);
    }
}
