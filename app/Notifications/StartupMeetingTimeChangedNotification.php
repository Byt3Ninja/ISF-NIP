<?php

namespace App\Notifications;

use App\Company;
use App\Settings;
use App\Step;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Broadcasting\WhatsAppChannel;
use App\Services\GlobalNotificationSettingsService;
use Throwable;

/**
 * Tells a startup that its screening meeting time moved, or that its meeting
 * was cancelled (GV-8892 / GV-8893).
 *
 * Sent for both initial screening (stype 2) and final screening (stype 5).
 *
 * With interval scheduling each startup's slot is derived from its POSITION in
 * the panel's company list, so adding, removing or reordering startups reflows
 * the times of everyone after the edit — not just the startup the admin
 * touched. The caller diffs the before/after slots and hands this class only
 * the startups whose time actually changed; this class just renders for
 * whoever it is given.
 *
 * The notifiable is the company OWNER (`companies.user_id`), since a Company is
 * not itself notifiable.
 */
class StartupMeetingTimeChangedNotification extends Notification implements ShouldQueue
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
     * The screening step whose schedule changed.
     *
     * Stored as ids, not models: this notification is queued, and serialized
     * models would restore stale attributes (or fail outright if the step or
     * company is edited before the worker picks the job up).
     *
     * @var int
     */
    public $stepId;

    /**
     * The startup whose meeting changed.
     *
     * @var int
     */
    public $companyId;

    /**
     * The slot start as 'Y-m-d H:i' — the NEW time when rescheduled, or the
     * time the cancelled meeting had been set for.
     *
     * Passed as a plain string rather than a Carbon so the queued payload does
     * not depend on Carbon's serialization format.
     *
     * @var string|null
     */
    public $slotStart;

    /**
     * Whether the meeting was cancelled rather than moved.
     *
     * @var bool
     */
    public $cancelled;

    public function __construct(int $stepId, int $companyId, ?string $slotStart, bool $cancelled = false)
    {
        $this->stepId = $stepId;
        $this->companyId = $companyId;
        $this->slotStart = $slotStart;
        $this->cancelled = $cancelled;
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
        $org = $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;

        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->buildEmailOrganizationData($org);

        $this->configureSmtp($smtpSettings);

        // Same rule as the judge notification: apply the platform default
        // language explicitly. User implements HasLocalePreference, so Laravel
        // otherwise restores the recipient's own language around this queued
        // job, and it must run before ->view() because the blade and
        // emails/layout/header read app()->getLocale() lazily at render time.
        app()->setLocale($this->resolveLocale($setting, $notifiable));

        $step = Step::with('cycle')->find($this->stepId);
        $key = $this->cancelled ? 'cancelled' : 'rescheduled';

        return (new MailMessage)
            ->from($from['address'], $from['name'])
            ->subject(__('email.startup_meeting_changed_email.' . $key . '.subject'))
            ->view('emails.startup_meeting_time_changed', array_merge([
                'recipient'     => $notifiable,
                'startupName'   => $this->startupName(),
                'programName'   => $this->programName($step, $platformName),
                'meetingDate'   => $this->formatDate(),
                'meetingTime'   => $this->formatTime(),
                'cancelled'     => $this->cancelled,
                'dashboardUrl'  => url('/dashboard'),
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
        $key = $this->cancelled ? 'cancelled' : 'rescheduled';

        return [
            'message' => __('email.startup_meeting_changed_email.' . $key . '.database', [
                'date' => $this->formatDate(),
                'time' => $this->formatTime(),
            ]),
        ];
    }

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        $step = Step::with('cycle')->find($this->stepId);
        $key = $this->cancelled ? 'cancelled' : 'rescheduled';

        return __('email.startup_meeting_changed_email.' . $key . '.whatsapp', [
            'name'         => trim($notifiable->name ?? ''),
            'program_name' => $this->programName($step, config('app.name')),
            'date'         => $this->formatDate(),
            'time'         => $this->formatTime(),
        ]);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $key = $this->cancelled ? 'cancelled' : 'rescheduled';

        return new BroadcastMessage([
            'subject' => __('email.startup_meeting_changed_email.' . $key . '.subject'),
            'message' => __('email.startup_meeting_changed_email.' . $key . '.database', [
                'date' => $this->formatDate(),
                'time' => $this->formatTime(),
            ]),
            'url'     => '/dashboard',
            'time'    => now()->toDateTimeString(),
        ]);
    }

    /**
     * Decide the language this email renders in.
     *
     * Order: the platform default from general settings, then the recipient's
     * own `users.language`, then the app locale — matching
     * JudgeAssignedToScreeningNotification, so both screening emails behave the
     * same way. The recipient's preference sits in the middle because most rows
     * have `language` NULL.
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
     * The program (cycle) name in the active locale, falling back to the
     * platform name when the step has no cycle.
     */
    protected function programName(?Step $step, string $fallback): string
    {
        $title = $step?->cycle?->title;

        return filled($title) ? $title : $fallback;
    }

    /**
     * The startup's own name, looked up lazily so the queued payload stays a
     * plain id. Blank when the company has since been deleted — the email still
     * makes sense addressed to the owner by name.
     */
    protected function startupName(): string
    {
        $company = Company::find($this->companyId);

        return trim((string) ($company->title ?? ''));
    }

    /**
     * The meeting date, or an em dash when the slot is unknown.
     */
    protected function formatDate(): string
    {
        return $this->slot()?->format('d-m-Y') ?? '—';
    }

    /**
     * The meeting start time, or an em dash when the slot is unknown.
     */
    protected function formatTime(): string
    {
        return $this->slot()?->format('h:i A') ?? '—';
    }

    /**
     * Parse the stored slot string, tolerating a malformed value rather than
     * failing the whole notification over a display field.
     */
    protected function slot(): ?Carbon
    {
        if (empty($this->slotStart)) {
            return null;
        }

        try {
            return Carbon::parse($this->slotStart);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Startup meeting time change notification failed after all retries', [
            'error'      => $exception->getMessage(),
            'step_id'    => $this->stepId,
            'company_id' => $this->companyId,
        ]);
    }
}
