<?php

namespace Tests\Feature;

use App\EducationEntity;
use App\Notifications\WelcomeUserNotification;
use App\Settings;
use App\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\Support\FakesEmailDns;
use Tests\Support\SecurityActors;
use Tests\TestCase;

/**
 * The public "apply to a program" form (/{org}/program/{program}/register). A new applicant
 * gets an account, a startup enrolled in the program's first step, their answers saved and
 * the welcome email. Whether that account still has to confirm its email follows the
 * platform's "verify email" setting, the same one that decides whether the form shows its
 * Verification step.
 *
 * `register-email` is checked with email:dns; the lookup is faked so this runs with no resolver.
 *
 * Also where the program's public links send people who should not be filling the form in:
 * a founder already on the application step, and a guest holding a step's join link.
 */
class PublicProgramApplicationTest extends TestCase
{
    use SecurityActors, FakesEmailDns;

    private int $programId;

    private int $stepId;

    private string $programUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpSecurityActors();
        $this->fakeEmailDns(['gmail.com']);

        $orgSlug = 'apply-org-' . Str::lower(Str::random(8));
        $programSlug = 'apply-program-' . Str::lower(Str::random(8));
        $orgId = $this->organization();
        DB::table('organizations')->where('id', $orgId)->update(['slug' => $orgSlug]);
        $this->programId = $this->program($orgId, [
            'is_public' => 1,
            'public_slug' => $programSlug,
            'start' => now()->subDay()->toDateString(),
            'end' => now()->addDays(10)->toDateString(),
        ]);
        $this->stepId = $this->step($this->programId, ['stype' => 1, 'arr' => 1]);
        $this->programUrl = "/{$orgSlug}/program/{$programSlug}";
    }

    /** Settings > "Verification Email": whether new accounts must confirm their address. */
    private function platformRequiresEmailVerification(bool $required): void
    {
        $settings = Settings::find(1) ?? Settings::factory()->create(['id' => 1]);
        $settings->forceFill(['VFEmail' => $required ? 1 : 0])->save();
    }

    /** A real request loads the signed-in account from the database; a test keeps the last one in memory. */
    private function asANewRequest(): void
    {
        auth()->forgetGuards();
    }

    private function newEmail(): string
    {
        return 'applicant.' . Str::lower(Str::random(10)) . '@gmail.com';
    }

    private function apply(string $email, array $fields = [])
    {
        return $this->postJson($this->programUrl . '/register', array_merge([
            'register-email' => $email,
            'register-first-name' => 'Nour',
            'register-last-name' => 'Hassan',
        ], $fields));
    }

    public function test_a_new_applicant_gets_an_account_enrolled_in_the_program_and_the_welcome_email(): void
    {
        $email = $this->newEmail();

        $this->apply($email)->assertOk()->assertJsonPath('status', true);

        $applicant = User::where('email', $email)->first();
        $this->assertNotNull($applicant, 'no account was created');
        $this->assertTrue($applicant->hasRole('registered'));
        $this->assertSame($this->programId, (int) $applicant->company->cycle);
        $this->assertSame($this->stepId, (int) $applicant->company->step);
        Notification::assertSentToTimes($applicant, WelcomeUserNotification::class, 1);
        $this->assertAuthenticatedAs($applicant);
    }

    public function test_the_answers_to_the_application_form_are_saved_for_the_new_startup(): void
    {
        $fieldId = DB::table('form_fields')->insertGetId([
            'step_id' => $this->stepId,
            'field_id' => DB::table('fields')->value('id'),
            'options' => json_encode(['label' => 'What are you building?', 'type' => 'text', 'validation' => ['required' => 1]]),
        ]);
        $email = $this->newEmail();

        $this->apply($email, ['field_' . $fieldId => 'A solar water pump'])->assertJsonPath('status', true);

        $applicant = User::where('email', $email)->firstOrFail();
        $this->assertDatabaseHas('form_data', [
            'field_id' => $fieldId,
            'company_id' => $applicant->company_id,
            'user_id' => $applicant->id,
            'step_id' => $this->stepId,
            'question' => 'What are you building?',
            'answer' => 'A solar water pump',
        ]);
    }

    public function test_the_applicant_is_verified_at_once_when_the_platform_does_not_require_email_verification(): void
    {
        $this->platformRequiresEmailVerification(false);
        $email = $this->newEmail();

        $this->apply($email)->assertJsonPath('status', true);

        $this->assertNotNull(User::where('email', $email)->firstOrFail()->email_verified_at);
    }

    public function test_the_applicant_is_left_unverified_when_the_platform_requires_email_verification(): void
    {
        $this->platformRequiresEmailVerification(true);
        $email = $this->newEmail();

        $this->apply($email)->assertJsonPath('status', true);

        $applicant = User::where('email', $email)->firstOrFail();
        $this->assertNull($applicant->email_verified_at);
        // Still welcomed: that email carries the link that confirms the address.
        Notification::assertSentToTimes($applicant, WelcomeUserNotification::class, 1);
    }

    public function test_the_welcome_email_link_verifies_an_applicant_who_was_left_unverified(): void
    {
        $this->platformRequiresEmailVerification(true);
        $email = $this->newEmail();
        $this->apply($email)->assertJsonPath('status', true);
        $applicant = User::where('email', $email)->firstOrFail();
        $link = $applicant->generateMagicLoginUrl(); // what rendering the welcome email does
        $this->asANewRequest();

        $this->get($link)->assertRedirect(route('user.profile_Edit'));

        $this->assertNotNull($applicant->fresh()->email_verified_at);
    }

    public function test_an_unverified_applicant_who_opens_the_dashboard_is_asked_to_verify_their_email(): void
    {
        $this->withMiddleware([EnsureEmailIsVerified::class]); // SecurityActors switches it off
        $this->platformRequiresEmailVerification(true);
        $this->apply($this->newEmail())->assertJsonPath('status', true);

        // Until the welcome email is generated the account is also flagged "must change
        // password", which sends every page to the profile; the profile needs a verified email.
        $this->asANewRequest();
        $this->get('/dashboard')->assertRedirect(route('user.profile_Edit'));
        $this->asANewRequest();
        $this->get(route('user.profile_Edit'))->assertRedirect(route('verification.notice'));
        $this->asANewRequest();
        $this->get(route('verification.notice'))->assertOk()->assertViewIs('auth.verify');
    }

    /** @return array<string, array{bool}> */
    public static function verificationSettings(): array
    {
        return ['verification required' => [true], 'verification not required' => [false]];
    }

    /**
     * The form tells the applicant to check their inbox exactly when their account will be
     * left unverified.
     *
     * @dataProvider verificationSettings
     */
    public function test_the_application_form_shows_its_verification_step_only_when_verification_is_required(bool $required): void
    {
        $this->platformRequiresEmailVerification($required);

        $page = $this->get($this->programUrl . '/apply')->assertOk();

        $required
            ? $page->assertSee('id="verification-step-pane"', false)
            : $page->assertDontSee('id="verification-step-pane"', false);
    }

    public function test_an_address_whose_domain_has_no_mail_server_is_rejected_and_no_account_is_created(): void
    {
        $email = 'applicant.' . Str::lower(Str::random(10)) . '@no-mail-server-here.com';

        $this->apply($email)->assertStatus(422)->assertJsonValidationErrors('register-email');

        $this->assertDatabaseMissing('users', ['email' => $email]);
    }

    /** A founder whose idea is still on the application (form) step of this program. */
    private function founderOnTheApplicationStep(): User
    {
        // The redirects below must carry the program's id, so the step's id has to be a different number.
        $stepId = $this->stepId !== $this->programId ? $this->stepId : $this->step($this->programId, ['stype' => 1, 'arr' => 2]);
        [, $founder] = $this->startup($this->programId, ['step' => $stepId]);
        $founder->forceFill(['email' => $this->newEmail()])->save();

        return $founder;
    }

    public function test_a_signed_in_founder_on_the_application_step_is_sent_to_their_program_from_the_form(): void
    {
        $founder = $this->founderOnTheApplicationStep();

        $this->actingAs($founder)->get($this->programUrl . '/apply')
            ->assertRedirect(url('dashboard/cycles/view/' . $this->programId));
    }

    public function test_a_founder_on_the_application_step_who_signs_in_from_the_form_is_sent_to_their_program(): void
    {
        $founder = $this->founderOnTheApplicationStep();

        $this->postJson($this->programUrl . '/authenticate', ['email' => $founder->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('url', url('dashboard/cycles/view/' . $this->programId));
    }

    public function test_a_guest_who_opens_a_step_s_public_join_link_is_asked_to_sign_in(): void
    {
        $orgCode = Str::lower(Str::random(8));
        $stepSlug = 'join-step-' . Str::lower(Str::random(8));
        DB::table('organizations')->where('id', DB::table('cycles')->where('id', $this->programId)->value('organization_id'))->update(['short_code' => $orgCode]);
        DB::table('steps')->where('id', $this->stepId)->update(['is_public' => 1, 'public_slug' => $stepSlug]);

        $this->get("/{$orgCode}/join/{$stepSlug}")->assertRedirect(route('login'));
    }

    public function test_an_isf_applicant_also_has_their_mobile_education_and_referral_details_saved(): void
    {
        config(['app.localization_path' => 'isf']);
        $university = EducationEntity::create(['type' => EducationEntity::TYPE_UNIVERSITY, 'name_en' => 'Apply Test University', 'name_ar' => 'جامعة']);
        $email = $this->newEmail();

        $this->apply($email, [
            'phone' => '1012345678',
            'country_code' => '20',
            'academic_stat' => EducationEntity::STATUS_UNIVERSITY_STUDENT,
            'education_entity_id' => $university->id,
            'national_id' => '29001011234567',
            'referral_source' => 'linkedin',
        ])->assertOk()->assertJsonPath('status', true);

        $applicant = User::where('email', $email)->firstOrFail();
        $this->assertSame('1012345678', $applicant->phone);
        $this->assertSame('20', $applicant->country_code);
        $this->assertSame(EducationEntity::STATUS_UNIVERSITY_STUDENT, $applicant->academic_stat);
        $this->assertSame($university->id, (int) $applicant->education_entity_id);
        $this->assertSame('29001011234567', $applicant->national_id);
        $this->assertNull($applicant->student_code);
        $this->assertSame('linkedin', $applicant->referral_source);
        Notification::assertSentToTimes($applicant, WelcomeUserNotification::class, 1);
    }
}
