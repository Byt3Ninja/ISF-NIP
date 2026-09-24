<?php

namespace Tests\Feature\Dashboard;

use Carbon\Carbon;
use Tests\Support\BuildsScreeningFixtures;
use Tests\TestCase;

/**
 * GV-9620 regression (proven fixed before GV-9566), replayed with dummy data
 * through the GV-9566 changes:
 *   1. a final panel whose online-meeting link failed to generate still shows its
 *      row, with "Meeting link unavailable" instead of a Join link, and does not
 *      hide the other panels;
 *   2. a startup that sits in several final panels sees every one of them.
 */
class FounderFinalSessionPanelsTest extends TestCase
{
    use BuildsScreeningFixtures;

    private const LINK = 'https://example.invalid/gv9620-panel-1';

    private array $program;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setPlatformZone('Africa/Cairo');
        $this->program = $this->makeFinalScreeningProgram();

        $judgeA = $this->makeUser('judger', null);
        $judgeB = $this->makeUser('judger', null);

        // Panel 1: link generated.  Panel 2: link generation failed (NULL).
        $this->makeFinalPanel($this->program['step'], $this->program['company'], 1, [$judgeA->id], '2026-09-20', '10:00:00', '10:30:00', self::LINK);
        $this->makeFinalPanel($this->program['step'], $this->program['company'], 2, [$judgeB->id], '2026-09-20', '12:00:00', '12:30:00', null);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function founderDashboard(string $cairoNow): string
    {
        Carbon::setTestNow(Carbon::parse($cairoNow, 'Africa/Cairo'));

        return $this->actingAs($this->program['founder'])->get('/dashboard')->assertOk()->getContent();
    }

    public function test_every_final_panel_is_listed_including_the_one_without_a_link(): void
    {
        $html = $this->founderDashboard('2026-09-20 09:55');

        $panel1 = $this->rowText($html, '10:00 AM - 10:30 AM');
        $panel2 = $this->rowText($html, '12:00 PM - 12:30 PM');

        $this->assertNotNull($panel1, 'Panel 1 is missing from the founder dashboard.');
        $this->assertNotNull($panel2, 'Panel 2 (no meeting link) is missing: the GV-9620 table-hiding bug is back.');
        $this->assertStringNotContainsString('will start soon', strip_tags($html));
    }

    public function test_the_linkless_panel_says_link_unavailable_and_never_offers_join(): void
    {
        // 11:55 Cairo: inside panel 2's founder window (opens 10 minutes before).
        $html = $this->founderDashboard('2026-09-20 11:55');
        $panel2 = $this->rowHtml($html, '12:00 PM - 12:30 PM');

        $this->assertNotNull($panel2);
        $this->assertStringContainsString('Meeting link unavailable', strip_tags($panel2));
        $this->assertStringNotContainsString('Join now', strip_tags($panel2));
    }

    public function test_the_panel_with_a_link_follows_the_founder_join_window(): void
    {
        $this->assertStringContainsString('Session Not Started Yet', $this->rowText($this->founderDashboard('2026-09-20 09:49'), '10:00 AM - 10:30 AM'));

        $open = $this->rowHtml($this->founderDashboard('2026-09-20 09:50'), '10:00 AM - 10:30 AM');
        $this->assertStringContainsString('href="' . self::LINK . '"', $open);
        $this->assertStringContainsString('Join now', strip_tags($open));

        $this->assertStringContainsString('Session Completed', $this->rowText($this->founderDashboard('2026-09-20 11:31'), '10:00 AM - 10:30 AM'));
    }
}
