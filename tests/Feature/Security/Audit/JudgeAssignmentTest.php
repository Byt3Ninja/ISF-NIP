<?php

namespace Tests\Feature\Security\Audit;

use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * H7: a judge could open any startup's application answers (screening.view) and write
 * evaluation scores and comments for startups they are not assigned to.
 * C23: a missing startup id crashed store_evaluation with a null dereference.
 */
class JudgeAssignmentTest extends TestCase
{
    use SecurityActors;

    private int $orgId;
    private int $companyId;
    private int $formStepId;
    private int $screeningStepId;
    private int $evaluationId;
    private int $criteriaId;
    private int $answerId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
        $this->grant('judger', ['screening.view', 'screening.evaluate_save_draft', 'screening.evaluate_publish']);
        $this->grant('organizationmanager', ['screening.view']);

        $this->orgId = $this->organization();
        $cycle = $this->program($this->orgId);
        $this->formStepId = $this->step($cycle, ['stype' => 1, 'arr' => 1]);
        $this->screeningStepId = $this->step($cycle, ['stype' => 2, 'arr' => 2]);
        [$this->companyId, $founder] = $this->startup($cycle, ['step' => $this->screeningStepId]);

        $this->answerId = DB::table('form_data')->insertGetId([
            'field_id' => $this->fieldId(), 'company_id' => $this->companyId, 'user_id' => $founder->id, 'form_id' => $this->formStepId,
            'step_id' => $this->formStepId, 'question' => 'Q', 'answer' => 'A', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->evaluationId = DB::table('screening_evaluation')->insertGetId(['step_id' => $this->screeningStepId, 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $category = DB::table('screening_evaluation_criteria')->insertGetId(['title' => 'Team', 'weight' => 1, 'evaluation_id' => $this->evaluationId, 'created_at' => now(), 'updated_at' => now()]);
        // Judges score sub-criteria (the canvas posts sub-criterion ids only).
        $this->criteriaId = DB::table('screening_evaluation_criteria')->insertGetId(['title' => 'Founders', 'weight' => 1, 'evaluation_id' => $this->evaluationId, 'parent_id' => $category, 'created_at' => now(), 'updated_at' => now()]);
    }

    /** form_data.field_id is a foreign key to form_fields; any existing field will do. */
    private function fieldId(): int
    {
        return (int) DB::table('form_fields')->value('id');
    }

    private function assignedJudge()
    {
        $judge = $this->actor('judger');
        DB::table('screenings')->insert([
            'step_id' => $this->screeningStepId, 'user_id' => $judge->id, 'panel' => 1,
            'users' => json_encode([(string) $this->companyId]), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return $judge;
    }

    private function evaluate($judge, array $extra = [])
    {
        return $this->actingAs($judge)->postJson('/dashboard/judger/screening/evaluation/save_draft', array_merge([
            'type' => 'draft', 'program' => $this->companyId, 'id' => $this->evaluationId,
            'final_comment' => 'note',
            'evaluation' => [['id' => $this->criteriaId, 'score' => 9, 'comment' => 'ok']],
            'comments' => [],
        ], $extra));
    }

    private function results(int $judgeId): int
    {
        return DB::table('screening_evaluation_results')->where('company_id', $this->companyId)->where('user_id', $judgeId)->count();
    }

    public function test_unassigned_judge_cannot_view_answers(): void
    {
        $this->actingAs($this->actor('judger'))->get("/dashboard/screening/{$this->formStepId}/{$this->companyId}")->assertForbidden();
    }

    public function test_manager_of_another_org_cannot_view_answers(): void
    {
        $this->actingAs($this->manager($this->organization()))->get("/dashboard/screening/{$this->formStepId}/{$this->companyId}")->assertForbidden();
    }

    public function test_assigned_judge_and_program_manager_can_view_answers(): void
    {
        $this->actingAs($this->assignedJudge())->get("/dashboard/screening/{$this->formStepId}/{$this->companyId}")->assertOk();
        $this->actingAs($this->manager($this->orgId))->get("/dashboard/screening/{$this->formStepId}/{$this->companyId}")->assertOk();
    }

    public function test_unassigned_judge_cannot_write_an_evaluation(): void
    {
        $judge = $this->actor('judger');

        $this->evaluate($judge)->assertForbidden();

        $this->assertSame(0, $this->results($judge->id));
    }

    public function test_assigned_judge_can_write_an_evaluation(): void
    {
        $judge = $this->assignedJudge();

        $this->evaluate($judge)->assertOk()->assertJson(['status' => true]);

        $this->assertSame(1, $this->results($judge->id));
    }

    public function test_assigned_judge_cannot_comment_on_another_startups_answers(): void
    {
        $judge = $this->assignedJudge();
        [$otherCompany, $otherFounder] = $this->startup($this->program($this->organization()));
        $foreignAnswer = DB::table('form_data')->insertGetId([
            'field_id' => $this->fieldId(), 'company_id' => $otherCompany, 'user_id' => $otherFounder->id, 'form_id' => $this->formStepId,
            'step_id' => $this->formStepId, 'question' => 'Q', 'answer' => 'A', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->evaluate($judge, ['comments' => [['id' => $foreignAnswer, 'comment' => 'injected'], ['id' => $this->answerId, 'comment' => 'fine']]])->assertOk();

        $this->assertSame(0, DB::table('form_data_judges_comments')->where('form_data_id', $foreignAnswer)->count());
        $this->assertSame(1, DB::table('form_data_judges_comments')->where('form_data_id', $this->answerId)->where('user_id', $judge->id)->count());
    }

    public function test_missing_startup_is_a_404_not_a_crash(): void
    {
        $this->evaluate($this->actor('judger'), ['program' => 999999999])->assertNotFound();
    }
}
