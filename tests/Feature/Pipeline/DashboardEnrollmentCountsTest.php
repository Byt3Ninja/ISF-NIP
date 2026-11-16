<?php

namespace Tests\Feature\Pipeline;

use App\Company;
use App\CompanyEnrollment;
use App\Http\Controllers\ReportController;
use App\Services\DashboardService;
use Illuminate\Support\Facades\DB;
use Tests\Support\FakesOutboundServices;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * P1-02, dashboards and reports: company figures are counted from active enrollments.
 * An idea in two sub-programs counts once in its program's totals and once under each
 * sub-program, wherever the single pointer on the company happens to be.
 */
class DashboardEnrollmentCountsTest extends TestCase
{
    use SecurityActors;
    use FakesOutboundServices;

    private int $organization;
    private int $firstCycle;
    private int $secondCycle;
    private int $inBoth;
    private int $inFirstOnly;
    private int $inAnotherOrganization;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeOutboundServices();

        $this->organization = $this->organization();
        $this->firstCycle = $this->program($this->organization, ['title' => 'Count First']);
        $this->secondCycle = $this->program($this->organization, ['title' => 'Count Second']);

        // The pointer of this one sits on the first cycle; it is active in the second as well.
        [$this->inBoth] = $this->startup($this->firstCycle, ['business_model' => 'Marketplace']);
        $this->enroll($this->inBoth, $this->secondCycle);
        [$this->inFirstOnly] = $this->startup($this->firstCycle, ['business_model' => 'Marketplace']);

        // Noise that must never be counted: another organization, and an ended enrollment.
        $elsewhere = $this->program($this->organization());
        [$this->inAnotherOrganization] = $this->startup($elsewhere, ['business_model' => 'Marketplace']);
        [$left] = $this->startup($this->secondCycle, ['business_model' => 'Marketplace']);
        CompanyEnrollment::closeActive($left, $this->secondCycle, CompanyEnrollment::STATUS_REJECTED);
        DB::table('companies')->where('id', $left)->update(['cycle' => null]);
    }

    private function enroll(int $companyId, int $cycleId, ?int $stepId = null): void
    {
        $enrollment = new CompanyEnrollment(['step_id' => $stepId, 'status' => CompanyEnrollment::STATUS_ACTIVE, 'joined_at' => now()]);
        $enrollment->company_id = $companyId;
        $enrollment->cycle_id = $cycleId;
        $enrollment->save();
    }

    public function test_the_enrolled_filter_matches_a_company_once_and_respects_the_organization(): void
    {
        $in = fn (?array $organizations) => Company::whereRaw('companies.id IN ' . CompanyEnrollment::activeCompanyIdsSql($organizations));

        $this->assertEqualsCanonicalizing(
            [$this->inBoth, $this->inFirstOnly],
            $in([$this->organization])->pluck('id')->all()
        );
        $this->assertSame(0, $in([])->count(), 'a viewer with no organizations sees nothing');
        $this->assertGreaterThanOrEqual(3, $in(null)->count());
    }

    public function test_the_api_dashboard_counts_once_per_program_and_once_under_each_sub_program(): void
    {
        // Two staff users of the organization: the old queries multiplied by them.
        $this->actor('organizationmanager', ['organization_id' => $this->organization]);
        $this->actor('organizationmanager', ['organization_id' => $this->organization]);

        $data = app(DashboardService::class)->organizationManagerDashboard($this->organization, 'name_en');

        $this->assertSame(2, (int) $data['programStartups']['Count First']);
        $this->assertSame(1, (int) $data['programStartups']['Count Second']);
        $this->assertSame(2, (int) $data['businessModels']['totalStartups']);
    }

    public function test_the_program_manager_dashboard_opens_and_counts_each_idea_once(): void
    {
        $manager = $this->actor('organizationmanager', ['organization_id' => $this->organization]);

        $response = $this->actingAs($manager)->get('/dashboard')->assertOk();

        $this->assertSame(2, $response->viewData('startups'));
        $this->assertSame(2, $response->viewData('organization_counts')['total_startups']);
    }

    public function test_the_cycles_report_lists_a_company_under_each_sub_program_it_is_screened_in(): void
    {
        $firstScreening = $this->step($this->firstCycle, ['stype' => 2]);
        $secondScreening = $this->step($this->secondCycle, ['stype' => 2]);
        CompanyEnrollment::where('company_id', $this->inBoth)->where('cycle_id', $this->firstCycle)->update(['step_id' => $firstScreening]);
        CompanyEnrollment::where('company_id', $this->inBoth)->where('cycle_id', $this->secondCycle)->update(['step_id' => $secondScreening]);

        $manager = $this->actor('organizationmanager', ['organization_id' => $this->organization]);
        $report = new \ReflectionMethod(ReportController::class, 'cyclesReport');
        $report->setAccessible(true);
        $data = $report->invoke(app(ReportController::class), [$this->organization], $manager, null);

        $kpis = collect($data['cycles_kpis']);
        $this->assertSame(2, (int) $kpis[$this->firstCycle]['startups']);
        $this->assertSame(1, (int) $kpis[$this->secondCycle]['startups']);
        $this->assertSame([$this->inBoth], array_column($data['cycles_screenings'][$this->firstCycle], 'id'));
        $this->assertSame([$this->inBoth], array_column($data['cycles_screenings'][$this->secondCycle], 'id'));
    }

    public function test_the_combined_dashboard_charts_count_each_idea_once(): void
    {
        // HomeController::index reads this flag with env().
        $_ENV['NEW_DASHBOARD'] = $_SERVER['NEW_DASHBOARD'] = 'true';
        putenv('NEW_DASHBOARD=true');

        try {
            DB::table('companies')->whereIn('id', [$this->inBoth, $this->inFirstOnly])->update(['business_type' => 'Count B2B']);
            $manager = $this->actor('organizationmanager', ['organization_id' => $this->organization]);
            $this->actor('organizationmanager', ['organization_id' => $this->organization]);

            $response = $this->actingAs($manager)->get('/dashboard')->assertOk();

            $types = collect($response->viewData('project_types'))->pluck('count', 'label');
            $this->assertSame(2, (int) $types['Count B2B']);

            // An admin is not limited to organizations: the idea in the other organization counts too.
            DB::table('companies')->where('id', $this->inAnotherOrganization)->update(['business_type' => 'Count B2B']);
            $all = collect($this->actingAs($this->actor('admin'))->get('/dashboard')->assertOk()->viewData('project_types'))->pluck('count', 'label');
            $this->assertSame(3, (int) $all['Count B2B']);
        } finally {
            unset($_ENV['NEW_DASHBOARD'], $_SERVER['NEW_DASHBOARD']);
            putenv('NEW_DASHBOARD');
        }
    }
}
