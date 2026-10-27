<?php

namespace Tests\Feature\Correctness;

use App\Company;
use App\Finalscreening;
use App\Screening;
use App\ScreeningEvaluationCriteria;
use App\Services\Importer\ImporterSession;
use App\Services\Importer\ImporterSessionService;
use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\Support\BuildsScreeningFixtures;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * X4 (C20, C21 + saveForm, judging panels): multi-write operations must be all-or-nothing,
 * and the importer must claim a session atomically so two executes can't both import.
 */
class TransactionsTest extends TestCase
{
    use SecurityActors, BuildsScreeningFixtures {
        SecurityActors::actor insteadof BuildsScreeningFixtures;
        BuildsScreeningFixtures::makeUser as screeningUser;
    }

    /** SQL fragment that should blow up mid-operation; null = off. */
    private ?string $failOn = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();

        DB::connection()->beforeExecuting(function ($query) {
            if ($this->failOn !== null && str_contains((string) $query, $this->failOn)) {
                throw new RuntimeException('injected failure');
            }
        });
    }

    protected function tearDown(): void
    {
        $this->failOn = null;
        ScreeningEvaluationCriteria::flushEventListeners();
        Finalscreening::flushEventListeners();
        Screening::flushEventListeners();
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_evaluation_criteria_rebuild_is_all_or_nothing(): void
    {
        $admin = $this->actor('admin');
        $this->grant('admin', ['screening.evaluation_save_draft']);
        $step = $this->step($this->program($this->organization()), ['stype' => 2]);
        $evaluation = DB::table('screening_evaluation')->insertGetId(['step_id' => $step, 'status' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $old = DB::table('screening_evaluation_criteria')->insertGetId(['title' => 'Old', 'weight' => 100, 'evaluation_id' => $evaluation, 'created_at' => now(), 'updated_at' => now()]);

        // The new criterion is created first, then removing the old one fails.
        ScreeningEvaluationCriteria::deleting(fn () => throw new RuntimeException('injected failure'));

        $this->actingAs($admin)->postJson('/dashboard/screening/evaluation/save_draft', [
            'action' => 'draft', 'stepId' => $step,
            'criteria' => [['id' => null, 'name' => 'New', 'weight' => 1]],
        ]);

        $this->assertFalse(DB::table('screening_evaluation_criteria')->where('evaluation_id', $evaluation)->where('title', 'New')->exists());
        $this->assertTrue(DB::table('screening_evaluation_criteria')->where('id', $old)->exists());
    }

    public function test_importer_execute_claims_the_session_atomically(): void
    {
        Storage::fake('importer');
        Mail::fake();
        $admin = $this->actor('admin');
        $orgId = $this->organization();

        $uuid = $this->actingAs($admin)->postJson('/dashboard/importer/sessions')->json('data.session_id');
        $path = base_path('tests/Fixtures/Importer/valid_minimal.csv');
        $this->actingAs($admin)->post("/dashboard/importer/sessions/{$uuid}/file", ['file' => new UploadedFile($path, 'valid_minimal.csv', 'text/csv', null, true)], ['Accept' => 'application/json'])->assertOk();
        $maps = $this->actingAs($admin)->postJson("/dashboard/importer/sessions/{$uuid}/automap")->json('data.mappings');
        $this->actingAs($admin)->postJson("/dashboard/importer/sessions/{$uuid}/validate", ['mappings' => $maps]);
        $this->actingAs($admin)->postJson("/dashboard/importer/sessions/{$uuid}/conflicts", ['organization_id' => $orgId, 'accept' => true]);

        // This request read the session while it was still validated; a concurrent
        // execute has since claimed it.
        $stale = ImporterSession::where('uuid', $uuid)->firstOrFail();
        DB::table('importer_sessions')->where('uuid', $uuid)->update(['status' => ImporterSession::STATUS_IMPORTING]);
        $this->partialMock(ImporterSessionService::class, fn ($m) => $m->shouldReceive('findForUserByUuid')->andReturn($stale));

        $this->actingAs($admin)->postJson("/dashboard/importer/sessions/{$uuid}/execute", ['organization_id' => $orgId, 'accepted_warnings' => true])
            ->assertStatus(409);

        $this->assertFalse(DB::table('companies')->where('name_en', 'Acme Robotics')->where('created_at', '>=', now()->subMinute())->exists());
    }

    /** A form step with one text question. @return array{0:int,1:int} */
    private function formStep(int $cycleId): array
    {
        $formId = DB::table('builder_forms')->insertGetId(['form_name' => 'Audit Form', 'created_at' => now(), 'updated_at' => now()]);
        $stepId = $this->step($cycleId, ['stype' => 1, 'arr' => 1, 'data' => $formId, 'from' => now()->subDay(), 'to' => now()->addMonth()]);
        $pivotId = DB::table('form_fields')->insertGetId([
            'step_id' => $stepId, 'builder_forms_id' => $formId, 'field_id' => DB::table('fields')->value('id'),
            'options' => json_encode(['type' => 'text', 'label' => 'Question']), 'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$stepId, $pivotId];
    }

    public function test_application_save_is_all_or_nothing(): void
    {
        $cycle = $this->program($this->organization());
        [$step, $field] = $this->formStep($cycle);
        [$companyId, $founder] = $this->startup($cycle, ['cycle' => null, 'step' => null]);
        $this->grant('registered', ['cycles.application_save']);
        $this->failOn = 'insert into `form_data`';

        $this->actingAs($founder)->post('/dashboard/cycles/save', [
            'id' => $step, 'cycle' => $cycle, 'field_ids' => (string) $field, 'field_' . $field => 'answer',
        ]);

        $company = Company::find($companyId);
        $this->assertSame([null, null], [$company->cycle, $company->step], 'the startup was moved into the program without its answers');
    }

    public function test_a_failed_join_notification_does_not_fail_a_saved_application(): void
    {
        $cycle = $this->program($this->organization());
        [$step, $field] = $this->formStep($cycle);
        [$companyId, $founder] = $this->startup($cycle, ['cycle' => null, 'step' => null]);
        $this->grant('registered', ['cycles.application_save']);
        Notification::swap(new class implements Dispatcher {
            public function send($notifiables, $notification)
            {
                throw new RuntimeException('mail down');
            }

            public function sendNow($notifiables, $notification, ?array $channels = null)
            {
                throw new RuntimeException('mail down');
            }
        });

        $this->actingAs($founder)->post('/dashboard/cycles/save', [
            'id' => $step, 'cycle' => $cycle, 'field_ids' => (string) $field, 'field_' . $field => 'answer',
        ])->assertRedirect(url('/dashboard'));

        $this->assertTrue(DB::table('form_data')->where('company_id', $companyId)->where('answer', 'answer')->exists());
        $this->assertNull(User::find($founder->id)->program_join_notified_cycle, 'the join email slot is free for a retry');
    }

    private function screeningProgram(): array
    {
        $this->setPlatformZone('Africa/Cairo');
        Carbon::setTestNow(Carbon::parse('2026-10-01 09:00', 'Africa/Cairo'));

        return [$this->makeFinalScreeningProgram(), $this->screeningUser('admin', null), $this->screeningUser('judger', null), $this->screeningUser('judger', null)];
    }

    public function test_judging_panel_rebuild_is_all_or_nothing(): void
    {
        [$program, $admin, $oldJudge, $newJudge] = $this->screeningProgram();
        $this->makeFinalPanel($program['step'], $program['company'], 1, [$oldJudge->id], '2026-10-10', '10:00', '11:00', null);
        Finalscreening::creating(fn () => throw new RuntimeException('injected failure'));

        $this->actingAs($admin)->postJson('/dashboard/screening/add-judging-panels', [
            'step' => $program['step'],
            'panels' => [['judges' => [$newJudge->id], 'companies' => [$program['company']], 'session_date' => '2026-10-12', 'start_time' => '10:00', 'end_time' => '11:00']],
            'acknowledge_judge_conflicts' => true,
        ]);

        $this->assertSame([$oldJudge->id], DB::table('screenings')->where('step_id', $program['step'])->pluck('user_id')->map('intval')->all());
        $this->assertTrue(DB::table('finalscreenings')->where('step_id', $program['step'])->where('company_id', $program['company'])->whereNull('deleted_at')->exists());
    }

    public function test_judging_panel_delete_is_all_or_nothing(): void
    {
        [$program, $admin, $judgeA, $judgeB] = $this->screeningProgram();
        $this->makeFinalPanel($program['step'], $program['company'], 1, [$judgeA->id], '2026-10-10', '10:00', '11:00', null);
        $this->makeFinalPanel($program['step'], $program['company'], 2, [$judgeB->id], '2026-10-11', '10:00', '11:00', null);
        DB::table('steps')->where('id', $program['step'])->update(['list' => json_encode([1 => ['a'], 2 => ['b']])]);
        Screening::updating(fn () => throw new RuntimeException('injected failure'));

        $this->actingAs($admin)->postJson('/dashboard/screening/delete-judging-panel', ['step' => $program['step'], 'panel' => 1]);

        $this->assertSame([1, 2], DB::table('screenings')->where('step_id', $program['step'])->orderBy('panel')->pluck('panel')->map('intval')->all());
        $this->assertCount(2, json_decode(DB::table('steps')->where('id', $program['step'])->value('list'), true));
    }
}
