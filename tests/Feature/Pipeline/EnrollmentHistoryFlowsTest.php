<?php

namespace Tests\Feature\Pipeline;

use App\Company;
use App\CompanyEnrollment;
use Illuminate\Support\Facades\DB;
use Tests\Support\BuildsScreeningFixtures;
use Tests\Support\FakesOutboundServices;
use Tests\TestCase;

/**
 * P1-01 through the real screens and the scheduler: the places that move a company
 * between steps, reject it or mark it as alumni must leave the right enrollment row.
 *
 * The fixture company is inserted the way old data exists, with no enrollment row,
 * and gets one from the same backfill the migration runs.
 */
class EnrollmentHistoryFlowsTest extends TestCase
{
    use BuildsScreeningFixtures;
    use FakesOutboundServices;

    private array $program;
    private \App\User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeOutboundServices();
        $this->program = $this->makeFinalScreeningProgram();
        $this->admin = $this->makeUser('admin', null);

        CompanyEnrollment::backfill();
    }

    private function enrollment(): CompanyEnrollment
    {
        return CompanyEnrollment::where('company_id', $this->program['company'])->sole();
    }

    private function addStep(array $attributes = []): int
    {
        return DB::table('steps')->insertGetId($attributes + [
            'cycle_id' => $this->program['cycle'],
            'title' => 'Next step',
            'stype' => 3,
            'arr' => 2,
            'from' => '2026-11-01',
            'to' => '2026-11-30',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_backfilled_fixture_starts_active_on_its_step(): void
    {
        $this->assertSame(CompanyEnrollment::STATUS_ACTIVE, $this->enrollment()->status);
        $this->assertSame($this->program['step'], $this->enrollment()->step_id);
        $this->assertNull($this->enrollment()->joined_at, 'the join date of older data was never stored');
    }

    public function test_disapproving_at_screening_records_a_rejection(): void
    {
        $this->actingAs($this->admin)
            ->post(route('screening.change_step'), ['company' => $this->program['company'], 'step' => '0'])
            ->assertSessionHasNoErrors();

        $this->assertNull(Company::find($this->program['company'])->cycle);
        $enrollment = $this->enrollment();
        $this->assertSame(CompanyEnrollment::STATUS_REJECTED, $enrollment->status);
        $this->assertSame($this->program['step'], $enrollment->step_id, 'the step it was rejected at is kept');
        $this->assertNotNull($enrollment->ended_at);
    }

    public function test_moving_a_company_to_the_next_step_from_screening_updates_its_enrollment(): void
    {
        $next = $this->addStep();

        $this->actingAs($this->admin)
            ->post(route('screening.change_step'), ['company' => $this->program['company'], 'step' => (string) $next])
            ->assertSessionHasNoErrors();

        $this->assertSame($next, Company::find($this->program['company'])->step);
        $enrollment = $this->enrollment();
        $this->assertSame($next, $enrollment->step_id);
        $this->assertSame(CompanyEnrollment::STATUS_ACTIVE, $enrollment->status);
    }

    public function test_marking_as_alumni_records_a_completion(): void
    {
        DB::table('companies')->where('id', $this->program['company'])
            ->update(['country_id' => DB::table('countries')->value('id')]);

        // The screen calls this with an XHR; without that header it answers with a redirect.
        $this->actingAs($this->admin)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('company.addCompanyAlumni', $this->program['company']))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNull(Company::find($this->program['company'])->cycle);
        $enrollment = $this->enrollment();
        $this->assertSame(CompanyEnrollment::STATUS_COMPLETED, $enrollment->status);
        $this->assertNotNull($enrollment->ended_at);
    }

    public function test_the_scheduled_step_change_moves_the_enrollment_too(): void
    {
        // A time-driven program whose first step has ended and whose second is open now.
        DB::table('cycles')->where('id', $this->program['cycle'])->update([
            'step_type' => 'time',
            'start' => now()->subDays(20)->toDateString(),
            'end' => now()->addDays(20)->toDateString(),
        ]);
        DB::table('steps')->where('id', $this->program['step'])->update([
            'from' => now()->subDays(20)->toDateString(),
            'to' => now()->subDays(5)->toDateString(),
        ]);
        $open = $this->addStep([
            'from' => now()->subDays(4)->toDateString(),
            'to' => now()->addDays(20)->toDateString(),
        ]);

        $this->artisan('cycle:change-step')->assertSuccessful();

        $this->assertSame($open, Company::find($this->program['company'])->step);
        $this->assertSame($open, $this->enrollment()->step_id);
        $this->assertSame(CompanyEnrollment::STATUS_ACTIVE, $this->enrollment()->status);
    }
}
