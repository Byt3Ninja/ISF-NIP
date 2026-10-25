<?php

namespace Tests\Unit\Services;

use App\Company;
use App\Cycle;
use App\InviteMentor;
use App\Mail\InviteMentorMail;
use App\Organization;
use App\Services\EmailBranding;
use App\Step;
use App\User;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Tests\TestCase;

/**
 * The email branding rule: program -> its organization, a specific
 * organization -> that organization, anything else -> the platform. The
 * recipient's own organization never decides.
 */
class EmailBrandingTest extends TestCase
{
    protected Organization $programOrg;

    protected Organization $recipientOrg;

    protected Cycle $program;

    protected function setUp(): void
    {
        parent::setUp();

        $this->programOrg = Organization::factory()->create(['img_url' => 'program-org-logo.png']);
        $this->recipientOrg = Organization::factory()->create(['img_url' => 'recipient-org-logo.png']);
        $this->program = Cycle::factory()->create(['organization_id' => $this->programOrg->id]);
    }

    protected function tearDown(): void
    {
        EmailBranding::end();

        parent::tearDown();
    }

    /** @test */
    public function a_program_and_anything_inside_it_resolves_to_the_organization_running_it()
    {
        $step = Step::factory()->create(['cycle_id' => $this->program->id]);
        $company = Company::factory()->create(['cycle' => $this->program->id, 'product_or_service' => 'Service']);

        $this->assertSame($this->programOrg->id, EmailBranding::organizationFor($this->programOrg)?->id);
        $this->assertSame($this->programOrg->id, EmailBranding::organizationFor($this->program)?->id);
        $this->assertSame($this->programOrg->id, EmailBranding::organizationFor($step)?->id);
        $this->assertSame($this->programOrg->id, EmailBranding::organizationFor($company)?->id);
    }

    /** @test */
    public function ids_under_known_keys_and_nested_arrays_are_resolved()
    {
        $this->assertSame($this->programOrg->id, EmailBranding::organizationFor(['cycle_id' => $this->program->id])?->id);
        $this->assertSame($this->programOrg->id, EmailBranding::organizationFor(['data' => ['organizationId' => $this->programOrg->id]])?->id);
        $this->assertNull(EmailBranding::organizationFor(['subject' => 'Hello', 'count' => 3]));
    }

    /** @test */
    public function the_recipients_own_organization_never_brands_an_email()
    {
        $recipient = User::factory()->create(['organization_id' => $this->recipientOrg->id]);

        $this->assertNull(EmailBranding::organizationFor($recipient));
        $this->assertNull(EmailBranding::forMail(new BrandingTestNotification($recipient)));
        $this->assertSame(
            $this->programOrg->id,
            EmailBranding::forMail(new BrandingTestNotification($recipient, $this->program))?->id
        );
    }

    /** @test */
    public function an_explicit_email_branding_wins_over_the_properties()
    {
        $mail = new class($this->program) extends Notification {
            public function __construct(public $cycle)
            {
            }

            public function emailBranding()
            {
                return null;
            }
        };

        $this->assertNull(EmailBranding::forMail($mail));
    }

    /** @test */
    public function an_organization_without_a_logo_falls_back_to_the_platform_logo()
    {
        // img_url is NOT NULL: an organization without a logo stores ''.
        $noLogo = Organization::factory()->create(['img_url' => '']);

        $data = EmailBranding::data($noLogo);

        $this->assertNull($data['org_logo_url']);
        $this->assertSame(EmailBranding::logo(null), $data['email_logo']);
        $this->assertSame($this->programOrg->image_url, EmailBranding::data($this->programOrg)['email_logo']['url']);
    }

    /** @test */
    public function a_sent_email_shows_the_program_logo_even_when_it_passes_the_recipients_organization()
    {
        $recipient = User::factory()->create(['organization_id' => $this->recipientOrg->id]);

        $html = $this->sendAndCapture($recipient, new BrandingTestNotification($recipient, $this->program));

        $this->assertStringContainsString('program-org-logo.png', $html);
        $this->assertStringNotContainsString('recipient-org-logo.png', $html);
        $this->assertFalse(EmailBranding::active(), 'Branding must not outlive the send.');
    }

    /** @test */
    public function a_sent_email_without_a_program_shows_the_platform_logo()
    {
        $recipient = User::factory()->create(['organization_id' => $this->recipientOrg->id]);

        $html = $this->sendAndCapture($recipient, new BrandingTestNotification($recipient));

        $this->assertStringNotContainsString('recipient-org-logo.png', $html);
        $this->assertStringNotContainsString('program-org-logo.png', $html);

        $platformLogo = EmailBranding::logo(null)['url'];
        if ($platformLogo) {
            $this->assertStringContainsString($platformLogo, $html);
        }
    }

    /** @test */
    public function a_mentor_invitation_to_one_organization_shows_that_organizations_logo()
    {
        $mail = new InviteMentorMail(new InviteMentor([
            'name'             => 'Invited Mentor',
            'scope'            => 'Organization',
            'organization_ids' => [(string) $this->programOrg->id],
        ]));

        $this->assertSame($this->programOrg->id, EmailBranding::forMail($mail)?->id);
        $this->assertStringContainsString('program-org-logo.png', $mail->render());
    }

    /** @test */
    public function a_public_or_multi_organization_mentor_invitation_shows_the_platform_logo()
    {
        $public = new InviteMentorMail(new InviteMentor(['scope' => 'Public', 'organization_ids' => []]));
        $several = new InviteMentorMail(new InviteMentor([
            'scope'            => 'Organization',
            'organization_ids' => [$this->programOrg->id, $this->recipientOrg->id],
        ]));

        $this->assertNull(EmailBranding::forMail($public));
        $this->assertNull(EmailBranding::forMail($several));
    }

    /**
     * Run the real notification pipeline and return the rendered HTML,
     * blocking delivery.
     */
    protected function sendAndCapture(User $recipient, Notification $notification): string
    {
        $html = '';

        Event::listen(MessageSending::class, function (MessageSending $event) use (&$html) {
            $html = (string) $event->message->getHtmlBody();

            return false;
        });

        NotificationFacade::sendNow($recipient, $notification, ['mail']);

        return $html;
    }
}

/**
 * Renders the shared layout and, like the old per-email code, passes the
 * recipient's own organization logo.
 */
class BrandingTestNotification extends Notification
{
    public function __construct(protected User $recipient, public ?Cycle $cycle = null)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Branding test')
            ->view('emails.layout.master', [
                'org_logo_url' => url('/organizations/logo/recipient-org-logo.png'),
            ]);
    }
}
