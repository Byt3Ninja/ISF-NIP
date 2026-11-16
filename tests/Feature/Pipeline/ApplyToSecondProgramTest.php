<?php

namespace Tests\Feature\Pipeline;

use App\Company;
use App\CompanyEnrollment;
use App\Cycle;
use Illuminate\Support\Facades\DB;
use Tests\Support\FakesOutboundServices;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * P1-03: a founder whose idea is active in one sub-program can apply to another.
 * The first stays active, and the founder lands in the one just applied to.
 */
class ApplyToSecondProgramTest extends TestCase
{
    use SecurityActors;
    use FakesOutboundServices;

    private int $mine;
    private int $myStep;
    private int $other;
    private int $otherFirst;
    private int $otherField;
    private int $companyId;
    private \App\User $founder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeOutboundServices();

        $open = ['start' => now()->subDays(5)->toDateString(), 'end' => now()->addDays(30)->toDateString(), 'type' => 'General_Program'];
        $this->mine = $this->program($this->organization(), $open + ['title' => 'Mine Program']);
        [$this->myStep] = $this->formStep($this->mine, 1);
        $this->other = $this->program($this->organization(), $open + ['title' => 'Other Open Program']);
        [$this->otherFirst, $this->otherField] = $this->formStep($this->other, 1);

        [$this->companyId, $this->founder] = $this->startup($this->mine, ['step' => $this->myStep]);
    }

    /** A form step with one text question. @return array{0:int,1:int} */
    private function formStep(int $cycleId, int $arr): array
    {
        $formId = DB::table('builder_forms')->insertGetId(['form_name' => 'Audit Form', 'created_at' => now(), 'updated_at' => now()]);
        $stepId = $this->step($cycleId, ['stype' => 1, 'arr' => $arr, 'data' => $formId, 'from' => now()->subDay(), 'to' => now()->addMonth()]);
        $pivotId = DB::table('form_fields')->insertGetId([
            'step_id' => $stepId, 'builder_forms_id' => $formId, 'field_id' => DB::table('fields')->value('id'),
            'options' => json_encode(['type' => 'text', 'label' => 'Question']), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$stepId, $pivotId];
    }

    private function apply()
    {
        return $this->actingAs($this->founder)->post('/dashboard/cycles/save', [
            'id' => $this->otherFirst, 'cycle' => $this->other,
            'field_ids' => (string) $this->otherField, 'field_' . $this->otherField => 'answer',
        ]);
    }

    public function test_the_application_form_of_another_program_opens_for_an_enrolled_founder(): void
    {
        $this->actingAs($this->founder)
            ->get('/dashboard/cycles/view/' . $this->other)
            ->assertOk()
            ->assertViewIs('admin.settings.cycles.form');
    }

    public function test_applying_keeps_the_first_program_active_and_selects_the_new_one(): void
    {
        $this->apply()->assertRedirect();

        $company = Company::findOrFail($this->companyId);
        $this->assertSame([$this->other, $this->otherFirst], [(int) $company->cycle, (int) $company->step]);

        $rows = CompanyEnrollment::where('company_id', $this->companyId)->orderBy('id')->get();
        $this->assertSame([$this->mine, $this->other], $rows->pluck('cycle_id')->all());
        $this->assertSame(['active', 'active'], $rows->pluck('status')->all());
        $this->assertSame($this->myStep, $rows[0]->step_id);
    }

    public function test_a_founder_still_cannot_jump_to_a_later_step_of_another_program(): void
    {
        [$later, $laterField] = $this->formStep($this->other, 5);

        $this->actingAs($this->founder)->post('/dashboard/cycles/save', [
            'id' => $later, 'cycle' => $this->other, 'field_ids' => (string) $laterField, 'field_' . $laterField => 'answer',
        ])->assertForbidden();

        $this->assertSame($this->mine, (int) Company::find($this->companyId)->cycle);
        $this->assertSame(1, CompanyEnrollment::where('company_id', $this->companyId)->count());
    }

    public function test_after_applying_the_founder_can_submit_the_step_they_are_on_in_the_first_program(): void
    {
        // A later form step in the first program, which staff move the idea to.
        [$nextStep, $nextField] = $this->formStep($this->mine, 2);
        $this->apply();
        Company::findOrFail($this->companyId)->moveToStepIn($nextStep);
        // A new request: the founder's company is read again, not reused from the one above.
        $this->founder = $this->founder->fresh();

        $this->actingAs($this->founder)->post('/dashboard/cycles/save', [
            'id' => $nextStep, 'cycle' => $this->mine, 'field_ids' => (string) $nextField, 'field_' . $nextField => 'answer',
        ])->assertRedirect();

        $company = Company::findOrFail($this->companyId);
        $this->assertSame([$this->mine, $nextStep], [(int) $company->cycle, (int) $company->step]);
        $this->assertSame(2, $company->activeEnrollments()->count());
    }

    public function test_open_programs_the_idea_is_not_in_are_offered(): void
    {
        $company = Company::findOrFail($this->companyId);
        $closed = $this->program($this->organization(), ['start' => now()->subDays(30)->toDateString(), 'end' => now()->subDays(2)->toDateString(), 'type' => 'General_Program']);

        $offered = Cycle::joinableBy($company)->pluck('id')->all();

        $this->assertContains($this->other, $offered);
        $this->assertNotContains($this->mine, $offered, 'already active there');
        $this->assertNotContains($closed, $offered, 'closed');

        $html = view('partials.other-programs', ['company' => $company])->render();
        $this->assertStringContainsString('Other Open Program', $html);
        $this->assertStringContainsString('/dashboard/cycles/view/' . $this->other, $html);
        $this->assertStringNotContainsString('Mine Program', $html);
    }

    public function test_the_founder_dashboard_offers_the_other_open_program(): void
    {
        $this->actingAs($this->founder)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Apply to another program')
            ->assertSee('Other Open Program');
    }
}
