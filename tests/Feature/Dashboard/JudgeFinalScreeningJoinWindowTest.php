<?php

namespace Tests\Feature\Dashboard;

use Carbon\Carbon;
use Tests\Support\BuildsScreeningFixtures;
use Tests\TestCase;

/**
 * GV-9566: "Join Now is not shown one day before the Final Screening meeting".
 *
 * Reproduces the reported setup with dummy data: the server runs on UTC
 * (APP_TIMEZONE), the admins schedule in Cairo (General Settings), the judge has
 * no profile timezone, and the meeting is 14-09-2026 01:20-01:25 (5-minute
 * interval). The screenshot was taken at 13-09 01:27 Cairo, 23h53m before start.
 */
class JudgeFinalScreeningJoinWindowTest extends TestCase
{
    use BuildsScreeningFixtures;

    private const LINK = 'https://example.invalid/gv9566-final-panel';

    private string $originalZone;
    private array $program;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalZone = date_default_timezone_get();
        $this->setStorageZone('UTC');
        $this->setPlatformZone('Africa/Cairo');

        $this->program = $this->makeFinalScreeningProgram();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        $this->setStorageZone($this->originalZone);

        parent::tearDown();
    }

    private function judgeRow(\App\User $judge, string $cairoNow): string
    {
        Carbon::setTestNow(Carbon::parse($cairoNow, 'Africa/Cairo'));

        $html = $this->actingAs($judge)->get('/dashboard')->assertOk()->getContent();
        $row = $this->rowText($html, 'GV9566 Dummy Startup');

        $this->assertNotNull($row, 'The final-screening row for the dummy startup is not on the judge dashboard.');

        // "JOIN" marks a real Join button: the row links to the meeting.
        return str_contains((string) $this->rowHtml($html, 'GV9566 Dummy Startup'), 'href="' . self::LINK . '"')
            ? $row . ' [JOIN]'
            : $row;
    }

    private function schedulePanel(array $judgeIds): void
    {
        $this->makeFinalPanel(
            $this->program['step'], $this->program['company'], 1, $judgeIds,
            '2026-09-14', '01:20:00', '01:30:00', self::LINK
        );
        \Illuminate\Support\Facades\DB::table('screenings')->where('step_id', $this->program['step'])->update(['interval' => 5]);
    }

    public function test_reported_case_join_shows_23h53m_before_start_for_a_judge_without_a_timezone(): void
    {
        $judge = $this->makeUser('judger', null);
        $this->schedulePanel([$judge->id]);

        $row = $this->judgeRow($judge, '2026-09-13 01:27');

        $this->assertStringContainsString('14-09-2026', $row);
        $this->assertStringContainsString('01:20 AM - 01:25 AM', $row);
        $this->assertStringContainsString('[JOIN]', $row);
        $this->assertStringNotContainsString('Not Started', $row);
        $this->assertStringNotContainsString('Meeting link unavailable', $row);
    }

    public function test_join_window_edges_24h_before_start_until_60_minutes_after_end(): void
    {
        $judge = $this->makeUser('judger', null);
        $this->schedulePanel([$judge->id]);

        $this->assertStringContainsString('Session Not Started Yet', $this->judgeRow($judge, '2026-09-13 01:19'));
        $this->assertStringContainsString('[JOIN]', $this->judgeRow($judge, '2026-09-13 01:20'));
        $this->assertStringContainsString('[JOIN]', $this->judgeRow($judge, '2026-09-14 02:25'));
        $this->assertStringContainsString('Session Completed', $this->judgeRow($judge, '2026-09-14 02:26'));
    }

    public function test_every_judge_gets_join_at_the_same_real_moment_and_sees_their_own_clock(): void
    {
        $cairoJudge = $this->makeUser('judger', 'Africa/Cairo');
        $kolkataJudge = $this->makeUser('judger', 'Asia/Kolkata');
        $this->schedulePanel([$cairoJudge->id, $kolkataJudge->id]);

        // One minute before the window: closed for both, whatever their profile zone.
        $this->assertStringContainsString('Session Not Started Yet', $this->judgeRow($cairoJudge, '2026-09-13 01:19'));
        $this->assertStringContainsString('Session Not Started Yet', $this->judgeRow($kolkataJudge, '2026-09-13 01:19'));

        // Window open: both can join; each reads the time in their own zone.
        $cairoRow = $this->judgeRow($cairoJudge, '2026-09-13 01:27');
        $kolkataRow = $this->judgeRow($kolkataJudge, '2026-09-13 01:27');

        $this->assertStringContainsString('[JOIN]', $cairoRow);
        $this->assertStringContainsString('[JOIN]', $kolkataRow);
        $this->assertStringContainsString('14-09-2026', $cairoRow);
        $this->assertStringContainsString('01:20 AM - 01:25 AM', $cairoRow);
        $this->assertStringContainsString('14-09-2026', $kolkataRow);
        $this->assertStringContainsString('03:50 AM - 03:55 AM', $kolkataRow);
    }

    public function test_the_general_settings_timezone_drives_the_window_not_app_timezone(): void
    {
        // Same typed slot, but the admins now work in Kolkata: 01:20 Kolkata = 22:50 Cairo on the 13th,
        // so the judge window opens at 22:50 Cairo on the 12th.
        $this->setPlatformZone('Asia/Kolkata');
        $judge = $this->makeUser('judger', null);
        $this->schedulePanel([$judge->id]);

        $this->assertStringContainsString('Session Not Started Yet', $this->judgeRow($judge, '2026-09-12 22:49'));
        $this->assertStringContainsString('[JOIN]', $this->judgeRow($judge, '2026-09-12 22:50'));
    }
}
