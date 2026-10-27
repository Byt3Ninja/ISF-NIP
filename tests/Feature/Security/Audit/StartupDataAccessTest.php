<?php

namespace Tests\Feature\Security\Audit;

use App\User;
use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * H3 + N8/N9/N13: startup profile data (profile page, team, statistics, alumni snapshot,
 * financial projection, funding rounds, mentorship tasks, SWOT) was reachable by id for
 * users unrelated to the startup.
 */
class StartupDataAccessTest extends TestCase
{
    use SecurityActors;

    private int $orgId;
    private int $cycleId;
    private int $companyId;
    private User $founder;
    private int $otherCompanyId;
    private User $otherFounder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();

        $this->orgId = $this->organization();
        $this->cycleId = $this->program($this->orgId);
        $this->step($this->cycleId);
        [$this->companyId, $this->founder] = $this->startup($this->cycleId);
        [$this->otherCompanyId, $this->otherFounder] = $this->startup($this->program($this->organization()));
    }

    // ------------------------------------------------------------------ profile page (show)

    public function test_unrelated_users_cannot_open_a_startup_profile(): void
    {
        $this->grant('registered', ['company.profile_view']);
        $this->grant('mentor', ['company.profile_view']);
        $this->grant('judger', ['company.profile_view']);
        $this->grant('serviceProvider', ['company.profile_view']);

        foreach ([$this->otherFounder, $this->actor('mentor'), $this->actor('judger'), $this->actor('serviceProvider'), $this->manager($this->organization())] as $outsider) {
            $this->grant($outsider->roles()->value('default_for'), ['company.profile_view']);
            $this->actingAs($outsider)->get('/dashboard/companyProfile/' . $this->companyId)->assertForbidden();
        }
    }

    public function test_related_users_can_still_open_the_startup_profile(): void
    {
        $mentorOfProgram = $this->actor('mentor');
        DB::table('cycles_mentors')->insert(['cycle_id' => $this->cycleId, 'user_id' => $mentorOfProgram->id, 'created_at' => now(), 'updated_at' => now()]);

        $requestedMentor = $this->actor('mentor');
        DB::table('mentorship_requests')->insert(['mentor_id' => $requestedMentor->id, 'user_id' => $this->founder->id, 'cycle_id' => $this->cycleId, 'created_at' => now(), 'updated_at' => now()]);

        $judge = $this->actor('judger');
        DB::table('screenings')->insert(['step_id' => DB::table('steps')->where('cycle_id', $this->cycleId)->value('id'), 'user_id' => $judge->id, 'users' => json_encode([$this->companyId]), 'created_at' => now(), 'updated_at' => now()]);

        $provider = $this->actor('serviceProvider');
        $providerId = DB::table('service_providers')->insertGetId(['user_id' => $provider->id, 'company_name' => 'Audit SP', 'created_at' => now(), 'updated_at' => now()]);
        $serviceId = DB::table('services')->insertGetId(['service_provider_id' => $providerId, 'service_category_id' => DB::table('service_categories')->value('id'), 'name_en' => 'Audit Service', 'name_ar' => 'Audit Service', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('services_requests')->insert(['service_id' => $serviceId, 'user_id' => $this->founder->id, 'created_at' => now(), 'updated_at' => now()]);

        foreach (['registered', 'mentor', 'judger', 'serviceProvider', 'organizationmanager'] as $role) {
            $this->grant($role, ['company.profile_view']);
        }

        foreach ([$this->founder, $this->manager($this->orgId), $mentorOfProgram, $requestedMentor, $judge, $provider] as $viewer) {
            $status = $this->actingAs($viewer)->get('/dashboard/companyProfile/' . $this->companyId)->status();
            $this->assertSame(200, $status, 'viewer ' . $viewer->roles()->value('default_for') . " got {$status}");
        }
    }

    // ------------------------------------------------------------------ edit page (editOrCreate)

    public function test_manager_without_programs_cannot_open_another_startups_edit_page(): void
    {
        $this->grant('organizationmanager', ['company.profile_view_mine']);

        $this->actingAs($this->manager($this->organization()))
            ->get('/dashboard/myCompany/' . $this->companyId)
            ->assertForbidden();
    }

    // ------------------------------------------------------------------ team members

    public function test_founder_cannot_add_team_members_to_another_startup(): void
    {
        $this->grant('registered', ['company.profile_teams_add']);

        $this->actingAs($this->otherFounder)->post('/dashboard/AddCompanyTeams/' . $this->companyId, [
            'TeamsNo' => 'Investors', 'investor_name' => 'Injected', 'investment_type' => 'Angel',
        ])->assertForbidden();

        $this->assertFalse(DB::table('team_members')->where('Company_id', $this->companyId)->where('Name', 'Injected')->exists());
    }

    public function test_founder_cannot_edit_another_startups_investor(): void
    {
        $this->grant('registered', ['company.profile_teams_edit']);
        $memberId = DB::table('team_members')->insertGetId(['Company_id' => $this->companyId, 'TeamMember' => 'Investor', 'Name' => 'Original', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($this->otherFounder)->post('/dashboard/AddCompanyEdit', [
            'id' => $memberId, 'investor_name' => 'Hijacked', 'investment_type' => 'Angel',
        ]);

        $this->assertSame('Original', DB::table('team_members')->where('id', $memberId)->value('Name'));
    }

    public function test_founder_cannot_delete_another_startups_team_member(): void
    {
        $this->grant('registered', ['company.profile_teams_delete']);
        $memberId = DB::table('team_members')->insertGetId(['Company_id' => $this->companyId, 'TeamMember' => 'Investor', 'Name' => 'Keep', 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($this->otherFounder)->post('/dashboard/AddCompanyDelete/' . $this->companyId, ['id' => $memberId]);

        $this->assertTrue(DB::table('team_members')->where('id', $memberId)->exists());
    }

    public function test_founder_can_still_manage_own_team(): void
    {
        $this->grant('registered', ['company.profile_teams_add']);

        $this->actingAs($this->founder)->post('/dashboard/AddCompanyTeams', [
            'TeamsNo' => 'Investors', 'investor_name' => 'Legit Investor', 'investment_type' => 'Angel',
        ]);

        $this->assertTrue(DB::table('team_members')->where('Company_id', $this->companyId)->where('Name', 'Legit Investor')->exists());
    }

    // ------------------------------------------------------------------ statistics

    public function test_founder_cannot_save_another_startups_statistics(): void
    {
        $this->grant('registered', ['company.employees_statistics']);

        $this->actingAs($this->otherFounder)->postJson('/dashboard/save-statistics/' . $this->companyId, ['total_employees' => 999])->assertForbidden();

        $this->assertNotEquals(999, (int) DB::table('companies')->where('id', $this->companyId)->value('total_employees'));
    }

    // ------------------------------------------------------------------ N8 alumni snapshot

    public function test_unrelated_founder_cannot_open_an_alumni_snapshot(): void
    {
        $this->grant('registered', ['company.alumni_view']);
        DB::table('alumni')->insert(['user_id' => $this->founder->id, 'cycle_id' => $this->cycleId, 'company_id' => $this->companyId, 'created_at' => now(), 'updated_at' => now()]);

        $this->actingAs($this->otherFounder)->get("/dashboard/companyProfile/{$this->companyId}/alumni/{$this->cycleId}")->assertForbidden();
    }

    // ------------------------------------------------------------------ N9 add to alumni

    public function test_manager_of_another_org_cannot_mark_a_startup_alumni(): void
    {
        $this->grant('organizationmanager', ['company.addCompanyAlumni', 'company.addCompanyAlumniBulk']);
        $outsider = $this->manager($this->organization());

        $this->actingAs($outsider)->postJson('/dashboard/AddCompanyalumni/' . $this->companyId)->assertForbidden();
        $this->actingAs($outsider)->postJson('/dashboard/AddCompanyalumniBulk', ['company_ids' => [$this->companyId]]);

        $this->assertFalse(DB::table('alumni')->where('company_id', $this->companyId)->exists());
    }

    // ------------------------------------------------------------------ N13 financial projection

    public function test_financial_projection_is_deny_by_default_for_unrelated_roles(): void
    {
        // A role outside founder/staff that holds the permission (e.g. after a role edit) must not pass.
        $this->grant('judger', ['company.financial_projection.realtime', 'company.financial_projection.save']);

        $this->actingAs($this->actor('judger'))->getJson("/dashboard/company/{$this->companyId}/financial-projection/realtime")->assertForbidden();
        $this->actingAs($this->actor('judger'))->postJson("/dashboard/company/{$this->companyId}/financial-projection/save", [])->assertForbidden();
    }

    // ------------------------------------------------------------------ funding rounds

    public function test_founder_cannot_read_or_delete_another_startups_funding_round(): void
    {
        $this->grant('registered', ['startup.fundingRounds.show', 'startup.fundingRounds.destroy']);
        $roundId = DB::table('funding_rounds')->insertGetId([
            'company_id' => $this->companyId, 'funding_stage' => 'seed', 'amount' => 1, 'post_money_valuation' => 1,
            'date_raised' => now()->toDateString(), 'investment_vehicle' => 'equity', 'amount_raised' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($this->otherFounder)->getJson('/dashboard/startup/fundingRounds/' . $roundId)->assertNotFound();
        $this->actingAs($this->otherFounder)->deleteJson('/dashboard/startup/fundingRounds/' . $roundId)->assertNotFound();

        $this->assertTrue(DB::table('funding_rounds')->where('id', $roundId)->exists());

        // The startup's own founder still reads it.
        $this->actingAs($this->founder)->getJson('/dashboard/startup/fundingRounds/' . $roundId)->assertOk();
    }

    // ------------------------------------------------------------------ mentorship tasks

    public function test_founder_cannot_see_or_complete_another_startups_mentorship_task(): void
    {
        $mentor = $this->actor('mentor');
        $requestId = DB::table('mentorship_requests')->insertGetId(['mentor_id' => $mentor->id, 'user_id' => $this->founder->id, 'cycle_id' => $this->cycleId, 'created_at' => now(), 'updated_at' => now()]);
        $taskId = DB::table('mentorship_tasks')->insertGetId(['mentorship_request_id' => $requestId, 'mentor_id' => $mentor->id, 'company_id' => $this->companyId, 'user_id' => $this->founder->id, 'title' => 'Task', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
        $this->grant('registered', ['startup.startup.mentorshipTasks.index', 'startup.startup.mentorshipTasks.show', 'startup.mentorship_tasks.complete_mine', 'startup.mentorship_tasks.comment_mine']);

        $this->actingAs($this->otherFounder)->get('/dashboard/startup/mentorshipTasks/' . $requestId)->assertForbidden();
        $this->actingAs($this->otherFounder)->get('/dashboard/startup/mentorshipTasks/show/' . $taskId)->assertForbidden();
        $this->actingAs($this->otherFounder)->post('/dashboard/startup/mentorshipTasks/complete/' . $taskId)->assertForbidden();
        $this->actingAs($this->otherFounder)->post('/dashboard/startup/mentorshipTasks/send/comment', ['mentorship_task_id' => $taskId, 'comment' => 'x'])->assertForbidden();

        $this->assertSame('pending', DB::table('mentorship_tasks')->where('id', $taskId)->value('status'));

        // The startup's own founder can still complete it.
        $this->actingAs($this->founder)->post('/dashboard/startup/mentorshipTasks/complete/' . $taskId)->assertRedirect();
        $this->assertSame('completed', DB::table('mentorship_tasks')->where('id', $taskId)->value('status'));
    }

    // ------------------------------------------------------------------ SWOT + likeService

    public function test_founder_cannot_overwrite_another_startups_swot(): void
    {
        $this->grant('registered', ['swot.save']);

        $this->actingAs($this->otherFounder)->post('/dashboard/swot-analysis/save', [
            'company_id' => $this->companyId, 'strength' => 'Injected', 'weakness' => '', 'opportunity' => '', 'threat' => '',
            'srecommendation' => '', 'wrecommendation' => '', 'orecommendation' => '', 'trecommendation' => '',
        ])->assertForbidden();

        $this->assertFalse(DB::table('swot_analyses')->where('company_id', $this->companyId)->where('strength', 'Injected')->exists());
    }

    public function test_like_service_rejects_a_foreign_company(): void
    {
        $this->grant('organizationmanager', ['services.like']);

        $this->actingAs($this->manager($this->organization()))
            ->postJson('/dashboard/likeService', ['company_id' => $this->companyId, 'service_id' => 1, 'current_state' => 'unliked'])
            ->assertForbidden();
    }
}
