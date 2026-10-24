<?php

namespace Tests\Feature\Correctness;

use App\ScreeningEvaluationResults;
use App\Services\ProgramService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * C28 wrong sub-criteria weight message, C37 null criteria in the screening report,
 * C22 N+1 in ProgramService::getProgramMentors, C34 WhatsAppService::sendMessage return type.
 */
class SmallFixesTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
    }

    public function test_publish_reports_sub_criteria_over_100_percent_as_exceeding(): void
    {
        $admin = $this->actor('admin');
        $this->grant('admin', ['screening.evaluation_publish']);
        $stepId = $this->step($this->program($this->organization()), ['stype' => 2]);

        $response = $this->actingAs($admin)->postJson('/dashboard/screening/evaluation/publish', [
            'action' => 'publish',
            'stepId' => $stepId,
            'criteria' => [[
                'name' => 'Team', 'weight' => 1,
                'subCriteria' => [['name' => 'Founders', 'weight' => 0.6], ['name' => 'Hiring', 'weight' => 0.6]],
            ]],
        ]);

        $response->assertOk()->assertJson(['status' => false]);
        $this->assertSame(__('admin.category_weight_not_exceed_100', ['category' => 'Team']), $response->json('message'));
    }

    /** @return array{step:int, company:int, evaluation:int, category:int, sub:int, judge:\App\User} */
    private function screeningWithAssignedJudge(): array
    {
        $cycle = $this->program($this->organization());
        $step = $this->step($cycle, ['stype' => 2, 'arr' => 2]);
        [$company] = $this->startup($cycle, ['step' => $step]);
        $evaluation = DB::table('screening_evaluation')->insertGetId(['step_id' => $step, 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $category = DB::table('screening_evaluation_criteria')->insertGetId(['title' => 'Team', 'weight' => 1, 'evaluation_id' => $evaluation, 'created_at' => now(), 'updated_at' => now()]);
        $sub = DB::table('screening_evaluation_criteria')->insertGetId(['title' => 'Founders', 'weight' => 1, 'evaluation_id' => $evaluation, 'parent_id' => $category, 'created_at' => now(), 'updated_at' => now()]);
        $judge = $this->actor('judger');
        DB::table('screenings')->insert(['step_id' => $step, 'user_id' => $judge->id, 'panel' => 1, 'users' => json_encode([(string) $company]), 'created_at' => now(), 'updated_at' => now()]);

        return compact('step', 'company', 'evaluation', 'category', 'sub', 'judge');
    }

    public function test_judge_cannot_store_a_score_against_a_non_sub_criterion(): void
    {
        $s = $this->screeningWithAssignedJudge();
        $this->grant('judger', ['screening.evaluate_save_draft']);

        $this->actingAs($s['judge'])->postJson('/dashboard/judger/screening/evaluation/save_draft', [
            'type' => 'draft', 'program' => $s['company'], 'id' => $s['evaluation'], 'final_comment' => 'note', 'comments' => [],
            'evaluation' => [
                ['id' => $s['category'], 'score' => 9, 'comment' => 'top-level'],
                ['id' => null, 'score' => 9, 'comment' => 'no criterion'],
                ['id' => $s['sub'], 'score' => 7, 'comment' => 'ok'],
            ],
        ])->assertOk();

        $stored = DB::table('screening_evaluation_results')->where('user_id', $s['judge']->id)->pluck('criteria_id')->all();
        $this->assertSame([$s['sub']], array_map('intval', $stored));
    }

    public function test_report_tolerates_results_without_a_sub_criterion(): void
    {
        $s = $this->screeningWithAssignedJudge();
        foreach ([$s['category'], null] as $criteriaId) {
            DB::table('screening_evaluation_results')->insert([
                'evaluation_id' => $s['evaluation'], 'criteria_id' => $criteriaId, 'user_id' => $s['judge']->id,
                'company_id' => $s['company'], 'score' => 8, 'status' => 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $metadata = ScreeningEvaluationResults::metadata($s['step']);

        $this->assertNotNull($metadata);
    }

    private function mentorQueryCount(int $mentors): int
    {
        $admin = $this->actor('admin');
        $this->actingAs($admin);
        $cycle = $this->program($this->organization());
        for ($i = 0; $i < $mentors; $i++) {
            DB::table('cycles_mentors')->insert(['cycle_id' => $cycle, 'user_id' => $this->actor('mentor')->id, 'created_at' => now(), 'updated_at' => now()]);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();
        $result = app(ProgramService::class)->getProgramMentors($cycle);
        $count = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertCount($mentors, $result->items());
        foreach ($result->items() as $item) {
            $this->assertSame(1, $item['programCount']);
            $this->assertSame('mentor', $item['role']);
        }

        return $count;
    }

    public function test_program_mentors_query_count_does_not_grow_per_mentor(): void
    {
        $one = $this->mentorQueryCount(1);
        $five = $this->mentorQueryCount(5);

        $this->assertSame($one, $five, "queries: 1 mentor = {$one}, 5 mentors = {$five}");
    }

    public function test_whatsapp_send_message_returns_an_array_like_send_otp(): void
    {
        DB::table('settings')->where('id', 1)->update(['whatsApp_Status_Session' => 0]);

        $result = (new WhatsAppService())->sendMessage('201000000000', 'hello');

        $this->assertIsArray($result);
        $this->assertFalse($result['success']);
    }
}
