<?php

namespace Tests\Feature\Pipeline;

use App\Company;
use App\CompanyEnrollment;
use App\Cycle;
use App\Step;
use Tests\Support\FakesOutboundServices;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * P1-02, staff lists: the registered-ideas list of a sub-program is built from
 * enrollments, so an idea that is active in two sub-programs appears in both, each
 * with the phase it has reached there. The cycle / step pointer can only name one.
 */
class RegisteredIdeasListTest extends TestCase
{
    use SecurityActors;
    use FakesOutboundServices;

    private Cycle $first;
    private Cycle $second;
    private Step $firstStep;
    private Step $secondStep;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeOutboundServices();

        $this->first = Cycle::factory()->create();
        $this->second = Cycle::factory()->create();
        $this->firstStep = Step::factory()->create(['cycle_id' => $this->first->id, 'title' => 'Alpha Phase', 'stype' => 1]);
        $this->secondStep = Step::factory()->create(['cycle_id' => $this->second->id, 'title' => 'Beta Phase', 'stype' => 1]);

        // Active in both: the pointer names the second, the history holds both.
        $this->company = Company::factory()->create(['name_en' => 'Two Program Idea', 'name_ar' => 'Two Program Idea', 'cycle' => $this->first->id, 'step' => $this->firstStep->id]);
        Company::withoutEvents(fn () => $this->company->update(['cycle' => $this->second->id, 'step' => $this->secondStep->id]));
        $enrollment = new CompanyEnrollment(['step_id' => $this->secondStep->id, 'status' => CompanyEnrollment::STATUS_ACTIVE, 'joined_at' => now()]);
        $enrollment->company_id = $this->company->id;
        $enrollment->cycle_id = $this->second->id;
        $enrollment->save();
    }

    public function test_scopes_find_a_company_in_every_cycle_it_is_active_in(): void
    {
        $this->assertTrue(Company::enrolledIn($this->first->id)->whereKey($this->company->id)->exists());
        $this->assertTrue(Company::enrolledIn($this->second->id)->whereKey($this->company->id)->exists());
        $this->assertTrue(Company::enrolledOnStep($this->firstStep->id)->whereKey($this->company->id)->exists());
        $this->assertSame($this->firstStep->id, $this->company->stepIdIn($this->first->id));
        $this->assertSame($this->secondStep->id, $this->company->stepIdIn($this->second->id));
    }

    public function test_scopes_ignore_enrollments_that_have_ended(): void
    {
        CompanyEnrollment::closeActive($this->company->id, $this->first->id, CompanyEnrollment::STATUS_REJECTED);

        $this->assertFalse(Company::enrolledIn($this->first->id)->whereKey($this->company->id)->exists());
        $this->assertNull($this->company->fresh()->stepIdIn($this->first->id));
    }

    public function test_the_list_of_each_sub_program_shows_the_idea_with_its_phase_there(): void
    {
        $admin = $this->actor('admin');

        $this->actingAs($admin)->get(route('cycles.users_manage', $this->first->id))
            ->assertOk()->assertSee('Two Program Idea')->assertSee('Alpha Phase')->assertDontSee('Beta Phase');

        $this->actingAs($admin)->get(route('cycles.users_manage', $this->second->id))
            ->assertOk()->assertSee('Two Program Idea')->assertSee('Beta Phase');
    }

    public function test_the_phase_filter_uses_the_phase_in_that_sub_program(): void
    {
        $admin = $this->actor('admin');

        $this->actingAs($admin)->get(route('cycles.users_manage', $this->first->id) . '?step_id=' . $this->firstStep->id)
            ->assertOk()->assertSee('Two Program Idea');
    }

    public function test_the_export_lists_the_idea_with_its_phase_in_that_sub_program(): void
    {
        $response = $this->actingAs($this->actor('admin'))->get('/dashboard/export-cyclesComp?cycle=' . $this->first->id);
        $response->assertOk();

        $rows = \PhpOffice\PhpSpreadsheet\IOFactory::load($response->baseResponse->getFile()->getPathname())
            ->getActiveSheet()->toArray();

        $this->assertCount(2, $rows, 'a heading row and the one company');
        $this->assertSame($this->company->id, (int) $rows[1][0]);
        $this->assertSame('Alpha Phase', $rows[1][6]);
    }
}
