<?php

namespace Tests\Feature\SaveFlows;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\BuildsScreeningFixtures;
use Tests\Support\FakesOutboundServices;
use Tests\TestCase;

/**
 * GV-9566 save flow: a founder books a service at a wall-clock time.
 *
 * The picker posts platform wall-clock. `after:now` compared it in the server
 * zone: on a UTC server a Cairo time already 30 minutes in the past was accepted.
 */
class ServiceBookingSaveTest extends TestCase
{
    use BuildsScreeningFixtures;
    use FakesOutboundServices;

    private string $originalZone;
    private \App\User $founder;
    private int $serviceId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalZone = date_default_timezone_get();
        $this->setStorageZone('UTC');
        $this->setPlatformZone('Africa/Cairo');
        $this->fakeOutboundServices();

        $this->founder = $this->makeFinalScreeningProgram()['founder'];
        $this->serviceId = DB::table('services')->insertGetId([
            'name_en' => 'GV9566 Dummy Service', 'name_ar' => 'GV9566 Dummy Service', 'status' => 1,
            'service_category_id' => DB::table('service_categories')->value('id') ?? 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // 12:30 in Cairo = 09:30 on the UTC server clock.
        Carbon::setTestNow(Carbon::parse('2026-10-01 12:30', 'Africa/Cairo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->setStorageZone($this->originalZone);

        parent::tearDown();
    }

    private function book(string $datetime)
    {
        return $this->actingAs($this->founder)->postJson('/dashboard/service/request', [
            'id' => $this->serviceId, 'datetime' => $datetime, 'comment' => 'GV9566 booking',
        ]);
    }

    public function test_a_time_already_past_on_the_platform_clock_is_rejected(): void
    {
        // 12:00 Cairo is in the past, but would still be "future" read as UTC wall-clock.
        $this->book('2026-10-01 12:00')->assertStatus(422)->assertJsonValidationErrors('datetime');

        $this->assertFalse(DB::table('services_requests')->where('service_id', $this->serviceId)->exists());
    }

    public function test_a_future_platform_time_is_accepted_and_stored_exactly_as_typed(): void
    {
        $this->book('2026-10-01 13:00')->assertOk()->assertJson(['state' => true]);

        $this->assertSame(
            '2026-10-01 13:00:00',
            DB::table('services_requests')->where('service_id', $this->serviceId)->value('date_time')
        );
    }
}
