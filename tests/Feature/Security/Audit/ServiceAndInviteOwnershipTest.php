<?php

namespace Tests\Feature\Security\Audit;

use App\User;
use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * H8: services could be edited/deleted by any provider or staff member, and hackathon
 * invitations could be accepted/declined by id alone — claiming someone else's seat or
 * self-approving one's own join request. C1: declining a missing invitation crashed.
 */
class ServiceAndInviteOwnershipTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
        $this->grant('serviceProvider', ['services.destroy', 'services.edit']);
        $this->grant('organizationmanager', ['services.destroy', 'services.edit']);
        $this->grant('registered', ['hackathon.acceptInvitation', 'hackathon.rejectInvitation']);
    }

    /** @return array{0:User,1:int} provider user and its service_providers id */
    private function provider(): array
    {
        $user = $this->actor('serviceProvider');
        $providerId = DB::table('service_providers')->insertGetId([
            'company_name' => 'Audit Provider', 'contact_name' => 'Audit', 'email' => $user->email,
            'status' => 1, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$user, $providerId];
    }

    private function service(int $providerId, ?int $organizationId = null): int
    {
        $categoryId = DB::table('service_categories')->insertGetId(['name_en' => 'Audit', 'name_ar' => 'Audit', 'created_at' => now(), 'updated_at' => now()]);
        $serviceId = DB::table('services')->insertGetId([
            'service_category_id' => $categoryId, 'service_provider_id' => $providerId,
            'name_en' => 'Audit Service', 'name_ar' => 'Audit Service', 'status' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        if ($organizationId) {
            DB::table('organization_service')->insert(['organization_id' => $organizationId, 'service_id' => $serviceId, 'created_at' => now(), 'updated_at' => now()]);
        }

        return $serviceId;
    }

    private function serviceExists(int $id): bool
    {
        return DB::table('services')->where('id', $id)->whereNull('deleted_at')->exists();
    }

    public function test_provider_cannot_delete_or_edit_another_providers_service(): void
    {
        [, $ownerProviderId] = $this->provider();
        [$other] = $this->provider();
        $serviceId = $this->service($ownerProviderId);

        $this->actingAs($other)->deleteJson("/dashboard/services/{$serviceId}")->assertForbidden();
        $this->actingAs($other)->get("/dashboard/services/{$serviceId}/edit")->assertForbidden();

        $this->assertTrue($this->serviceExists($serviceId));
    }

    public function test_manager_cannot_delete_a_service_of_another_organization(): void
    {
        [, $providerId] = $this->provider();
        $serviceId = $this->service($providerId, $this->organization());

        $this->actingAs($this->manager($this->organization()))->deleteJson("/dashboard/services/{$serviceId}")->assertForbidden();

        $this->assertTrue($this->serviceExists($serviceId));
    }

    public function test_owner_and_linked_org_manager_can_delete(): void
    {
        [$owner, $providerId] = $this->provider();
        $org = $this->organization();
        $first = $this->service($providerId);
        $second = $this->service($providerId, $org);

        $this->actingAs($owner)->deleteJson("/dashboard/services/{$first}")->assertOk();
        $this->actingAs($this->manager($org))->deleteJson("/dashboard/services/{$second}")->assertOk();

        $this->assertFalse($this->serviceExists($first));
        $this->assertFalse($this->serviceExists($second));
    }

    /** A hackathon with one team; returns [team_formation_id, leader, company id]. */
    private function hackathonTeam(): array
    {
        $cycle = $this->program($this->organization());
        $step = $this->step($cycle, ['stype' => 6, 'count_team_number' => 5]);
        [$companyId, $leader] = $this->startup($cycle);
        $teamId = DB::table('team_formations')->insertGetId([
            'user_id' => $leader->id, 'name' => 'Audit Team', 'cycle_id' => $cycle, 'step_id' => $step,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$teamId, $leader, $companyId];
    }

    private function invitation(int $teamId, int $companyId, string $email, string $status = 'pending'): int
    {
        return DB::table('team_members')->insertGetId([
            'TeamMember' => 'hackathon', 'Name' => 'Invitee', 'Email' => $email, 'Company_id' => $companyId,
            'acceptInvition' => $status, 'isTeamLeader' => 0, 'team_formation_id' => $teamId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function inviteStatus(int $id): string
    {
        return (string) DB::table('team_members')->where('id', $id)->value('acceptInvition');
    }

    public function test_user_cannot_accept_or_decline_someone_elses_invitation(): void
    {
        [$teamId, , $companyId] = $this->hackathonTeam();
        $inviteId = $this->invitation($teamId, $companyId, 'invitee-' . uniqid() . '@example.invalid');
        $intruder = $this->actor('registered');

        $this->actingAs($intruder)->patch("/dashboard/hackathon/acceptInvitation/{$inviteId}");
        $this->assertSame('pending', $this->inviteStatus($inviteId));

        $this->actingAs($intruder)->patch("/dashboard/hackathon/rejectInvitation/{$inviteId}");
        $this->assertSame('pending', $this->inviteStatus($inviteId));
    }

    public function test_requester_cannot_approve_their_own_join_request(): void
    {
        [$teamId, , $companyId] = $this->hackathonTeam();
        $requester = $this->actor('registered');
        $joinId = $this->invitation($teamId, $companyId, $requester->email, 'join');

        $this->actingAs($requester)->patch("/dashboard/hackathon/acceptInvitation/{$joinId}");

        $this->assertSame('join', $this->inviteStatus($joinId));
    }

    public function test_invitee_can_decline_their_own_invitation_matching_email_case_insensitively(): void
    {
        [$teamId, , $companyId] = $this->hackathonTeam();
        $invitee = $this->actor('registered');
        $inviteId = $this->invitation($teamId, $companyId, strtoupper($invitee->email));

        $this->actingAs($invitee)->patch("/dashboard/hackathon/rejectInvitation/{$inviteId}")->assertRedirect();

        $this->assertSame('rejected', $this->inviteStatus($inviteId));
    }

    public function test_invitee_can_accept_their_own_invitation(): void
    {
        [$teamId, , $companyId] = $this->hackathonTeam();
        $invitee = $this->actor('registered');
        $inviteId = $this->invitation($teamId, $companyId, $invitee->email);

        $this->actingAs($invitee)->patch("/dashboard/hackathon/acceptInvitation/{$inviteId}");

        $this->assertSame('accepted', $this->inviteStatus($inviteId));
    }

    public function test_declining_a_missing_invitation_does_not_crash(): void
    {
        $this->actingAs($this->actor('registered'))->patch('/dashboard/hackathon/rejectInvitation/999999999')->assertRedirect();
    }
}
