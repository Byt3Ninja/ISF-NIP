<?php

namespace Tests\Feature\SaveFlows;

use App\MentorshipRequest;
use App\Notifications\MentorshipSessionFounderNotification;
use App\Services\ZoomService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\Support\BuildsScreeningFixtures;
use Tests\Support\FakesOutboundServices;
use Tests\TestCase;

/**
 * GV-9566 save flows: mentorship sessions booked by an admin (add / addWait),
 * by the founder (self-booking) and rescheduled (modifySession).
 *
 * Run under the reported server condition: APP_TIMEZONE=UTC, admins in Cairo.
 * zoom_date must be stored as the typed platform wall-clock, the Zoom meeting
 * must be booked at that same time, past checks use the platform clock, and the
 * founder is told the time in their own zone.
 */
class MentorshipBookingSaveTest extends TestCase
{
    use BuildsScreeningFixtures;
    use FakesOutboundServices;

    private string $originalZone;
    private array $program;
    private \App\User $admin;
    private \App\User $mentor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalZone = date_default_timezone_get();
        $this->setStorageZone('UTC');
        $this->setPlatformZone('Africa/Cairo');
        $this->fakeOutboundServices();

        $this->program = $this->makeFinalScreeningProgram();
        $this->admin = $this->makeUser('admin', null);
        $this->mentor = $this->makeUser('mentor', null);

        // Mentor available every Thursday 08:00-18:00 (2026-10-01 is a Thursday).
        DB::table('schedules')->insert([
            'user_id' => $this->mentor->id, 'type' => 'Weekly', 'days' => 'Thursday',
            'tfrom' => '08:00:00', 'tto' => '18:00:00', 'active' => 1, 'session_type' => 'Online',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Carbon::setTestNow(Carbon::parse('2026-09-25 09:00', 'Africa/Cairo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->setStorageZone($this->originalZone);

        parent::tearDown();
    }

    private function zoomStart(array $payload): string
    {
        $zoom = (new \ReflectionClass(ZoomService::class))->newInstanceWithoutConstructor();

        return $zoom->toZoomTimeFormat($payload['start_time'], $payload['timezone'] ?? null);
    }

    private function latestSession(): MentorshipRequest
    {
        return MentorshipRequest::where('mentor_id', $this->mentor->id)->latest('id')->firstOrFail();
    }

    private function adminAddWait(string $date, string $from, string $to)
    {
        return $this->actingAs($this->admin)->postJson('/dashboard/mentorship/addwait', [
            'user_id' => $this->program['founder']->id,
            'mentorid' => $this->mentor->id,
            'organization_id' => $this->program['organization'],
            'cycle_id' => $this->program['cycle'],
            'session_type' => 'Online',
            'selected_dates' => [['date' => $date, 'time_from' => $from, 'time_to' => $to, 'session_type' => 'Online']],
        ]);
    }

    public function test_admin_add_stores_the_typed_time_and_books_zoom_at_it(): void
    {
        $this->actingAs($this->admin)->post('/dashboard/mentorship/add', [
            'userid' => $this->program['founder']->id,
            'mentorid' => $this->mentor->id,
            'date' => '2026-10-01 10:00',
            'period' => 60,
            'notes' => 'GV9566',
            'organization_id' => $this->program['organization'],
            'cycle_id' => $this->program['cycle'],
        ])->assertSessionHas('add', 1);

        $this->assertSame('2026-10-01 10:00:00', $this->latestSession()->getRawOriginal('zoom_date'));
        $this->assertSame('2026-10-01T10:00:00', $this->zoomStart($this->meetings->created()[0]));
        $this->assertSame('Africa/Cairo', $this->meetings->created()[0]['timezone']);
    }

    public function test_the_founder_is_told_the_time_in_their_own_timezone(): void
    {
        $this->program['founder']->forceFill(['timezone' => 'Asia/Kolkata'])->save();

        $this->actingAs($this->admin)->post('/dashboard/mentorship/add', [
            'userid' => $this->program['founder']->id, 'mentorid' => $this->mentor->id,
            'date' => '2026-10-01 10:00', 'period' => 60,
            'organization_id' => $this->program['organization'], 'cycle_id' => $this->program['cycle'],
        ])->assertSessionHas('add', 1);

        Notification::assertSentTo($this->program['founder'], MentorshipSessionFounderNotification::class, function ($notification) {
            $data = (new \ReflectionProperty($notification, 'sessionData'))->getValue($notification);
            // 10:00 Cairo = 12:30 Kolkata
            return $data['session_date'] === '2026-10-01' && $data['start_time'] === '12:30:00' && $data['end_time'] === '13:30:00';
        });
    }

    public function test_admin_add_wait_stores_the_slot_and_books_zoom_at_the_slot_time(): void
    {
        $this->adminAddWait('2026-10-01', '10:00', '11:00')->assertOk()->assertJson(['status' => 'success', 'booked' => 1]);

        $this->assertSame('2026-10-01 10:00:00', $this->latestSession()->getRawOriginal('zoom_date'));
        // The meeting must be booked at the slot, not at midnight of that day.
        $this->assertSame('2026-10-01T10:00:00', $this->zoomStart($this->meetings->created()[0]));
    }

    public function test_admin_add_wait_rejects_a_slot_already_past_on_the_platform_clock(): void
    {
        // 10:30 Cairo = 07:30 on the UTC server: a 10:00 slot looks "future" in UTC but is past.
        Carbon::setTestNow(Carbon::parse('2026-10-01 10:30', 'Africa/Cairo'));

        $response = $this->adminAddWait('2026-10-01', '10:00', '11:00')->assertOk()->json();

        $this->assertSame(0, $response['booked']);
        $this->assertSame(__('mentor.slot_in_past'), $response['failed'][0]['reason'] ?? null);
    }

    public function test_founder_self_booking_stores_the_slot_and_books_zoom_at_the_slot_time(): void
    {
        $this->actingAs($this->program['founder'])->post('/dashboard/mentorship/addwait', [
            'mentorid' => $this->mentor->id,
            'date' => '2026-10-01',
            'session_start_time' => '10:00',
            'session_end_time' => '11:00',
            'period' => 60,
            'session_type' => 'Online',
        ])->assertSessionHas('msg', 'Request_successfully_added_Startup');

        $this->assertSame('2026-10-01 10:00:00', $this->latestSession()->getRawOriginal('zoom_date'));
        $this->assertSame('2026-10-01T10:00:00', $this->zoomStart($this->meetings->created()[0]));
    }

    public function test_reschedule_stores_the_new_typed_time(): void
    {
        $id = DB::table('mentorship_requests')->insertGetId([
            'mentor_id' => $this->mentor->id, 'user_id' => $this->program['founder']->id,
            'organization_id' => $this->program['organization'], 'cycle_id' => $this->program['cycle'],
            'zoom_date' => '2026-10-01 10:00:00', 'period' => 60, 'approved' => 'Yes',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($this->admin)->post('/dashboard/mentorship/modify', [
            'id' => $id, 'date' => '2026-10-02 11:00 am', 'period' => 60, 'approved' => 1,
            'mentorid' => $this->mentor->id, 'userid' => $this->program['founder']->id, 'notes' => 'GV9566',
        ])->assertSessionHas('message', 'Saved_successfully');

        $this->assertSame('2026-10-02 11:00:00', DB::table('mentorship_requests')->where('id', $id)->value('zoom_date'));
        $this->assertSame('2026-10-02T11:00:00', $this->zoomStart($this->meetings->created()[0]));
    }
}
