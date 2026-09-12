<?php

namespace App\Providers;

use App\Listeners\ConfigureTenantMailTransport;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Route every email through the involved organization's SMTP account,
        // falling back to the platform configuration.
        NotificationSending::class => [
            [ConfigureTenantMailTransport::class, 'sending'],
        ],
        NotificationSent::class => [
            [ConfigureTenantMailTransport::class, 'sent'],
        ],
        MessageSending::class => [
            [ConfigureTenantMailTransport::class, 'messageSending'],
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
