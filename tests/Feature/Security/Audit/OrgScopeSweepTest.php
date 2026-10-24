<?php

namespace Tests\Feature\Security\Audit;

use App\Services\ProgramService;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * ownsOrganization() sweep: a founder "owns" their own program's organization (and any
 * user with users.organization_id owns that org), so it is not a staff check. Staff-only
 * guards must use managesOrganization(); recipients must follow MessagingRecipients.
 */
class OrgScopeSweepTest extends TestCase
{
    use SecurityActors;

    private int $org;
    private int $cycle;
    private int $companyId;
    private User $founder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();

        $this->org = $this->organization();
        $this->cycle = $this->program($this->org);
        [$this->companyId, $this->founder] = $this->startup($this->cycle, ['approved' => 1]);
    }

    private function messagesTo(User $receiver): int
    {
        return DB::table('messenger')->where('sender', $this->founder->id)->where('receiver', $receiver->id)->count();
    }

    private function apiAs(User $user)
    {
        return $this->withHeader('Authorization', 'Bearer ' . $user->createToken('audit')->plainTextToken);
    }

    // 1. Form builder listing (unnamed route, ownsOrganization guard)

    public function test_founder_cannot_list_their_programs_organization_forms(): void
    {
        $this->actingAs($this->founder)->getJson('/dashboard/builder/organization/' . $this->org)->assertForbidden();
    }

    public function test_program_manager_can_list_the_organization_forms(): void
    {
        $this->grant('organizationmanager', ['forms.builder_view']);

        $this->actingAs($this->manager($this->org))->getJson('/dashboard/builder/organization/' . $this->org)->assertOk();
    }

    // 2. API message store follows the same recipient rule as the web Messenger

    public function test_founder_cannot_api_message_an_unrelated_user(): void
    {
        $this->grant('registered', ['messages.send', 'messages.save']);
        $stranger = $this->actor('mentor');

        $this->apiAs($this->founder)->postJson('/api/messages', [
            'userTypeValue' => 2, 'userId' => [$stranger->id], 'message' => 'hi',
        ]);

        $this->assertSame(0, $this->messagesTo($stranger));
    }

    public function test_founder_cannot_api_broadcast_to_their_programs_organization(): void
    {
        $this->grant('registered', ['messages.send', 'messages.save']);
        $manager = $this->manager($this->org);

        $this->apiAs($this->founder)->postJson('/api/messages', ['userTypeValue' => 3, 'userId' => [$this->org], 'message' => 'hi']);
        $this->apiAs($this->founder)->postJson('/api/messages', ['userTypeValue' => 0, 'message' => 'hi']);

        $this->assertSame(0, $this->messagesTo($manager));
    }

    public function test_founder_can_still_api_message_a_mentor_of_their_program(): void
    {
        $this->grant('registered', ['messages.send', 'messages.save']);
        $mentor = $this->actor('mentor');
        DB::table('cycles_mentors')->insert(['cycle_id' => $this->cycle, 'user_id' => $mentor->id, 'created_at' => now(), 'updated_at' => now()]);

        $this->apiAs($this->founder)->postJson('/api/messages', [
            'userTypeValue' => 2, 'userId' => [$mentor->id], 'message' => 'hi',
        ])->assertCreated();

        $this->assertSame(1, $this->messagesTo($mentor));
    }

    // 3. Staff-only guards: impersonation, password reset, final screening

    public function test_founder_with_login_as_cannot_impersonate_their_program_manager(): void
    {
        $this->grant('registered', ['users.login_as']);
        $manager = $this->manager($this->org);

        $this->actingAs($this->founder)->get('/dashboard/users/impersonate-start/' . $manager->id)->assertForbidden();

        $this->assertAuthenticatedAs($this->founder);
    }

    public function test_manager_can_still_impersonate_a_founder_of_their_program(): void
    {
        $this->grant('organizationmanager', ['users.login_as']);

        $this->actingAs($this->manager($this->org))->get('/dashboard/users/impersonate-start/' . $this->founder->id)->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($this->founder);
    }

    public function test_founder_with_activity_permissions_cannot_reset_their_managers_password(): void
    {
        $this->grant('registered', ['activities.index', 'activities.reset-password']);
        $manager = $this->manager($this->org);
        $hash = $manager->password;

        $this->actingAs($this->founder)->post('/dashboard/activities/reset-password', ['user_id' => $manager->id])->assertForbidden();

        $this->assertSame($hash, $manager->fresh()->password);
    }

    public function test_judge_with_an_organization_cannot_rewrite_final_screening_assignments(): void
    {
        $this->grant('judger', ['screening.update']);
        $judge = $this->actor('judger', ['organization_id' => $this->org]);
        $step = $this->step($this->cycle, ['stype' => 5]);

        $this->actingAs($judge)->post('/dashboard/final/update', ['step' => $step, 'judges' => [$judge->id], 'users' => [$this->companyId]])->assertForbidden();
    }

    // 4. Program startups search stays inside the program

    public function test_program_startup_search_does_not_leak_other_programs(): void
    {
        $sectorName = 'AuditSector' . Str::random(8);
        $sectorId = DB::table('sectors')->insertGetId(['name_en' => $sectorName, 'name_ar' => $sectorName, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('companies')->where('id', $this->companyId)->update(['sector' => $sectorId]);
        [$foreignCompany] = $this->startup($this->program($this->organization()), ['sector' => $sectorId]);

        $this->actingAs($this->actor('admin'));
        $page = app(ProgramService::class)->getProgramStartups($this->cycle, $sectorName);

        $ids = collect($page->items())->pluck('id')->map('intval')->all();
        $this->assertContains($this->companyId, $ids);
        $this->assertNotContains((int) $foreignCompany, $ids);
    }

    // 5. Route-name permissions that were missing from the seed file

    public function test_route_permissions_exist_in_the_seed_file(): void
    {
        $names = collect(json_decode(file_get_contents(base_path('database/dbFiles/rolesWithDefaultPermissions.json')), true))
            ->flatMap(fn ($group) => collect($group['permissions'])->pluck('name'));

        foreach (['forms.reorder_questions', 'final.export_results', 'final.export_company_results'] as $name) {
            $this->assertTrue($names->contains($name), "{$name} missing from rolesWithDefaultPermissions.json");
        }
    }
}
