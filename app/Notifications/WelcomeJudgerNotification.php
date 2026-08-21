<?php

namespace App\Notifications;

use App\Settings;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Broadcasting\WhatsAppChannel;

class WelcomeJudgerNotification extends Notification implements ShouldQueue
{
    use Queueable, ConfiguresSmtp;

    public $tries = 3;
    public $backoff = 60;

    public $data;

    /**
     * Create a new notification instance.
     *
     * Accepts either a User model (as dispatched from UserService when an admin
     * creates a judge) or an array payload (as dispatched from
     * FinalscreeningController when judges are assigned to a screening step).
     * Both are normalised to an array here so the mail/database/whatsapp/
     * broadcast representations can rely on a single shape.
     *
     * @param \App\User|array $data
     */
    public function __construct($data)
    {
        $this->data = $this->normalize($data);
    }

    /**
     * Normalise the constructor payload into a plain array.
     *
     * @param \App\User|array $data
     * @return array
     */
    protected function normalize($data): array
    {
        if (is_array($data)) {
            $normalized = $data;
        } else {
            $normalized = [
                'role'   => 'Judger',
                'name'   => trim(($data->first_name_en ?? '') . ' ' . ($data->last_name_en ?? '')),
                'email'  => $data->email ?? null,
                'gender' => $data->gender ?? null,
            ];
        }

        // The judge is always a judge here — callers that omit the role would
        // otherwise silently fall through to the generic welcome email.
        $normalized['role'] = $normalized['role'] ?? 'Judger';

        // "Assigned By" has no persisted column on users; capture the acting
        // admin at dispatch time (these notifications are always raised from an
        // authenticated admin request) so it survives being queued.
        if (empty($normalized['assigned_by']) && Auth::check()) {
            $normalized['assigned_by'] = Auth::user()->name;
        }

        return $normalized;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = [];
        // Always store in database
        $channels[] = 'database';

        // WhatsApp if enabled
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
        $org = $notifiable->organization;
        $smtpSettings = $org?->organizationSettings;

        $this->configureSmtp($smtpSettings);
        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->buildEmailOrganizationData($org);

        $setting = Settings::first();
        $platformName = $setting ? ($setting->Platform_Name ?? config('app.name')) : config('app.name');

        $locale = $notifiable->preferredLocale() === 'ar' ? 'ar' : 'en';

        if ($this->data['role'] !== 'Judger') {
            // emails.welcome is shared with WelcomeUserNotification and expects
            // $user (and optionally $magicLoginUrl) — not just $data.
            return (new MailMessage)
                ->from($from['address'], $from['name'])
                ->subject(Lang::get('notifications.welcome.subject', [], $locale))
                ->view('emails.welcome', array_merge([
                    'data'          => $this->data,
                    'user'          => $notifiable,
                    'magicLoginUrl' => $notifiable->generateMagicLoginUrl(),
                ], $orgData));
        }

        // Single-use, 24h signed link that logs the judge in and lands them on
        // the profile page with a forced password change.
        $magicLoginUrl = $notifiable->generateMagicLoginUrl();

        return (new MailMessage)
            ->from($from['address'], $from['name'])
            ->subject(Lang::get('email.judger_welcome_email.subject', [], $locale))
            ->view('emails.welcome_judger', array_merge([
                'data'          => $this->data,
                'locale'        => $locale,
                'platformName'  => $platformName,
                'judgerName'    => $this->data['name'] ?? $notifiable->name,
                'judgerEmail'   => $this->data['email'] ?? $notifiable->email,
                'judgerGender'  => $this->data['gender'] ?? $notifiable->gender ?? null,
                'assignedBy'    => $this->data['assigned_by'] ?? null,
                'magicLoginUrl' => $magicLoginUrl,
            ], $orgData));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $locale = $notifiable->preferredLocale() === 'ar' ? 'ar' : 'en';
        $key = $this->data['role'] === 'Judger'
            ? 'notifications.welcome_judger.database'
            : 'notifications.welcome.database';

        return [
            'message' => Lang::get($key, [], $locale),
            'user_id' => $notifiable->id,
        ];
    }

    /**
     * WhatsApp message content.
     */
    public function toWhatsapp(object $notifiable): string
    {
        $locale = $notifiable->preferredLocale() === 'ar' ? 'ar' : 'en';
        $key = $this->data['role'] === 'Judger'
            ? 'notifications.welcome_judger.whatsapp'
            : 'notifications.welcome.whatsapp';

        return Lang::get($key, [
            'name' => $this->data['name'] ?? trim(($notifiable->first_name_en ?? '') . ' ' . ($notifiable->last_name_en ?? '')),
        ], $locale);
    }

    /**
     * Broadcast payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $locale = $notifiable->preferredLocale() === 'ar' ? 'ar' : 'en';
        $group = $this->data['role'] === 'Judger' ? 'welcome_judger' : 'welcome';

        return new BroadcastMessage([
            'subject' => Lang::get("notifications.{$group}.subject", [], $locale),
            'message' => Lang::get("notifications.{$group}.broadcast", [], $locale),
            'time' => now()->toDateTimeString(),
        ]);
    }
}
