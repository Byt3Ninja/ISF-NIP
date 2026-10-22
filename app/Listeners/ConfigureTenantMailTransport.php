<?php

namespace App\Listeners;

use App\Services\EmailBranding;
use App\Services\TenantMailer;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;

/**
 * Applies the sending organization's mail transport globally, so every email
 * is routed through the right SMTP account without each notification or
 * mailable having to remember to do it.
 *
 * NotificationSending fires before MailChannel resolves the mailer, which is
 * the last point at which the transport can still be swapped.
 */
class ConfigureTenantMailTransport
{
    /**
     * Route a notification through the involved organization's transport.
     *
     * Handles the AnonymousNotifiable case (Notification::route('mail', $email)),
     * where the notifiable carries no organization at all and every email would
     * otherwise silently go out on the platform transport.
     */
    public function sending(NotificationSending $event): void
    {
        if ($event->channel !== 'mail') {
            return;
        }

        TenantMailer::applyFor(
            $this->organizationFor($event->notification, $event->notifiable)
        );

        // Brand the email (logo, name, footer) with the organization it is
        // about, before toMail() builds and the layout renders it.
        EmailBranding::begin($event->notification);
    }

    /**
     * Restore the platform configuration once the notification has been sent,
     * so one tenant's SMTP never leaks into the next job on a shared worker.
     */
    public function sent(NotificationSent $event): void
    {
        if ($event->channel !== 'mail') {
            return;
        }

        TenantMailer::restore();
        EmailBranding::end();
    }

    /**
     * Make sure the message actually carries the sender of the transport that
     * is about to send it. Mailables that never set a From would otherwise be
     * stamped with whatever global from address was configured at boot.
     */
    public function messageSending(MessageSending $event): void
    {
        // The message is fully rendered by now: stop branding, so nothing
        // rendered later in the same worker inherits this email's brand, even
        // if the send itself fails.
        EmailBranding::end();

        $from = config('mail.from.address');
        $name = config('mail.from.name');

        if (!$from) {
            return;
        }

        $current = $event->message->getFrom();

        // Only stamp when the message has no explicit sender of its own.
        if (empty($current)) {
            $event->message->from(new \Symfony\Component\Mime\Address($from, (string) $name));
        }
    }

    /**
     * Resolve the organization an email belongs to: an explicit organization
     * exposed by the notification wins, otherwise fall back to the notifiable
     * (user, or recipient address for anonymous notifiables).
     */
    protected function organizationFor($notification, $notifiable)
    {
        foreach (['organization', 'org'] as $property) {
            if (isset($notification->{$property})) {
                $organization = TenantMailer::resolveOrganization($notification->{$property});

                if ($organization) {
                    return $organization;
                }
            }
        }

        // Some notifications carry the subject user rather than the notifiable.
        if (isset($notification->user)) {
            $organization = TenantMailer::resolveOrganization($notification->user);

            if ($organization) {
                return $organization;
            }
        }

        // Falls back to the recipient address for on-demand notifiables,
        // which carry no organization of their own.
        return TenantMailer::resolveOrganization($notifiable);
    }
}
