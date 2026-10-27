<?php

namespace Tests\Feature;

use App\Invitation;
use App\Notifications\OrganizationAssignedNotification;
use App\Notifications\UserInvitationNotification;
use App\Organization;
use App\Settings;
use App\User;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * GV-10030 (org manager invites from Settings → Team Members),
 * GV-7257 / GV-7258 (admin / sub-admin assignment email).
 */
class OrganizationManagerInvitationTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
        $this->grant('organizationmanager', ['organization.users_invite']);
        $this->grant('subadmin', ['organization.users_invite']);
    }

    private function invite(User $as, string $email): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($as)->post('/dashboard/organizations/settings/invite', [
            'first_name' => 'Sara',
            'last_name' => 'Ali',
            'email' => $email,
            'role' => DB::table('roles')->where('default_for', 'organizationmanager')->value('name'),
        ]);
    }

    private function email(): string
    {
        return 'invitee-' . uniqid() . '@example.invalid';
    }

    private function sentTo(string $email): int
    {
        return Notification::sent(new AnonymousNotifiable(), UserInvitationNotification::class)
            ->filter(fn ($n) => $n->invitation->email === $email)
            ->count();
    }

    public function test_invite_sends_the_email_once_and_only_to_the_invited_address(): void
    {
        $org = $this->organization();
        $email = $this->email();

        $this->invite($this->manager($org), $email)->assertSessionHas('message', 'InvitationSent');

        $invitation = Invitation::where('email', $email)->firstOrFail();
        $this->assertSame($org, (int) $invitation->organization_id);
        $this->assertSame(1, $this->sentTo($email));
        Notification::assertSentOnDemand(
            UserInvitationNotification::class,
            fn ($n, $channels, $notifiable) => $notifiable->routes['mail'] === $email
        );
    }

    public function test_no_email_when_the_invitation_is_not_created(): void
    {
        $manager = $this->manager($this->organization());

        // Invalid email → validation fails before anything is stored.
        $this->invite($manager, 'not-an-email');
        // Already a registered user → refused by the unique rule.
        $this->invite($manager, $manager->email);

        Notification::assertNothingSent();
    }

    public function test_user_without_an_organization_cannot_invite(): void
    {
        $email = $this->email();

        $this->invite($this->actor('subadmin'), $email)
            ->assertSessionHas('message2', __('settings.no_organization_linked'));

        $this->assertFalse(Invitation::where('email', $email)->exists());
        Notification::assertNothingSent();
    }

    public function test_sub_admin_invites_into_the_organization_the_settings_page_shows(): void
    {
        $orgs = [$this->organization(), $this->organization()];
        $subAdmin = $this->actor('subadmin');
        $subAdmin->organizations()->attach($orgs);
        $email = $this->email();

        $shown = Organization::whereIn('id', $subAdmin->organizations_ids)->first()->id;
        $this->invite($subAdmin, $email);

        $this->assertSame($shown, (int) Invitation::where('email', $email)->value('organization_id'));
        $this->assertSame(1, $this->sentTo($email));
    }

    public function test_reinviting_the_same_email_resends_without_duplicating_the_invitation(): void
    {
        $manager = $this->manager($this->organization());
        $email = $this->email();

        $this->invite($manager, $email);
        $this->invite($manager, $email);

        $this->assertSame(1, Invitation::where('email', $email)->count());
        $this->assertSame(2, $this->sentTo($email));
    }

    public function test_email_follows_the_platform_language_and_template(): void
    {
        $orgId = $this->organization();
        $org = Organization::findOrFail($orgId);
        $invitation = Invitation::create([
            'first_name' => 'Sara', 'last_name' => 'Ali', 'email' => $this->email(),
            'role' => DB::table('roles')->where('default_for', 'organizationmanager')->value('name'),
            'organization_id' => $orgId, 'expires_at' => now()->addDays(2), 'status' => 'pending',
        ]);

        foreach (['en' => 'You’re Invited to Join', 'ar' => 'تمت دعوتك للانضمام إلى'] as $locale => $subject) {
            Settings::query()->update(['Default_Language' => $locale]);
            app()->setLocale($locale === 'en' ? 'ar' : 'en'); // inviter's locale must not leak in

            $mail = (new UserInvitationNotification($invitation))->toMail(new AnonymousNotifiable());
            $html = (string) $mail->render();

            $this->assertSame("{$subject} {$org->name}", $mail->subject);
            $this->assertStringContainsString('Sara Ali', $html);
            $this->assertStringContainsString(e($invitation->setPasswordUrl()), $html);
            $this->assertStringContainsString(__('email.org_assignment_invite_email.button', [], $locale), $html);
        }
    }

    public function test_accepting_logs_the_invitee_in_and_opens_the_dashboard(): void
    {
        $org = $this->organization();
        $this->invite($this->manager($org), $email = $this->email());
        $invitation = Invitation::where('email', $email)->firstOrFail();
        auth()->logout();

        $this->post($invitation->acceptUrl(), ['password' => 'password123', 'password_confirmation' => 'password123'])
            ->assertRedirect(route('core.dashboard_view'));

        $user = User::where('email', $email)->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->organizations()->where('organizations.id', $org)->exists());
    }

    public function test_assignment_email_is_sent_even_when_the_user_turned_email_off(): void
    {
        $user = $this->actor('subadmin', ['accept_email' => 0, 'accept_whatsapp' => 1]);
        $org = Organization::findOrFail($this->organization());

        $this->assertContains('mail', (new OrganizationAssignedNotification($org))->via($user));
    }
}
