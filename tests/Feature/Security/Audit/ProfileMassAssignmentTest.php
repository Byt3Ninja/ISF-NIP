<?php

namespace Tests\Feature\Security\Audit;

use App\User;
use Illuminate\Support\Facades\DB;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * Audit C-6: the profile form saved every posted field except a short deny-list, and
 * User::$fillable included company_id, so any user could attach themselves to any startup
 * (and bind someone else's social identity) from their own profile.
 */
class ProfileMassAssignmentTest extends TestCase
{
    use SecurityActors;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
    }

    private function payload(User $user, array $extra = []): array
    {
        return array_merge([
            'first_name_en' => 'Renamed',
            'last_name_en' => 'Founder',
            'phone' => $user->phone,
            'email' => $user->email,
            'country_id' => DB::table('countries')->value('id'),
            'academic_stat' => 'Graduate',
            'dob' => '1990-01-01',
            // beta's profile form also requires these (not required on dev).
            'title_en' => 'Founder',
            'state_id' => DB::table('states')->value('id'),
            'address' => 'Audit street 1',
            'timezone' => 'Africa/Cairo',
        ], $extra);
    }

    public function test_profile_form_cannot_attach_the_user_to_another_startup(): void
    {
        [$ownCompany, $founder] = $this->startup($this->program($this->organization()));
        [$victimCompany] = $this->startup($this->program($this->organization()));
        $this->grant('registered', ['user.profile_update_basic']);

        $this->actingAs($founder)->post('/dashboard/myProfile', $this->payload($founder, [
            'company_id' => $victimCompany,
            'google_id' => 'attacker-google-id',
            'provider_id' => 'attacker-provider-id',
            'email_verified_at' => null,
            'must_change_password' => 1,
        ]))->assertRedirect();

        $founder->refresh();
        $this->assertSame('Renamed', $founder->first_name_en, 'the form still saves its own fields');
        $this->assertSame($ownCompany, (int) $founder->company_id);
        $this->assertNull($founder->google_id);
        $this->assertNull($founder->provider_id);
        $this->assertNotNull($founder->email_verified_at);
        $this->assertFalse((bool) $founder->must_change_password);
    }

    public function test_sensitive_user_columns_are_not_mass_assignable(): void
    {
        $columns = [
            'company_id' => 5,
            'email_verified_at' => now(),
            'google_id' => 'g',
            'provider' => 'google',
            'provider_id' => 'p',
            'new_email' => 'someone@example.invalid',
            'must_change_password' => true,
            'magic_login_sent_at' => now(),
        ];

        $user = (new User)->fill($columns);

        foreach (array_keys($columns) as $column) {
            $this->assertNull($user->getAttribute($column), "{$column} must not be mass assignable");
        }
    }
}
