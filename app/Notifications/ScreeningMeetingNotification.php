<?php

namespace App\Notifications;

use App\Broadcasting\WhatsAppChannel;
use App\Company;
use App\Organization;
use App\Services\GlobalNotificationSettingsService;
use App\Services\TimezoneService;
use App\Settings;
use App\Step;
use App\Traits\ConfiguresSmtp;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;
use WeakMap;

/**
 * Shared delivery and rendering for the screening meeting messages sent when a
 * panel's schedule changes (GV-3295: GV-8486..GV-8759).
 *
 * One base serves both audiences (judge, founder) and both stages (initial
 * screening stype 2, final screening stype 5); a subclass supplies only its
 * audience key, its detail rows and any extra text parameters.
 *
 * Channels (GV-3295 decision D1): the "Notify now" switch governs the OUTWARD
 * channels only — email and WhatsApp. The in-platform notification (database
 * row + its live Pusher toast) always goes out, because the stories require it
 * unconditionally whenever a meeting moves or is cancelled.
 *
 * Every value is stored as a plain scalar: this is queued, and serialized
 * models would restore stale attributes or fail if deleted before the worker
 * picks the job up.
 */
abstract class ScreeningMeetingNotification extends Notification implements ShouldQueue
{
    use Queueable, ConfiguresSmtp;

    /**
     * @var int
     */
    public $tries = 3;

    /**
     * @var int
     */
    public $backoff = 60;

    /**
     * @var int
     */
    public $stepId;

    /**
     * 'scheduled', 'rescheduled' or 'cancelled' — the lang-file key.
     *
     * @var string
     */
    public $variant;

    /**
     * Meeting start as platform wall-clock 'Y-m-d H:i': the NEW time for a
     * (re)scheduled meeting, the old one for a cancelled meeting.
     *
     * @var string|null
     */
    public $slotStart;

    /**
     * Meeting end, same format as $slotStart.
     *
     * @var string|null
     */
    public $slotEnd;

    /**
     * @var string|null
     */
    public $meetingLink;

    /**
     * @var string|null
     */
    public $passcode;

    /**
     * The panel's "Notify now" switch: whether email/WhatsApp go out too.
     *
     * Defaults to true so a job queued before this property existed keeps the
     * behaviour it was queued with (it was only ever queued with the switch on).
     *
     * @var bool
     */
    public $sendEmail = true;

    /**
     * 'judge' or 'founder' — selects the lang-file branch.
     */
    abstract protected function audience(): string;

    /**
     * The "Meeting Details" rows, label => value. A null or blank value hides
     * its row, which is how the tickets' "If any (Hidden if there is none)"
     * fields are honoured.
     *
     * @return array<string, ?string>
     */
    abstract protected function details(object $notifiable): array;

    /**
     * Extra text parameters beyond stage/program/date/start/end.
     *
     * @return array<string, string>
     */
    protected function extraParams(): array
    {
        return [];
    }

    abstract protected function dashboardPath(): string;

    public function via(object $notifiable): array
    {
        $smtpSettings = $this->organization($notifiable)?->organizationSettings;

        $this->configureSmtp($smtpSettings);

        $orgAcceptEmail = $smtpSettings?->accept_email ?? true;
        $orgAcceptWhatsapp = $smtpSettings?->accept_whatsapp ?? true;
        $orgAcceptPusher = $smtpSettings?->accept_pusher ?? true;

        // Database first: toMail() switches the locale to the platform default,
        // and Laravel sends every channel inside one locale scope, so a channel
        // after mail would inherit it instead of the recipient's own language.
        $channels = ['database'];

        if (GlobalNotificationSettingsService::isPusherEnabled()
            && $orgAcceptPusher
            && $notifiable->accept_pusher) {
            $channels[] = 'broadcast';
        }

        if (!$this->sendEmail) {
            return $channels;
        }

        if (GlobalNotificationSettingsService::isWhatsAppEnabled()
            && $orgAcceptWhatsapp
            && $notifiable->accept_whatsapp) {
            $channels[] = WhatsAppChannel::class;
        }

        if (GlobalNotificationSettingsService::isEmailEnabled()
            && $orgAcceptEmail
            && $notifiable->accept_email) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $setting = Settings::first();
        $org = $this->organization($notifiable);
        $smtpSettings = $org?->organizationSettings;

        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->buildEmailOrganizationData($org);

        $this->configureSmtp($smtpSettings);

        // The tickets ask for the platform default language. User implements
        // HasLocalePreference, so Laravel would otherwise render in the
        // recipient's own language; this must run before ->view() because the
        // blade and the layout read app()->getLocale() at render time.
        app()->setLocale($this->resolveLocale($setting, $notifiable));

        $params = $this->params($notifiable);

        return (new MailMessage)
            ->from($from['address'], $from['name'])
            ->subject($this->text('subject', $params))
            ->view('emails.screening_meeting_changed', array_merge([
                'recipientName' => trim((string) ($notifiable->name ?? '')),
                'variant'       => $this->variant,
                'intro'         => $this->text('intro', $params),
                'detailsTitle'  => __('email.screening_meeting_email.' . ($this->variant === 'rescheduled' ? 'updated_details' : 'details')),
                'details'       => array_filter($this->details($notifiable), 'filled'),
                'outro'         => (array) __($this->key('outro'), $params),
            ], $orgData));
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->text('database', $this->params($notifiable)),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $params = $this->params($notifiable);

        return new BroadcastMessage([
            'subject' => $this->text('subject', $params),
            'message' => $this->text('database', $params),
            'url'     => $this->dashboardPath(),
            'time'    => now()->toDateTimeString(),
        ]);
    }

    public function toWhatsapp(object $notifiable): string
    {
        return __('email.screening_meeting_email.greeting', ['name' => trim((string) ($notifiable->name ?? ''))])
            . ' ' . $this->text('database', $this->params($notifiable));
    }

    public function failed(Throwable $exception): void
    {
        Log::error(static::class . ' failed after all retries', [
            'error'   => $exception->getMessage(),
            'step_id' => $this->stepId,
            'variant' => $this->variant,
        ]);
    }

    /**
     * The parameters every message of this family can use.
     *
     * @return array<string, string>
     */
    protected function params(object $notifiable): array
    {
        return array_merge([
            'stage'   => $this->stageName(),
            'program' => $this->programName(),
            'date'    => $this->formatDate($notifiable),
            'start'   => $this->formatClock($this->slotStart, $notifiable),
            'end'     => $this->formatClock($this->slotEnd, $notifiable),
        ], $this->extraParams());
    }

    protected function key(string $part): string
    {
        return 'email.screening_meeting_email.' . $this->audience() . '.' . $this->variant . '.' . $part;
    }

    protected function text(string $part, array $params): string
    {
        return __($this->key($part), $params);
    }

    /**
     * Loaded once per render. Kept in a static map keyed by this object rather
     * than a property, so the model never ends up in the queued payload.
     */
    protected function step(): ?Step
    {
        static $steps;
        $steps ??= new WeakMap();

        if (!isset($steps[$this])) {
            $steps[$this] = [Step::with('cycle.organization')->find($this->stepId)];
        }

        return $steps[$this][0];
    }

    /**
     * The organization that owns the program — its logo, name and SMTP account
     * brand the email. The recipient's own `users.organization_id` is only a
     * fallback: judges frequently have none, and would otherwise get platform
     * branding for a program run by an organization.
     */
    protected function organization(object $notifiable): ?Organization
    {
        return $this->step()?->cycle?->organization ?? ($notifiable->organization ?? null);
    }

    protected function stageName(): string
    {
        $stage = (int) ($this->step()?->stype) === 5 ? 'final' : 'screening';

        return __('email.screening_meeting_email.stage.' . $stage);
    }

    protected function stepName(): string
    {
        return trim((string) ($this->step()?->title ?? ''));
    }

    protected function programName(): string
    {
        $title = $this->step()?->cycle?->title;

        if (filled($title)) {
            return $title;
        }

        $setting = Settings::first();

        return $setting?->Platform_Name ?? config('app.name');
    }

    /**
     * A startup's name in the active locale, falling back to the other one —
     * companies carry name_en / name_ar and either may be blank.
     */
    protected function companyName(?Company $company): string
    {
        if (!$company) {
            return '';
        }

        $name = trim((string) $company->name);

        return $name !== '' ? $name : trim((string) ($company->name_en ?: $company->name_ar));
    }

    /**
     * "10:00 AM – 10:30 AM", or just the start when the meeting has no end.
     */
    protected function timeRange(object $notifiable): string
    {
        $start = $this->formatClock($this->slotStart, $notifiable);
        $end = $this->formatClock($this->slotEnd, $notifiable);

        return $end === '—' ? $start : $start . ' – ' . $end;
    }

    protected function formatDate(object $notifiable): string
    {
        return $this->moment($this->slotStart, $notifiable)?->format('d-m-Y') ?? '—';
    }

    protected function formatClock(?string $value, object $notifiable): string
    {
        return $this->moment($value, $notifiable)?->format('h:i A') ?? '—';
    }

    /**
     * GV-9566: slots are platform wall-clock of the step's organization, shown
     * in the recipient's own timezone.
     */
    protected function moment(?string $value, object $notifiable): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        return TimezoneService::forViewer(
            TimezoneService::wallClock($value, TimezoneService::forStep($this->stepId)),
            $notifiable
        );
    }

    /**
     * Platform default language from General Settings, then the recipient's
     * own, then the app locale — the order every screening email uses.
     */
    protected function resolveLocale($setting, object $notifiable): string
    {
        foreach ([$setting?->Default_Language, $notifiable->language ?? null, config('app.locale', 'en')] as $locale) {
            if (in_array($locale, ['en', 'ar'], true)) {
                return $locale;
            }
        }

        return 'en';
    }
}
