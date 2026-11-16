<?php

namespace Tests\Feature\Correctness;

use App\Console\Commands\SendServiceMessageEmailReminders;
use App\Cycle;
use App\Jobs\SendMentorshipRequestJob;
use App\Notifications\MentorNotifyMentorshipNotification;
use App\Notifications\ServiceMessageEmailNotification;
use App\Notifications\WelcomeUserNotification;
use App\Services\Importer\ImporterEmailDispatcher;
use App\Services\ProgramCreatedNotifier;
use App\Support\NotificationClaim;
use ArrayObject;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\Support\FakesEmailDns;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * X3 (C6, C7, C8, C25, C26, C35, C38): a "sent" stamp must be released when the send fails,
 * welcome mail must leave only after the account is committed, and WhatsApp texts must reach
 * the right person with the right decision.
 */
class NotifyAfterCommitTest extends TestCase
{
    use SecurityActors, FakesEmailDns;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
    }

    /** Every notification send throws, like an SMTP outage. */
    private function failNotifications(): void
    {
        Notification::swap(new class implements Dispatcher {
            public function send($notifiables, $notification)
            {
                throw new RuntimeException('mail down');
            }

            public function sendNow($notifiables, $notification, ?array $channels = null)
            {
                throw new RuntimeException('mail down');
            }
        });
    }

    /** Records each notification with the DB transaction level it was sent at (1 = the test's own). */
    private function recordNotificationLevels(): ArrayObject
    {
        $log = new ArrayObject();
        Notification::swap(new class($log) implements Dispatcher {
            public function __construct(private ArrayObject $log)
            {
            }

            public function send($notifiables, $notification)
            {
                $this->log[] = [get_class($notification), DB::transactionLevel()];
            }

            public function sendNow($notifiables, $notification, ?array $channels = null)
            {
                $this->send($notifiables, $notification);
            }
        });

        return $log;
    }

    private function serviceThread(): array
    {
        $providerUser = $this->actor('serviceProvider');
        $providerId = DB::table('service_providers')->insertGetId(['user_id' => $providerUser->id, 'company_name' => 'Audit SP', 'created_at' => now(), 'updated_at' => now()]);
        $serviceId = DB::table('services')->insertGetId([
            'service_category_id' => (int) DB::table('service_categories')->value('id'),
            'service_provider_id' => $providerId, 'name_en' => 'Audit Service', 'name_ar' => 'Audit Service',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        [, $founder] = $this->startup($this->program($this->organization()));
        $requestId = DB::table('services_requests')->insertGetId(['service_id' => $serviceId, 'user_id' => $founder->id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('service_request_messages')->insert([
            'service_request_id' => $requestId, 'sender_id' => $founder->id, 'body' => 'hello',
            'created_at' => now()->subHours(3), 'updated_at' => now()->subHours(3),
        ]);

        return [$requestId, $founder, $providerUser];
    }

    private function unemailed(int $requestId): int
    {
        return DB::table('service_request_messages')->where('service_request_id', $requestId)->whereNull('emailed_at')->count();
    }

    public function test_service_message_reminder_is_not_stamped_when_the_email_fails(): void
    {
        [$requestId] = $this->serviceThread();
        $this->failNotifications();

        $this->artisan(SendServiceMessageEmailReminders::class);

        $this->assertSame(1, $this->unemailed($requestId));
    }

    public function test_service_message_reminder_is_stamped_once_when_the_email_is_sent(): void
    {
        [$requestId, , $provider] = $this->serviceThread();

        $this->artisan(SendServiceMessageEmailReminders::class);
        $this->artisan(SendServiceMessageEmailReminders::class);

        $this->assertSame(0, $this->unemailed($requestId));
        $this->assertCount(1, Notification::sent($provider, ServiceMessageEmailNotification::class));
    }

    public function test_service_message_reminder_never_overlaps(): void
    {
        $this->app->make(ConsoleKernel::class);
        $event = collect($this->app->make(Schedule::class)->events())
            ->first(fn ($e) => str_contains((string) $e->command, 'service-messages:send-email-reminders'));

        $this->assertNotNull($event);
        $this->assertTrue($event->withoutOverlapping);
    }

    private function mentorship(array $attributes = []): array
    {
        $orgId = $this->organization();
        $cycleId = $this->program($orgId);
        [, $founder] = $this->startup($cycleId);
        $founder->forceFill(['country_code_w_a' => '20', 'whatsapp' => '1000000001', 'first_name_en' => 'Fatma', 'last_name_en' => 'Founder'])->save();
        $mentor = $this->actor('mentor', ['country_code_w_a' => '20', 'whatsapp' => '1000000002', 'first_name_en' => 'Mona', 'last_name_en' => 'Mentor']);
        $id = DB::table('mentorship_requests')->insertGetId(array_merge([
            'mentor_id' => $mentor->id, 'user_id' => $founder->id, 'cycle_id' => $cycleId, 'organization_id' => $orgId,
            'zoom_date' => now()->addDay()->format('Y-m-d H:i:s'), 'period' => 60,
            'created_at' => now(), 'updated_at' => now(),
        ], $attributes));

        return [$id, $mentor, $founder];
    }

    public function test_mentorship_claim_is_released_when_the_notification_fails(): void
    {
        [$id, $mentor, $founder] = $this->mentorship();
        $this->failNotifications();

        try {
            (new SendMentorshipRequestJob($mentor->id, now()->addDay()->format('Y-m-d H:i'), 60, $founder->id, null, $id, 'Yes', 'addwait'))->handle();
        } catch (RuntimeException $e) {
            // the job rethrows so the queue retries it
        }

        $this->assertNull(DB::table('mentorship_requests')->where('id', $id)->value('mentor_notified_at'));
    }

    public function test_queued_notification_failure_releases_its_claim(): void
    {
        [$id] = $this->mentorship();
        $claim = NotificationClaim::on('mentorship_requests', ['id' => $id], 'mentor_notified_at');
        $this->assertTrue($claim->take());

        $notification = (new MentorNotifyMentorshipNotification(['s', 'g', 'b', '/dashboard', 'b'], DB::table('mentorship_requests')->find($id)))->withClaim($claim);
        $notification->failed(new RuntimeException('worker failed'));

        $this->assertNull(DB::table('mentorship_requests')->where('id', $id)->value('mentor_notified_at'));
    }

    private function whatsAppTo(string $phone): array
    {
        return array_values(array_filter($this->whatsApp->sent, fn ($m) => str_contains((string) $m['phone'], $phone)));
    }

    public function test_session_update_whatsapp_to_mentor_names_the_founder(): void
    {
        [$id, $mentor, $founder] = $this->mentorship(['approved' => 'Yes']);

        (new SendMentorshipRequestJob($mentor->id, now()->addDay()->format('Y-m-d H:i'), 60, $founder->id, null, $id, null, 'modifySession'))->handle();

        $toMentor = $this->whatsAppTo('1000000002');
        $this->assertNotEmpty($toMentor);
        $this->assertStringContainsString('Fatma Founder', $toMentor[0]['message']);
        $this->assertStringNotContainsString("with 'Mona Mentor'", $toMentor[0]['message']);
    }

    public function test_decision_whatsapp_goes_to_each_party_with_the_real_decision(): void
    {
        [$id, $mentor, $founder] = $this->mentorship();

        (new SendMentorshipRequestJob($mentor->id, now()->addDay()->format('Y-m-d H:i'), 60, $founder->id, null, $id, 'Yes', 'modify'))->handle();

        $toMentor = $this->whatsAppTo('1000000002');
        $toFounder = $this->whatsAppTo('1000000001');
        $this->assertCount(1, $toMentor, 'the mentor gets their own WhatsApp');
        $this->assertCount(1, $toFounder);
        $this->assertStringContainsString('Approved', $toMentor[0]['message']);
        $this->assertStringContainsString('Approved', $toFounder[0]['message']);
        $this->assertStringNotContainsString('Rejected', $toFounder[0]['message']);
    }

    public function test_public_program_registration_sends_welcome_only_after_commit(): void
    {
        $this->fakeEmailDns(['gmail.com']); // register-email is checked with email:dns
        $orgId = $this->organization();
        DB::table('organizations')->where('id', $orgId)->update(['slug' => 'audit-org-' . uniqid()]);
        $slug = DB::table('organizations')->where('id', $orgId)->value('slug');
        $cycleId = $this->program($orgId, ['is_public' => 1, 'public_slug' => 'audit-prog-' . uniqid(), 'start' => now()->subDay()->toDateString(), 'end' => now()->addDays(10)->toDateString()]);
        $this->step($cycleId, ['stype' => 1, 'arr' => 1]);
        $log = $this->recordNotificationLevels();

        $this->postJson('/' . $slug . '/program/' . DB::table('cycles')->where('id', $cycleId)->value('public_slug') . '/register', [
            'register-email' => 'audit.join.' . uniqid() . '@gmail.com',
            'register-first-name' => 'Join',
        ])->assertJsonPath('status', true);

        $welcome = array_values(array_filter((array) $log, fn ($e) => $e[0] === WelcomeUserNotification::class));
        $this->assertCount(1, $welcome);
        $this->assertSame(1, $welcome[0][1], 'welcome sent inside the registration transaction');
    }

    public function test_api_registration_sends_welcome_only_after_commit(): void
    {
        $log = $this->recordNotificationLevels();

        $this->postJson('/api/register', [
            'firstName' => 'Api', 'lastName' => 'User', 'gender' => 'Male',
            'phone' => '4' . random_int(100000000, 999999999),
            'email' => 'audit.api.' . uniqid() . '@example.invalid',
            'password' => 'password123', 'password_confirmation' => 'password123', 'role' => 'Registered',
        ])->assertStatus(201);

        $welcome = array_values(array_filter((array) $log, fn ($e) => $e[0] === WelcomeUserNotification::class));
        $this->assertCount(1, $welcome);
        $this->assertSame(1, $welcome[0][1], 'welcome sent inside the registration transaction');
    }

    public function test_program_created_claim_is_released_when_no_one_was_notified(): void
    {
        if (!Schema::hasColumn('cycles', 'program_created_notified_at')) {
            $this->markTestSkipped('migration 2026_10_07_095450 not run on this database');
        }
        $orgId = $this->organization();
        $this->manager($orgId);
        $cycle = Cycle::findOrFail($this->program($orgId));
        $this->failNotifications();

        app(ProgramCreatedNotifier::class)->notify($cycle);

        $this->assertNull(DB::table('cycles')->where('id', $cycle->id)->value('program_created_notified_at'));
    }

    public function test_importer_counts_an_email_failure_separately_from_a_failed_row(): void
    {
        Storage::fake('importer');
        Mail::fake();
        $admin = $this->actor('admin');
        $orgId = $this->organization();

        $uuid = $this->actingAs($admin)->postJson('/dashboard/importer/sessions')->json('data.session_id');
        $path = base_path('tests/Fixtures/Importer/valid_minimal.csv');
        $this->actingAs($admin)->post("/dashboard/importer/sessions/{$uuid}/file", ['file' => new UploadedFile($path, 'valid_minimal.csv', 'text/csv', null, true)], ['Accept' => 'application/json'])->assertOk();
        $maps = $this->actingAs($admin)->postJson("/dashboard/importer/sessions/{$uuid}/automap")->json('data.mappings');
        $this->actingAs($admin)->postJson("/dashboard/importer/sessions/{$uuid}/validate", ['mappings' => $maps]);
        $this->actingAs($admin)->postJson("/dashboard/importer/sessions/{$uuid}/conflicts", ['organization_id' => $orgId, 'accept' => true]);

        $this->mock(ImporterEmailDispatcher::class, fn ($m) => $m->shouldReceive('dispatch')->andReturn(['status' => 'failed', 'reason' => 'smtp down']));

        $this->actingAs($admin)->postJson("/dashboard/importer/sessions/{$uuid}/execute", ['organization_id' => $orgId, 'accepted_warnings' => true])
            ->assertOk()
            ->assertJsonPath('data.totals.processed', 1)
            ->assertJsonPath('data.totals.imported', 1)
            ->assertJsonPath('data.totals.failed', 0)
            ->assertJsonPath('data.totals.email_failed', 1);
    }
}
