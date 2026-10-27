<?php

namespace Tests\Feature\SaveFlows;

use App\Notifications\StartupMeetingTimeChangedNotification;
use App\Services\ZoomService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\Support\BuildsScreeningFixtures;
use Tests\Support\FakesOutboundServices;
use Tests\TestCase;

/**
 * GV-9566 save flow: an admin schedules a final-screening judging panel
 * (POST /dashboard/screening/add-judging-panels).
 *
 * Run under the reported server condition: APP_TIMEZONE=UTC, admins in Cairo.
 * The typed wall-clock is stored as typed, Zoom is booked at that Cairo time,
 * "in the past" is judged on the platform clock, the judge dashboard shows the
 * same time back, and the startup is told the time in its founder's own zone.
 */
class ScreeningPanelSaveTest extends TestCase
{
    use BuildsScreeningFixtures;
    use FakesOutboundServices;

    private string $originalZone;
    private array $program;
    private \App\User $admin;
    private \App\User $judge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalZone = date_default_timezone_get();
        $this->setStorageZone('UTC');
        $this->setPlatformZone('Africa/Cairo');
        $this->fakeOutboundServices();

        $this->program = $this->makeFinalScreeningProgram();
        $this->admin = $this->makeUser('admin', null);
        $this->judge = $this->makeUser('judger', null);

        Carbon::setTestNow(Carbon::parse('2026-10-01 09:00', 'Africa/Cairo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->setStorageZone($this->originalZone);

        parent::tearDown();
    }

    private function savePanel(string $date, string $start, string $end, bool $notify = false)
    {
        return $this->actingAs($this->admin)->postJson('/dashboard/screening/add-judging-panels', [
            'step' => $this->program['step'],
            'panels' => [[
                'judges' => [$this->judge->id],
                'companies' => [$this->program['company']],
                'session_date' => $date,
                'start_time' => $start,
                'end_time' => $end,
                'notify' => $notify,
            ]],
            'acknowledge_judge_conflicts' => true,
        ]);
    }

    public function test_the_typed_panel_is_stored_as_typed_and_zoom_is_booked_at_that_platform_time(): void
    {
        $this->savePanel('2026-10-01', '14:00', '14:30')->assertOk()->assertJson(['success' => true]);

        $row = DB::table('screenings')->where('step_id', $this->program['step'])->first();
        $this->assertSame('2026-10-01', (string) $row->session_date);
        $this->assertSame('14:00:00', substr((string) $row->start_time, 0, 8));
        $this->assertStringStartsWith('2026-10-01 14:00', (string) DB::table('finalscreenings')->where('step_id', $this->program['step'])->value('datetime'));

        $payload = $this->meetings->created()[0];
        $zoom = (new \ReflectionClass(ZoomService::class))->newInstanceWithoutConstructor();
        $this->assertSame('Africa/Cairo', $payload['timezone']);
        $this->assertSame('2026-10-01T14:00:00', $zoom->toZoomTimeFormat($payload['start_time'], $payload['timezone']));
    }

    public function test_a_start_already_past_on_the_platform_clock_is_rejected(): void
    {
        // 10:30 Cairo = 07:30 on the UTC server: 10:00 looks "future" in UTC but is past.
        Carbon::setTestNow(Carbon::parse('2026-10-01 10:30', 'Africa/Cairo'));

        $this->savePanel('2026-10-01', '10:00', '10:30')->assertStatus(422)->assertJson(['success' => false]);
        $this->assertFalse(DB::table('screenings')->where('step_id', $this->program['step'])->exists());

        $this->savePanel('2026-10-01', '11:00', '11:30')->assertOk();
    }

    public function test_the_judge_dashboard_shows_the_saved_time_back_and_opens_join_24h_before(): void
    {
        $this->savePanel('2026-10-02', '14:00', '14:30')->assertOk();

        Carbon::setTestNow(Carbon::parse('2026-10-01 14:00', 'Africa/Cairo'));
        $row = $this->rowHtml($this->actingAs($this->judge)->get('/dashboard')->assertOk()->getContent(), 'GV9566 Dummy Startup');

        $this->assertNotNull($row);
        $text = preg_replace('/\s+/', ' ', strip_tags($row));
        $this->assertStringContainsString('02-10-2026', $text);
        $this->assertStringContainsString('02:00 PM - 02:30 PM', $text);
        $this->assertStringContainsString('href="https://example.invalid/fake-meeting/1"', $row);
    }

    public function test_the_startup_is_told_the_time_in_its_founders_own_timezone(): void
    {
        $this->program['founder']->forceFill(['timezone' => 'Asia/Kolkata'])->save();

        $this->savePanel('2026-10-01', '14:00', '14:30', true)->assertOk();

        $founder = $this->program['founder'];
        Notification::assertSentTo($founder, StartupMeetingTimeChangedNotification::class, function ($notification) use ($founder) {
            $message = $notification->toDatabase($founder)['message'];
            // 14:00 Cairo = 16:30 Kolkata
            return str_contains($message, '01-10-2026') && str_contains($message, '04:30 PM');
        });
    }
}
