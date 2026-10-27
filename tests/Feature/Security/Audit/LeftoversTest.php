<?php

namespace Tests\Feature\Security\Audit;

use App\Notifications\WelcomeMentorNotification;
use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * Audit-adjacent findings from the remediation groups:
 * (a) WelcomeMentorNotification serialized the mentor's plaintext password into the queue;
 * (b) evaluation criteria save/publish and judging-panel delete had no organization check.
 */
class LeftoversTest extends TestCase
{
    use SecurityActors;

    private const PERMISSIONS = ['screening.evaluation_save_draft', 'screening.evaluation_publish', 'screening.delete_judging_panel'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
        $this->grant('organizationmanager', self::PERMISSIONS);
    }

    public function test_queued_mentor_welcome_does_not_carry_the_password(): void
    {
        $mentor = $this->actor('mentor');

        $this->assertStringNotContainsString('Pl41n-Secret-x', serialize(new WelcomeMentorNotification($mentor, 'Pl41n-Secret-x')));
    }

    /** @return array{0:int,1:int} org id, screening step id */
    private function screeningStep(): array
    {
        $org = $this->organization();

        return [$org, $this->step($this->program($org), ['stype' => 2])];
    }

    public function test_manager_of_another_org_cannot_save_or_publish_evaluation_criteria(): void
    {
        [, $foreignStep] = $this->screeningStep();
        $outsider = $this->manager($this->organization());

        $this->actingAs($outsider)->postJson('/dashboard/screening/evaluation/save_draft', ['action' => 'draft', 'stepId' => $foreignStep])->assertForbidden();
        $this->actingAs($outsider)->postJson('/dashboard/screening/evaluation/publish', [
            'action' => 'publish', 'stepId' => $foreignStep,
            'criteria' => [['name' => 'Team', 'weight' => 1, 'subCriteria' => [['name' => 'Founders', 'weight' => 1]]]],
        ])->assertForbidden();

        $this->assertFalse(DB::table('screening_evaluation')->where('step_id', $foreignStep)->exists());
    }

    public function test_manager_can_save_own_programs_evaluation_draft(): void
    {
        [$org, $step] = $this->screeningStep();

        $this->actingAs($this->manager($org))->postJson('/dashboard/screening/evaluation/save_draft', ['action' => 'draft', 'stepId' => $step])->assertOk();

        $this->assertTrue(DB::table('screening_evaluation')->where('step_id', $step)->exists());
    }

    public function test_manager_of_another_org_cannot_delete_a_judging_panel(): void
    {
        [, $foreignStep] = $this->screeningStep();
        // A real panel is an entry in steps.list plus its screenings rows.
        DB::table('steps')->where('id', $foreignStep)->update(['list' => json_encode([1 => ['date' => now()->addWeek()->format('Y-m-d')]])]);
        $panel = DB::table('screenings')->insertGetId(['step_id' => $foreignStep, 'user_id' => $this->actor('judger')->id, 'panel' => 1, 'users' => '[]', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($this->manager($this->organization()))
            ->postJson('/dashboard/screening/delete-judging-panel', ['step' => $foreignStep, 'panel' => 1])
            ->assertForbidden();

        $this->assertTrue(DB::table('screenings')->where('id', $panel)->exists());
        $this->assertNotNull(DB::table('steps')->where('id', $foreignStep)->value('list'));
    }
}
