<?php

namespace Tests\Feature\Security;

use App\User;
use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * GET /api/profile/{id} returned any user's profile (email, phone, WhatsApp, address,
 * startup, mentorship sessions) to every authenticated API user, so a founder could walk
 * user ids. Only the user themself, a platform admin, or staff managing the target user's
 * organization may read it.
 */
class ApiProfileIdorTest extends TestCase
{
    use SecurityActors;

    private int $org;
    private int $cycle;
    private User $founder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();

        $this->org = $this->organization();
        $this->cycle = $this->program($this->org);
        [, $this->founder] = $this->startup($this->cycle);
    }

    private function profileAs(User $caller, User $target)
    {
        return $this->withHeader('Authorization', 'Bearer ' . $caller->createToken('audit')->plainTextToken)
            ->getJson('/api/profile/' . $target->id);
    }

    private function assertRefused(User $caller, User $target): void
    {
        $response = $this->profileAs($caller, $target);

        $role = $caller->roles()->value('default_for');
        $this->assertSame(403, $response->status(), "caller {$role} got {$response->status()}");
        $this->assertStringNotContainsString($target->email, $response->getContent());
        $this->assertStringNotContainsString((string) $target->phone, $response->getContent());
    }

    // ------------------------------------------------------------------ refused

    public function test_user_cannot_read_another_users_profile(): void
    {
        $caller = $this->actor('registered');
        $victim = $this->actor('registered');

        $this->assertRefused($caller, $victim);
    }

    public function test_founder_cannot_read_profiles_in_their_own_programs_organization(): void
    {
        // A founder "owns" their program's organization for scoping purposes; that must not
        // open the organization's staff or the program's other founders to them.
        [, $sameProgramFounder] = $this->startup($this->cycle);

        $this->assertRefused($this->founder, $this->manager($this->org));
        $this->assertRefused($this->founder, $sameProgramFounder);
    }

    public function test_manager_of_another_organization_cannot_read_the_profile(): void
    {
        $outsider = $this->manager($this->organization());

        $this->assertRefused($outsider, $this->founder);
        $this->assertRefused($outsider, $this->manager($this->org));
    }

    public function test_mentor_judge_and_service_provider_cannot_read_a_founders_profile(): void
    {
        foreach (['mentor', 'judger', 'serviceProvider'] as $role) {
            $this->assertRefused($this->actor($role), $this->founder);
        }
    }

    // ------------------------------------------------------------------ allowed

    public function test_user_can_read_their_own_profile(): void
    {
        $this->profileAs($this->founder, $this->founder)
            ->assertOk()
            ->assertJsonPath('data.id', $this->founder->id)
            ->assertJsonPath('data.email', $this->founder->email);
    }

    public function test_manager_can_read_profiles_in_their_organization(): void
    {
        $manager = $this->manager($this->org);
        $colleague = $this->manager($this->org);

        // A founder of a startup in the organization's program, and a staff account of the organization.
        $this->profileAs($manager, $this->founder)->assertOk()->assertJsonPath('data.email', $this->founder->email);
        $this->profileAs($manager, $colleague)->assertOk()->assertJsonPath('data.email', $colleague->email);
    }

    public function test_sub_admin_can_read_profiles_in_an_assigned_organization(): void
    {
        $subAdmin = $this->actor('subadmin');
        DB::table('user_organizations')->insert([
            'user_id' => $subAdmin->id, 'organization_id' => $this->org, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->profileAs($subAdmin, $this->founder)->assertOk()->assertJsonPath('data.id', $this->founder->id);
    }

    public function test_admin_can_read_any_profile(): void
    {
        $this->profileAs($this->actor('admin'), $this->founder)->assertOk()->assertJsonPath('data.id', $this->founder->id);
    }
}
