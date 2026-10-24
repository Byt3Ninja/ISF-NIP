<?php

namespace Tests\Feature\Security\Audit;

use App\Invitation;
use App\Notifications\UserInvitationNotification;
use App\User;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * H4: invitation set-password / accept links must be signed, expiring and single-use;
 * resend/cancel must stay inside the manager's organization.
 */
class InvitationLinkTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
    }

    private function invitation(int $organizationId, array $attributes = []): Invitation
    {
        return Invitation::create(array_merge([
            'first_name' => 'Invited',
            'last_name' => 'Manager',
            'email' => 'invited-' . uniqid() . '@example.invalid',
            'role' => DB::table('roles')->where('default_for', 'organizationmanager')->value('name'),
            'organization_id' => $organizationId,
            'expires_at' => now()->addDays(2),
            'status' => 'pending',
        ], $attributes));
    }

    private function acceptPayload(): array
    {
        return ['password' => 'password123', 'password_confirmation' => 'password123'];
    }

    public function test_unsigned_set_password_link_is_rejected(): void
    {
        $invitation = $this->invitation($this->organization());

        $this->get('/organizations/set-password/' . $invitation->id)->assertForbidden();
    }

    public function test_signed_set_password_link_renders_the_form_posting_to_a_signed_accept_url(): void
    {
        $invitation = $this->invitation($this->organization());

        $this->get($invitation->setPasswordUrl())
            ->assertOk()
            ->assertSee('signature=', false);
    }

    public function test_unsigned_accept_creates_nothing(): void
    {
        $invitation = $this->invitation($this->organization());

        $this->post('/organizations/settings/accept/' . $invitation->id, $this->acceptPayload())->assertForbidden();

        $this->assertFalse(User::where('email', $invitation->email)->exists());
    }

    public function test_signed_accept_creates_the_manager_once(): void
    {
        $org = $this->organization();
        $invitation = $this->invitation($org);

        $this->post($invitation->acceptUrl(), $this->acceptPayload())->assertRedirect();

        $user = User::where('email', $invitation->email)->firstOrFail();
        $this->assertSame($org, (int) $user->organization_id);
        $this->assertTrue($user->hasRole('organizationmanager'));
        $this->assertSame('accepted', $invitation->fresh()->status);

        // Replaying the same link is refused.
        $user->forceDelete();
        $this->post($invitation->acceptUrl(), $this->acceptPayload());
        $this->assertFalse(User::where('email', $invitation->email)->exists());
    }

    public function test_accept_of_a_cancelled_or_used_invitation_is_refused(): void
    {
        $invitation = $this->invitation($this->organization(), ['status' => 'accepted']);

        $this->post($invitation->acceptUrl(), $this->acceptPayload());

        $this->assertFalse(User::where('email', $invitation->email)->exists());
    }

    public function test_invitation_email_contains_a_signed_link(): void
    {
        $invitation = $this->invitation($this->organization());

        $html = (string) (new UserInvitationNotification($invitation))
            ->toMail(new AnonymousNotifiable())
            ->render();

        $this->assertStringContainsString('signature=', $html);
    }

    public function test_manager_cannot_resend_or_cancel_another_orgs_invitation(): void
    {
        $manager = $this->manager($this->organization());
        $this->grant('organizationmanager', ['organization.users_invitations_resend', 'organization.users_invitations_cancel']);
        $foreign = $this->invitation($this->organization());

        $this->actingAs($manager)->post('/dashboard/organizations/settings/resend-invitation/' . $foreign->id)->assertForbidden();
        $this->actingAs($manager)->delete('/dashboard/organizations/settings/cancel-invitation/' . $foreign->id)->assertForbidden();

        $this->assertNotNull($foreign->fresh());
    }

    public function test_manager_can_cancel_own_orgs_invitation(): void
    {
        $org = $this->organization();
        $manager = $this->manager($org);
        $this->grant('organizationmanager', ['organization.users_invitations_cancel']);
        $own = $this->invitation($org);

        $this->actingAs($manager)->delete('/dashboard/organizations/settings/cancel-invitation/' . $own->id)->assertRedirect();

        $this->assertNull($own->fresh());
    }
}
