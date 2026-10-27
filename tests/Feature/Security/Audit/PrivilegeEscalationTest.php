<?php

namespace Tests\Feature\Security\Audit;

use App\Invitation;
use App\User;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * H5a invite-as-admin, H5b role default_for, H5c/S1-S3 OAuth, N12 dd(), allotRole matching.
 */
class PrivilegeEscalationTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
    }

    /** fakeOutboundServices() registers a catch-all Http::fake() first; start from a clean factory. */
    private function freshHttp(): void
    {
        Http::swap(new Factory());
        Http::preventStrayRequests();
    }

    private function fakeLinkedIn(array $claims): void
    {
        $this->freshHttp();
        Http::fake([
            'www.linkedin.com/oauth/v2/accessToken' => Http::response(['access_token' => 'fake-token']),
            'api.linkedin.com/v2/userinfo' => Http::response($claims),
        ]);
    }

    public function test_allot_role_with_an_empty_value_assigns_nothing(): void
    {
        $user = $this->actor('mentor');

        $user->allotRole('');
        $user->allotRole(null);

        $this->assertSame(['mentor'], $user->fresh()->roles()->pluck('default_for')->all());
    }

    public function test_allot_role_matches_the_exact_role(): void
    {
        $user = $this->actor('mentor');

        $user->allotRole('subadmin');

        $this->assertEqualsCanonicalizing(['mentor', 'subadmin'], $user->fresh()->roles()->pluck('default_for')->all());
    }

    public function test_linkedin_redirect_never_grants_mentor_and_sends_state(): void
    {
        $response = $this->get('/auth/linkedin/2520');

        $response->assertRedirect();
        $this->assertStringContainsString('state=', $response->headers->get('Location'));
        $this->assertNotSame('Mentor', session('role'));
    }

    public function test_linkedin_callback_without_matching_state_does_not_log_in(): void
    {
        $existing = $this->actor('registered');
        $this->fakeLinkedIn(['sub' => 'li-1', 'email' => $existing->email, 'email_verified' => true]);

        $this->withSession(['linkedin_oauth_state' => 'expected'])
            ->get('/auth/linkedin-openid/callback?code=abc&state=forged')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_linkedin_links_existing_verified_account_without_changing_its_roles_or_profile(): void
    {
        $existing = $this->actor('registered', ['first_name_en' => 'Original']);
        $this->fakeLinkedIn(['sub' => 'li-2', 'email' => $existing->email, 'email_verified' => true, 'given_name' => 'Attacker']);

        $this->withSession(['linkedin_oauth_state' => 'good', 'role' => 'Mentor'])
            ->get('/auth/linkedin-openid/callback?code=abc&state=good')
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($existing);
        $fresh = $existing->fresh();
        $this->assertSame('Original', $fresh->first_name_en);
        $this->assertSame(['registered'], $fresh->roles()->pluck('default_for')->all());
    }

    public function test_linkedin_refuses_existing_account_when_email_is_unverified(): void
    {
        $existing = $this->actor('registered');
        $this->fakeLinkedIn(['sub' => 'li-3', 'email' => $existing->email, 'email_verified' => false]);

        $this->withSession(['linkedin_oauth_state' => 'good'])
            ->get('/auth/linkedin-openid/callback?code=abc&state=good')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_linkedin_new_account_is_a_founder(): void
    {
        $email = 'li-new-' . uniqid() . '@example.invalid';
        $this->fakeLinkedIn(['sub' => 'li-4', 'email' => $email, 'email_verified' => true, 'given_name' => 'New', 'family_name' => 'User']);

        $this->withSession(['linkedin_oauth_state' => 'good', 'role' => 'Mentor'])
            ->get('/auth/linkedin-openid/callback?code=abc&state=good')
            ->assertRedirect('/dashboard');

        $this->assertSame(['registered'], User::where('email', $email)->firstOrFail()->roles()->pluck('default_for')->all());
    }

    public function test_linkedin_callback_failure_redirects_instead_of_dumping(): void
    {
        $this->freshHttp();
        Http::fake(['*' => fn () => throw new \RuntimeException('boom')]);

        $this->withSession(['linkedin_oauth_state' => 'good'])
            ->get('/auth/linkedin-openid/callback?code=abc&state=good')
            ->assertRedirect(route('login'));
    }

    public function test_generic_social_callback_rejects_unknown_provider(): void
    {
        $this->get('/callback/github')->assertNotFound();
        $this->get('/auth/redirect/github')->assertNotFound();
    }

    public function test_org_manager_cannot_invite_an_admin(): void
    {
        $org = $this->organization();
        $manager = $this->manager($org);
        $this->grant('organizationmanager', ['organization.users_invite']);
        $email = 'invitee-' . uniqid() . '@example.invalid';

        $this->actingAs($manager)->post('/dashboard/organizations/settings/invite', [
            'first_name' => 'Evil', 'last_name' => 'Admin', 'email' => $email, 'role' => 'admin',
        ]);

        $this->assertFalse(Invitation::where('email', $email)->exists());
    }

    public function test_org_manager_can_still_invite_an_org_manager(): void
    {
        $org = $this->organization();
        $manager = $this->manager($org);
        $this->grant('organizationmanager', ['organization.users_invite']);
        $email = 'invitee-' . uniqid() . '@example.invalid';
        $roleName = DB::table('roles')->where('default_for', 'organizationmanager')->value('name');

        $this->actingAs($manager)->post('/dashboard/organizations/settings/invite', [
            'first_name' => 'Good', 'last_name' => 'Manager', 'email' => $email, 'role' => $roleName,
        ]);

        $this->assertSame($org, (int) Invitation::where('email', $email)->value('organization_id'));
    }

    public function test_role_default_for_must_be_a_known_type(): void
    {
        $admin = $this->actor('admin');
        $this->grant('admin', ['roles.store']);

        $permission = DB::table('permissions')->whereNull('deleted_at')->value('name');

        $this->actingAs($admin)->post('/dashboard/roles', [
            'name' => 'Audit Role ' . uniqid(),
            'default_for' => 'superuser',
            'permissions' => [$permission],
        ]);

        $this->assertFalse(DB::table('roles')->where('default_for', 'superuser')->exists());
    }
}
