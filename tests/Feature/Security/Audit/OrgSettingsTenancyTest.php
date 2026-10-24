<?php

namespace Tests\Feature\Security\Audit;

use App\Organization;
use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * H2: organization settings, the organization wizard and WhatsApp sessions loaded an
 * organization (or session) by a client-supplied id without checking it is the caller's.
 */
class OrgSettingsTenancyTest extends TestCase
{
    use SecurityActors;

    private const SETTINGS_PERMISSIONS = ['settings.organization_meeting_manage', 'settings.organization_social_manage', 'settings.organization.general.manage'];
    private const WIZARD_PERMISSIONS = ['organization.wizard.edit', 'organization.wizard.managers.list', 'organization.wizard.publish', 'organization.wizard.edit'];
    private const WHATSAPP_PERMISSIONS = ['whatsapp.session.terminate', 'whatsapp.session.status', 'whatsapp.session.qr.data', 'whatsapp.session.start'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
        $this->grant('organizationmanager', array_merge(self::SETTINGS_PERMISSIONS, self::WHATSAPP_PERMISSIONS));
        $this->grant('subadmin', self::WIZARD_PERMISSIONS);
    }

    private function subadminFor(int $organizationId)
    {
        $subadmin = $this->actor('subadmin');
        DB::table('user_organizations')->insert(['user_id' => $subadmin->id, 'organization_id' => $organizationId, 'created_at' => now(), 'updated_at' => now()]);

        return $subadmin;
    }

    public function test_manager_cannot_overwrite_another_orgs_meeting_credentials(): void
    {
        $manager = $this->manager($this->organization());
        $foreign = $this->organization();

        $this->actingAs($manager)->put('/dashboard/organizations/settings/updateMeeting', [
            'id' => $foreign, 'meeting_platform' => 'zoom', 'ZOOM_CLIENT_ID' => 'attacker-client',
        ])->assertForbidden();

        $this->assertNull(Organization::find($foreign)->zoom_client_id);
    }

    public function test_manager_cannot_change_another_orgs_general_or_social_settings(): void
    {
        $manager = $this->manager($this->organization());
        $foreign = $this->organization();

        $this->actingAs($manager)->put('/dashboard/organizations/settings/general', ['id' => $foreign, 'contact_email' => 'evil@example.invalid'])->assertForbidden();
        $this->actingAs($manager)->put('/dashboard/organizations/settings/socialLogin', ['id' => $foreign, 'website_url' => 'https://evil.example'])->assertForbidden();

        $org = Organization::find($foreign);
        $this->assertNotSame('evil@example.invalid', $org->contact_email);
        $this->assertNotSame('https://evil.example', $org->website_url);
    }

    public function test_manager_can_still_update_own_org_meeting_settings(): void
    {
        $own = $this->organization();

        $this->actingAs($this->manager($own))->put('/dashboard/organizations/settings/updateMeeting', [
            'id' => $own, 'meeting_platform' => 'zoom', 'ZOOM_CLIENT_ID' => 'own-client',
        ])->assertRedirect();

        $this->assertSame('own-client', Organization::find($own)->zoom_client_id);
    }

    public function test_subadmin_cannot_use_the_wizard_on_an_unassigned_org(): void
    {
        $subadmin = $this->subadminFor($this->organization());
        $foreign = $this->organization();
        DB::table('organizations')->where('id', $foreign)->update(['is_draft' => 1]);

        $this->actingAs($subadmin)->getJson("/dashboard/organization/{$foreign}/managers")->assertForbidden();
        $this->actingAs($subadmin)->post("/dashboard/organization/{$foreign}/publish")->assertForbidden();

        $this->assertSame(1, (int) DB::table('organizations')->where('id', $foreign)->value('is_draft'));
    }

    public function test_subadmin_can_use_the_wizard_on_an_assigned_org(): void
    {
        $own = $this->organization();

        $this->actingAs($this->subadminFor($own))->getJson("/dashboard/organization/{$own}/managers")->assertOk();
    }

    public function test_manager_cannot_terminate_another_orgs_whatsapp_session(): void
    {
        $manager = $this->manager($this->organization());
        $foreign = $this->organization();
        DB::table('organizations')->where('id', $foreign)->update(['org_whatsApp_SESSION_ID' => '987654', 'org_whatsApp_Status_Session' => '1']);

        $this->actingAs($manager)->deleteJson('/dashboard/whatsapp/session/987654/terminate')->assertForbidden();
        $this->actingAs($manager)->postJson('/dashboard/whatsapp/session/987654/qr-data')->assertForbidden();
        $this->actingAs($manager)->postJson('/dashboard/whatsapp/session/987654/start')->assertForbidden();

        $this->assertSame('987654', (string) DB::table('organizations')->where('id', $foreign)->value('org_whatsApp_SESSION_ID'));
    }

    public function test_manager_can_still_reach_own_whatsapp_session(): void
    {
        $own = $this->organization();
        DB::table('organizations')->where('id', $own)->update(['org_whatsApp_SESSION_ID' => '123123']);

        $response = $this->actingAs($this->manager($own))->postJson('/dashboard/whatsapp/session/123123/qr-data');

        $this->assertNotSame(403, $response->getStatusCode());
    }
}
