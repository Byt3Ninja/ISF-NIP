<?php

namespace Tests\Support;

use App\Services\TeamsService;
use App\Services\WhatsAppService;
use App\Services\ZoomService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Support\Fakes\FakeMeetingService;
use Tests\Support\Fakes\FakeWhatsAppService;

/**
 * Cuts every outbound channel a save flow can reach: Zoom/Teams, WhatsApp,
 * notifications (mail, WhatsApp, Pusher channels), raw mail, HTTP and broadcasting.
 * Queued jobs still run (QUEUE_CONNECTION=sync), so what they would send is
 * captured by the fakes and can be asserted.
 */
trait FakesOutboundServices
{
    protected FakeMeetingService $meetings;
    protected FakeWhatsAppService $whatsApp;

    protected function fakeOutboundServices(): void
    {
        $this->meetings = new FakeMeetingService();
        $this->whatsApp = new FakeWhatsAppService();

        $this->app->instance(ZoomService::class, $this->meetings);
        $this->app->instance(TeamsService::class, $this->meetings);
        $this->app->instance(WhatsAppService::class, $this->whatsApp);

        Notification::fake();
        Mail::fake();
        Http::fake();
        Http::preventStrayRequests();
        config(['broadcasting.default' => 'null']);

        // Only the broadcast events: faking every event would also silence model
        // hooks (e.g. the timezone cache busting on Settings/Organization save).
        Event::fake([\App\Events\WebMessage::class, \App\Events\MentorshipSessionUpdated::class]);
        Storage::fake('QrCodePublic');
        Storage::fake('UsersDiskInviteMentor');
    }
}
