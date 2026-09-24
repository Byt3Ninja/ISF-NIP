<?php

namespace Tests\Feature\SaveFlows;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\BuildsScreeningFixtures;
use Tests\Support\FakesOutboundServices;
use Tests\TestCase;

/**
 * GV-9566 save flow: the mobile API books a mentorship session
 * (POST /api/mentorship-sessions).
 *
 * An offset-less time is platform wall-clock; a time WITH an offset (a client
 * sending UTC) is converted to it. Either way zoom_date is stored as platform
 * wall-clock, and "in the past" is judged on the platform clock.
 */
class ApiMentorshipBookingSaveTest extends TestCase
{
    use BuildsScreeningFixtures;
    use FakesOutboundServices;

    private string $originalZone;
    private array $program;
    private \App\User $mentor;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalZone = date_default_timezone_get();
        $this->setStorageZone('UTC');
        $this->setPlatformZone('Africa/Cairo');
        $this->fakeOutboundServices();

        $this->program = $this->makeFinalScreeningProgram();
        $this->mentor = $this->makeUser('mentor', null);
        $this->token = $this->program['founder']->createToken('gv9566')->plainTextToken;

        Carbon::setTestNow(Carbon::parse('2026-09-25 09:00', 'Africa/Cairo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->setStorageZone($this->originalZone);

        parent::tearDown();
    }

    private function book(string $at)
    {
        return $this->withHeader('Authorization', 'Bearer ' . $this->token)->postJson('/api/mentorship-sessions', [
            'mentor_id' => $this->mentor->id,
            'preferred_date_time' => $at,
            'duration_minutes' => 60,
            'override_availability' => true,
            'override_reason' => 'GV9566 test',
            'cycle_id' => $this->program['cycle'],
            'organization_id' => $this->program['organization'],
        ]);
    }

    private function storedZoomDate(): ?string
    {
        return DB::table('mentorship_requests')->where('mentor_id', $this->mentor->id)->value('zoom_date');
    }

    public function test_a_plain_time_is_stored_as_typed_platform_wall_clock(): void
    {
        $this->book('2026-10-01 10:00')->assertStatus(201);

        $this->assertSame('2026-10-01 10:00:00', $this->storedZoomDate());
    }

    public function test_a_utc_time_from_the_client_is_converted_to_platform_wall_clock(): void
    {
        $this->book('2026-10-01T07:00:00Z')->assertStatus(201);

        // 07:00 UTC = 10:00 Cairo
        $this->assertSame('2026-10-01 10:00:00', $this->storedZoomDate());
    }

    public function test_a_time_already_past_on_the_platform_clock_is_rejected(): void
    {
        // 08:30 Cairo is past (it is 09:00 Cairo), yet still "future" read in the UTC server zone.
        $this->book('2026-09-25 08:30')->assertStatus(422);

        $this->assertNull($this->storedZoomDate());
    }
}
