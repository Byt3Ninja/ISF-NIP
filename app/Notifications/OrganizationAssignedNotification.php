<?php

namespace App\Notifications;

use App\Broadcasting\WhatsAppChannel;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent when an existing user is assigned to an organization.
 *
 * Distinct from OrganizationManagerWelcomeNotification: the recipient already has
 * an account, so this carries no welcome framing and no credentials — only the
 * fact that they now have access to a further organization.
 */
class OrganizationAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable, ConfiguresSmtp;

    public $organization;
    public $assignedBy;

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $organization  The organization the user was assigned to.
     * @param  mixed  $assignedBy    The admin who performed the assignment (optional).
     */
    public function __construct($organization, $assignedBy = null)
    {
        $this->organization = $organization;
        $this->assignedBy = $assignedBy;
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
        $this->withRecipientLocale($notifiable);

        $smtpSettings = $this->organization?->organizationSettings;

        $from = $this->getFromAddress($smtpSettings);
        $orgData = $this->emailBrandingData();

        $this->configureSmtp($smtpSettings);

        return (new MailMessage)
            ->from($from['address'], $from['name'])
            ->subject(__('email.org_assigned.subject', [
                'organization_name' => $this->organizationName(),
            ]))
            ->view('emails.organization_assigned', array_merge([
                'user'           => $notifiable,
                'organization'   => $this->organization,
                'assignedByName' => $this->assignedByName(),
                'dashboardUrl'   => url(\App\Providers\RouteServiceProvider::HOME),
            ], $orgData));
    }

    /**
     * Get the WhatsApp representation of the notification.
     */
    public function toWhatsapp(object $notifiable): string
    {
        $this->withRecipientLocale($notifiable);

        $name = trim(($notifiable->first_name_en ?? '') . ' ' . ($notifiable->last_name_en ?? ''))
            ?: ($notifiable->name ?? '');

        return __('email.hello') . ' ' . $name . "\n" .
            __('email.org_assigned.intro', [
                'organization_name' => $this->organizationName(),
                'platform_name'     => $this->platformName(),
            ]);
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $this->withRecipientLocale($notifiable);

        return new BroadcastMessage([
            'subject' => __('email.org_assigned.subject', [
                'organization_name' => $this->organizationName(),
            ]),
            'message' => __('notifications.organization_assigned.broadcast', [
                'organization_name' => $this->organizationName(),
            ]),
            'time'    => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     * This is used for the 'database' channel to store the notification data in the database.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $this->withRecipientLocale($notifiable);

        return [
            'message' => __('notifications.organization_assigned.database', [
                'organization_name' => $this->organizationName(),
            ]),
        ];
    }

    /**
     * Render this notification in the recipient's own language rather than the
     * locale of whoever triggered the assignment.
     */
    protected function withRecipientLocale(object $notifiable): void
    {
        app()->setLocale($notifiable->language ?: config('app.locale'));
    }

    /**
     * Resolve the organization name, falling back to the platform name.
     */
    protected function organizationName(): string
    {
        return $this->organization->name ?? $this->platformName();
    }

    /**
     * Resolve the display name of the admin who made the assignment, if known.
     */
    protected function assignedByName(): ?string
    {
        if (!$this->assignedBy) {
            return null;
        }

        $name = trim(($this->assignedBy->first_name_en ?? '') . ' ' . ($this->assignedBy->last_name_en ?? ''));

        return $name ?: ($this->assignedBy->name ?? null);
    }

    /**
     * Resolve the configured platform name.
     */
    protected function platformName(): string
    {
        $setting = \App\Settings::first();

        return $setting ? ($setting->Platform_Name ?? config('app.name')) : config('app.name');
    }
}
