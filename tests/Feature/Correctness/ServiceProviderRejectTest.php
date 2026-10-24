<?php

namespace Tests\Feature\Correctness;

use App\User;
use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * C39: a rejected service-provider applicant kept a verified login with the
 * serviceProvider role. Rejection now removes the role and the account is signed out.
 */
class ServiceProviderRejectTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
    }

    /** A provider applicant as registration_request() creates them: user + role + pending registration. */
    private function applicant(?bool $status = null, bool $acted = false): array
    {
        $user = $this->actor('serviceProvider');
        $registrationId = DB::table('service_provider_registration')->insertGetId([
            'company_name' => 'Audit Provider', 'contact_name' => 'Audit Provider', 'email' => $user->email,
            'country_code' => '20', 'phone' => '+20' . random_int(100000000, 999999999),
            'status' => $status, 'user_id' => $user->id, 'action_at' => $acted ? now() : null,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$user, $registrationId];
    }

    private function rejectAs(User $admin, int $registrationId)
    {
        $this->grant('admin', ['services_providers.takeAction']);

        return $this->actingAs($admin)->post('/dashboard/services-providers/requests/take-action', [
            'request_id' => $registrationId, 'request_action' => 'reject', 'request_reason' => 'Not a fit',
        ]);
    }

    public function test_rejecting_an_applicant_removes_the_service_provider_role(): void
    {
        [$user, $registrationId] = $this->applicant();

        $this->rejectAs($this->actor('admin'), $registrationId)->assertRedirect();

        $this->assertFalse($user->fresh()->hasActualRole('serviceProvider'));
    }

    public function test_rejected_applicant_cannot_sign_in(): void
    {
        [$user] = $this->applicant(false, true);
        DB::table('model_has_roles')->where('model_id', $user->id)->where('model_type', User::class)->delete();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('login'))
            ->assertSessionHas('error', __('auth.provider_registration_rejected'));

        $this->assertGuest();
    }

    public function test_rejected_applicant_with_an_open_session_is_signed_out(): void
    {
        [$user] = $this->applicant(false, true);
        DB::table('model_has_roles')->where('model_id', $user->id)->where('model_type', User::class)->delete();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_pending_and_approved_applicants_still_sign_in(): void
    {
        [$pending] = $this->applicant(null, false);
        $this->post('/login', ['email' => $pending->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($pending);
        $this->post('/logout');

        [$approved] = $this->applicant(true, true);
        $this->post('/login', ['email' => $approved->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($approved);
    }

    public function test_user_with_another_role_and_a_rejected_application_still_signs_in(): void
    {
        $founder = $this->actor('registered');
        DB::table('service_provider_registration')->insert([
            'company_name' => 'Side Gig', 'contact_name' => 'Side Gig', 'email' => $founder->email, 'country_code' => '20',
            'phone' => '+20' . random_int(100000000, 999999999), 'status' => false, 'user_id' => $founder->id,
            'action_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->post('/login', ['email' => $founder->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($founder);
    }
}
