<?php

namespace Tests\Feature\Security\Audit;

use App\Company;
use App\CycleCustomPages;
use App\Settings;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * H1 (cited routes), H6, N7: routes that had no name skipped the permission middleware,
 * and their methods trusted the ids they were given. Each now has a name, a seeded
 * permission and a record-level guard.
 */
class UnnamedRouteGuardsTest extends TestCase
{
    use SecurityActors;

    private const ROUTE_NAMES = [
        'cycles.companies_change_step', 'cycles.application_save',
        'screening.panels_update', 'screening.panel_startups_assign', 'screening.panel_startups_options', 'screening.form_scores_save',
        'hackathon.winners_view', 'hackathon.winners_export', 'hackathon.winners_publish',
        'settings.about_us_edit', 'settings.about_us_update', 'settings.contact_us_edit', 'settings.contact_us_update',
        'cycle.custom_pages.add', 'cycle.custom_pages.show', 'cycle.custom_pages.update', 'cycle.custom_pages.reorder',
        'cycle.custom_pages.toggle_status', 'cycle.custom_pages.toggle_visibility', 'cycle.custom_pages.delete', 'cycle.custom_pages.upload_image',
        'my_files.view_private',
    ];

    /** @var string[] user file folders created by a test, removed in tearDown */
    private array $fileDirs = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();

        $staff = ['admin', 'organizationmanager', 'subadmin'];
        foreach ($staff as $role) {
            $this->grant($role, [
                'cycles.users_manage', 'cycles.companies_change_step', 'cycles.application_save',
                'screening.add_judging_panels', 'screening.manage', 'screening.panels_update', 'screening.panel_startups_assign',
                'screening.panel_startups_options', 'screening.form_scores_save', 'screening.view',
                'hackathon.viewAdminHackaton', 'hackathon.winners_view', 'hackathon.winners_export', 'hackathon.winners_publish',
                'programs.edit', 'cycle.custom_pages.add', 'cycle.custom_pages.upload_image',
            ]);
        }
        $this->grant('admin', ['settings.system_general_update', 'settings.about_us_update', 'settings.contact_us_update', 'my_files.view_file', 'my_files.view_private', 'my_files.list']);
        $this->grant('judger', ['screening.view', 'screening.form_scores_save', 'my_files.view_file', 'my_files.view_private', 'my_files.list']);
        $this->grant('registered', ['cycles.list_mine', 'cycles.application_save', 'my_files.view_file', 'my_files.view_private', 'my_files.list']);
        // Prove the record-level guard, not the permission middleware, stops a manager.
        $this->grant('organizationmanager', ['settings.about_us_update', 'settings.system_general_update']);
    }

    protected function tearDown(): void
    {
        foreach ($this->fileDirs as $dir) {
            File::deleteDirectory($dir);
        }
        parent::tearDown();
    }

    /** A form step with one text question. @return array{0:int,1:int} [stepId, formFieldPivotId] */
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

    // ---- change/cyc ------------------------------------------------------------------

    public function test_manager_cannot_move_another_orgs_startup_to_a_step(): void
    {
        $cycle = $this->program($this->organization());
        [$companyId] = $this->startup($cycle, ['step' => null]);
        $step = $this->step($cycle);

        $this->actingAs($this->manager($this->organization()))
            ->postJson('/dashboard/change/cyc', ['checkget' => $step, 'compid' => [$companyId]])
            ->assertForbidden();

        $this->assertNull(Company::find($companyId)->step);
    }

    public function test_manager_can_move_own_startup(): void
    {
        $org = $this->organization();
        $cycle = $this->program($org);
        [$companyId] = $this->startup($cycle);
        $step = $this->step($cycle);

        $this->actingAs($this->manager($org))->postJson('/dashboard/change/cyc', ['checkget' => $step, 'compid' => [$companyId]])->assertOk();

        $this->assertSame($step, (int) Company::find($companyId)->step);
    }

    // ---- cycles/save -------------------------------------------------------------------

    public function test_founder_cannot_jump_their_startup_to_a_later_step(): void
    {
        $cycle = $this->program($this->organization());
        [$first] = $this->formStep($cycle, 1);
        [$later, $laterField] = $this->formStep($cycle, 5);
        [$companyId, $founder] = $this->startup($cycle, ['step' => $first]);

        $response = $this->actingAs($founder)->post('/dashboard/cycles/save', [
            'id' => $later, 'cycle' => $cycle, 'field_ids' => (string) $laterField, 'field_' . $laterField => 'answer',
        ]);

        $this->assertSame($first, (int) Company::find($companyId)->step);
        $response->assertForbidden();
    }

    public function test_founder_cannot_move_their_startup_into_another_program(): void
    {
        $mine = $this->program($this->organization());
        [$myStep] = $this->formStep($mine, 1);
        $other = $this->program($this->organization());
        [$otherFirst, $otherField] = $this->formStep($other, 1);
        [$companyId, $founder] = $this->startup($mine, ['step' => $myStep]);

        $response = $this->actingAs($founder)->post('/dashboard/cycles/save', [
            'id' => $otherFirst, 'cycle' => $other, 'field_ids' => (string) $otherField, 'field_' . $otherField => 'answer',
        ]);

        $this->assertSame($mine, (int) Company::find($companyId)->cycle);
        $response->assertForbidden();
    }

    public function test_founder_can_submit_the_application_step_of_a_program(): void
    {
        $cycle = $this->program($this->organization());
        [$first, $field] = $this->formStep($cycle, 1);
        [$companyId, $founder] = $this->startup($cycle, ['cycle' => null, 'step' => null]);

        $this->actingAs($founder)->post('/dashboard/cycles/save', [
            'id' => $first, 'cycle' => $cycle, 'field_ids' => (string) $field, 'field_' . $field => 'answer',
        ])->assertRedirect();

        $company = Company::find($companyId);
        $this->assertSame([$cycle, $first], [(int) $company->cycle, (int) $company->step]);
    }

    // ---- screening/update, screening/add, screening/get, screening/save ------------------------

    private function screeningSetup(): array
    {
        $org = $this->organization();
        $cycle = $this->program($org);
        [$step, $field] = $this->formStep($cycle, 2);
        [$companyId, $founder] = $this->startup($cycle, ['step' => $step]);
        $judge = $this->actor('judger');
        $screeningId = DB::table('screenings')->insertGetId([
            'step_id' => $step, 'user_id' => $judge->id, 'panel' => 1, 'users' => json_encode([$companyId]), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $answerId = DB::table('form_data')->insertGetId([
            'field_id' => $field, 'company_id' => $companyId, 'user_id' => $founder->id, 'form_id' => $step, 'step_id' => $step,
            'question' => 'Question', 'answer' => 'answer', 'screening' => '{}', 'comments' => '{}', 'created_at' => now(), 'updated_at' => now(),
        ]);

        return compact('org', 'cycle', 'step', 'field', 'companyId', 'judge', 'screeningId', 'answerId');
    }

    public function test_manager_cannot_rebuild_another_orgs_judging_panels(): void
    {
        $s = $this->screeningSetup();

        $response = $this->actingAs($this->manager($this->organization()))
            ->post('/dashboard/screening/update', ['step' => $s['step'], 'fid' => $s['step'], 'judges' => [$s['judge']->id]]);

        $this->assertTrue(DB::table('screenings')->where('id', $s['screeningId'])->whereNull('deleted_at')->exists());
        $response->assertForbidden();
    }

    public function test_manager_cannot_reassign_another_orgs_panel_startups(): void
    {
        $s = $this->screeningSetup();

        $this->actingAs($this->manager($this->organization()))
            ->post('/dashboard/screening/add', ['sid' => $s['step'], 'pid' => 1, 'panelusers' => [999999]])
            ->assertForbidden();

        $this->assertSame(json_encode([$s['companyId']]), DB::table('screenings')->where('id', $s['screeningId'])->value('users'));
    }

    public function test_manager_cannot_list_another_orgs_panel_startups(): void
    {
        $s = $this->screeningSetup();

        $this->actingAs($this->manager($this->organization()))
            ->get("/dashboard/screening/get/{$s['step']}/{$s['step']}/1")
            ->assertForbidden();

        $this->actingAs($this->manager($s['org']))->get("/dashboard/screening/get/{$s['step']}/{$s['step']}/1")->assertOk();
    }

    public function test_unassigned_judge_cannot_score_a_startup(): void
    {
        $s = $this->screeningSetup();

        $response = $this->actingAs($this->actor('judger'))->post('/dashboard/screening/save', [
            'form_id' => $s['step'], 'company_id' => $s['companyId'], 'w' . $s['field'] => 9, 'c' . $s['field'] => 'x',
        ]);

        $this->assertSame('{}', DB::table('form_data')->where('id', $s['answerId'])->value('screening'));
        $response->assertForbidden();
    }

    public function test_assigned_judge_can_score_the_startup(): void
    {
        $s = $this->screeningSetup();

        $this->actingAs($s['judge'])->post('/dashboard/screening/save', [
            'form_id' => $s['step'], 'company_id' => $s['companyId'], 'w' . $s['field'] => 9, 'c' . $s['field'] => 'x',
        ])->assertRedirect();

        $this->assertStringContainsString('9', (string) DB::table('form_data')->where('id', $s['answerId'])->value('screening'));
    }

    // ---- hackathon winners -------------------------------------------------------------

    public function test_manager_cannot_view_or_publish_another_orgs_hackathon_winners(): void
    {
        $hackathon = $this->program($this->organization(), ['type' => 'Hackathon', 'start' => now()->subWeek(), 'end' => now()->addWeek()]);
        $outsider = $this->manager($this->organization());

        $this->actingAs($outsider)->get("/dashboard/hackathon/dashboard/winners/{$hackathon}")->assertForbidden();
        $this->actingAs($outsider)->post("/dashboard/hackathon/dashboard/winners/{$hackathon}", ['winners_announcement' => 1])->assertForbidden();
    }

    public function test_founder_cannot_export_hackathon_winners_of_their_program_org(): void
    {
        $org = $this->organization();
        $hackathon = $this->program($org, ['type' => 'Hackathon', 'start' => now()->subWeek(), 'end' => now()->addWeek()]);
        [, $founder] = $this->startup($hackathon);
        $this->grant('registered', ['hackathon.winners_export']);

        $this->actingAs($founder)->get("/dashboard/hackathon/dashboard/winners/{$hackathon}/export")->assertForbidden();
    }

    // ---- about us / contact us -----------------------------------------------------------

    public function test_only_admin_can_update_about_and_contact_pages(): void
    {
        $before = Settings::find(10)->title;
        // The edit form posts every column of the row.
        $form = Settings::find(10)->only(['ar_title', 'dsc', 'ar_dsc', 'set1', 'ar_set1', 'set2', 'ar_set2', 'url']);

        $response = $this->actingAs($this->manager($this->organization()))->post('/dashboard/updateaboutus', ['title' => 'Defaced'] + $form);
        $this->assertSame($before, Settings::find(10)->title);
        $response->assertForbidden();

        $this->actingAs($this->actor('admin'))->post('/dashboard/updateaboutus', ['title' => 'Admin edit'] + $form)->assertRedirect();
        $this->assertSame('Admin edit', Settings::find(10)->title);
    }

    // ---- N7 cycle custom pages -----------------------------------------------------------

    public function test_manager_cannot_add_custom_pages_to_another_orgs_program(): void
    {
        $cycle = $this->program($this->organization());

        $this->actingAs($this->manager($this->organization()))->postJson("/dashboard/cycles/{$cycle}/add_page", [
            'cycleId' => $cycle, 'title' => 'Injected', 'content' => '<p>x</p>', 'visibility' => 'public',
        ])->assertForbidden();

        $this->assertSame(0, CycleCustomPages::where('cycle_id', $cycle)->count());
    }

    public function test_manager_can_add_custom_pages_to_own_program(): void
    {
        $org = $this->organization();
        $cycle = $this->program($org);

        $this->actingAs($this->manager($org))->postJson("/dashboard/cycles/{$cycle}/add_page", [
            'cycleId' => $cycle, 'title' => 'Mine', 'content' => '<p>x</p>', 'visibility' => 'public',
        ])->assertOk()->assertJson(['status' => true]);
    }

    // ---- H6 user file folders -----------------------------------------------------------

    private function userFile(string $base, int $ownerId): string
    {
        // Remove the base folder too when this test is the one creating it.
        $baseDir = storage_path("app/{$base}");
        if (!File::isDirectory($baseDir)) {
            $this->fileDirs[] = $baseDir;
        }
        $dir = "{$baseDir}/{$ownerId}";
        File::ensureDirectoryExists($dir);
        array_unshift($this->fileDirs, $dir);
        File::put("{$dir}/audit.txt", 'secret');

        return 'audit.txt';
    }

    public function test_founder_cannot_open_another_users_files(): void
    {
        $cycle = $this->program($this->organization());
        [, $owner] = $this->startup($cycle);
        [, $other] = $this->startup($this->program($this->organization()));
        $name = $this->userFile('uploads/files', $owner->id);
        $private = $this->userFile('users', $owner->id);

        $this->actingAs($other)->get("/dashboard/uploads/files/{$owner->id}/{$name}")->assertForbidden();
        $this->actingAs($other)->get("/dashboard/private/{$owner->id}/{$private}")->assertForbidden();

        $this->actingAs($owner)->get("/dashboard/uploads/files/{$owner->id}/{$name}")->assertOk();
        $this->actingAs($owner)->get("/dashboard/private/{$owner->id}/{$private}")->assertOk();
    }

    public function test_assigned_judge_can_open_the_startup_founders_files(): void
    {
        $s = $this->screeningSetup();
        $founder = User::where('company_id', $s['companyId'])->firstOrFail();
        $name = $this->userFile('uploads/files', $founder->id);

        $this->actingAs($s['judge'])->get("/dashboard/uploads/files/{$founder->id}/{$name}")->assertOk();
    }

    public function test_routes_are_named(): void
    {
        foreach (self::ROUTE_NAMES as $name) {
            $this->assertTrue(Route::has($name), "route {$name} missing");
        }
    }

    public function test_every_new_route_name_is_seeded(): void
    {
        $seeded = collect(json_decode(file_get_contents(database_path('dbFiles/rolesWithDefaultPermissions.json')), true))
            ->flatMap(fn ($group) => array_column($group['permissions'], 'name'));

        foreach (self::ROUTE_NAMES as $name) {
            $this->assertTrue($seeded->contains($name), "{$name} not in rolesWithDefaultPermissions.json");
        }
    }
}
