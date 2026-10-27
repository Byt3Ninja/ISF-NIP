<?php

namespace Tests\Unit\Notifications;

use App\Cycle;
use App\Notifications\InviteToHackathon;
use App\Settings;
use App\Team_member;
use App\TeamFormation;
use App\User;
use Illuminate\Notifications\AnonymousNotifiable;
use Tests\TestCase;

/**
 * GV-3730: the invite email renders in the platform default language with the
 * ticket's copy. Guards against the view referencing translation keys that
 * are missing from resources/lang (the email then showed raw key names).
 */
class InviteToHackathonTest extends TestCase
{
    public static function copyProvider(): array
    {
        return [
            'en' => ['en', 'You’re invited to join a hackathon team', [
                'Hello Nour Adel,',
                'You have been invited to join Alpha Team in AI Innovation Hackathon.',
                'Please review the invitation and choose whether to accept or reject it before the link expires.',
                'Invitation expires in:',
                'View Invitation',
                'Best regards,',
            ]],
            'ar' => ['ar', 'تمت دعوتك للانضمام إلى فريق في الهاكاثون', [
                'مرحباً نور عادل،',
                'تمت دعوتك للانضمام إلى فريق Alpha Team في هاكاثون الابتكار.',
                'يرجى مراجعة الدعوة واختيار قبولها أو رفضها قبل انتهاء صلاحية الرابط.',
                'تنتهي صلاحية الدعوة خلال:',
                'عرض الدعوة',
                'مع أطيب التحيات،',
            ]],
        ];
    }

    /** @dataProvider copyProvider */
    public function test_registered_founder_gets_ticket_copy_in_platform_language(string $locale, string $subject, array $lines)
    {
        $this->setPlatformLanguage($locale);

        $founder = new User();
        $founder->first_name_en = 'Nour';
        $founder->last_name_en = 'Adel';
        $founder->first_name_ar = 'نور';
        $founder->last_name_ar = 'عادل';

        $mail = $this->notification()->toMail($founder);
        $html = html_entity_decode((string) $mail->render());

        $this->assertSame($subject, $mail->subject);
        foreach ($lines as $line) {
            $this->assertStringContainsString($line, $html);
        }
        $this->assertStringContainsString('dashboard/hackathon/details/7', $html);
        $this->assertStringNotContainsString('email.hackathon_invite_email', $html);
    }

    /** @dataProvider copyProvider */
    public function test_email_only_invitee_gets_register_cta(string $locale)
    {
        $this->setPlatformLanguage($locale);

        $mail = $this->notification()->toMail((new AnonymousNotifiable)->route('mail', 'new@example.com'));
        $html = html_entity_decode((string) $mail->render());

        $this->assertStringContainsString(__('email.hackathon_invite_email.new_user_hint', [], $locale), $html);
        $this->assertStringContainsString(__('email.hackathon_invite_email.cta_new_user', [], $locale), $html);
        $this->assertStringContainsString(url('/register'), $html);
        $this->assertStringNotContainsString('Hello ,', $html);
        $this->assertStringNotContainsString('email.hackathon_invite_email', $html);
    }

    private function setPlatformLanguage(string $locale): void
    {
        $setting = Settings::first();
        if (! $setting) {
            $this->markTestSkipped('No settings row in this database.');
        }

        // Not mass-assignable on Settings. Rolled back by the TestCase transaction.
        $setting->Default_Language = $locale;
        $setting->save();
    }

    private function notification(): InviteToHackathon
    {
        $cycle = new Cycle();
        $cycle->title = 'AI Innovation Hackathon';
        $cycle->title_ar = 'هاكاثون الابتكار';

        $team = new TeamFormation();
        $team->name = 'Alpha Team';
        $team->cycle_id = 7;
        $team->setRelation('cycle', $cycle);

        $leader = new User();
        $leader->first_name_en = 'Jane';
        $leader->last_name_en = 'Doe';

        $member = new Team_member();
        $member->expire_at = now()->addHours(24);

        return new InviteToHackathon($leader, $team, $member);
    }
}
