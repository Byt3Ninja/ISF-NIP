<?php

namespace App\Support;

use App\Casts\PlatformDateTime;
use App\MentorshipRequest;
use App\Services\MeetingWindowService;
use App\Services\PanelWindowService;
use App\Services\ScreeningSlotService;
use App\Services\TeamsService;
use App\Services\TimezoneService;
use App\Services\ZoomService;
use App\User;
use Carbon\Carbon;
use Throwable;

/**
 * Executable proof of the GV-9566 timezone rules.
 *
 * Shared by `php artisan timezone:doctor` (any server) and the PHPUnit suite, so
 * both run the exact same scenarios. It never writes to the database: zones
 * are passed explicitly, the clock is frozen with Carbon::setTestNow(), and the
 * app zone is switched in memory only -- all restored afterwards.
 */
class TimezoneSelfTest
{
    /** Zones the scenarios are replayed under, on top of the live configuration. */
    public const STORAGE_ZONES = ['UTC', 'Africa/Cairo', 'Europe/Berlin'];
    public const PLATFORM_ZONES = ['Africa/Cairo', 'Asia/Kolkata', 'America/New_York'];
    public const VIEWER_ZONES = [null, 'Africa/Cairo', 'Asia/Kolkata', 'America/New_York'];

    /** @var array<int, array{group: string, name: string, ok: bool, detail: string}> */
    private array $results = [];

    /** @return array<int, array{group: string, name: string, ok: bool, detail: string}> */
    public function run(): array
    {
        $this->results = [];
        $originalZone = date_default_timezone_get();
        $originalConfig = config('app.timezone');

        try {
            foreach (array_unique(array_merge([$originalConfig], self::STORAGE_ZONES)) as $storage) {
                config(['app.timezone' => $storage]);
                date_default_timezone_set($storage);

                foreach (self::PLATFORM_ZONES as $platform) {
                    $this->meetingWindows($storage, $platform);
                    $this->panelWindows($storage, $platform);
                    $this->display($storage, $platform);
                    $this->cast($storage, $platform);
                    $this->providers($storage, $platform);
                }
            }
            $this->trainingInstants();
        } finally {
            Carbon::setTestNow();
            config(['app.timezone' => $originalConfig]);
            date_default_timezone_set($originalZone);
        }

        return $this->results;
    }

    /** Judge/founder Join windows open and close at the same real instant for every viewer. */
    private function meetingWindows(string $storage, string $platform): void
    {
        $slot = ScreeningSlotService::slotFor('2026-09-14', '01:20:00', 5, 0, '01:30:00', $platform);
        $start = Carbon::parse('2026-09-14 01:20', $platform);
        $end = Carbon::parse('2026-09-14 01:25', $platform);

        $edges = [
            ['judge opens 24h before', 'judge', $start->copy()->subHours(24), true],
            ['judge closed 24h+1m before', 'judge', $start->copy()->subHours(24)->subMinute(), false],
            ['founder opens 10m before', 'founder', $start->copy()->subMinutes(10), true],
            ['founder closed 11m before', 'founder', $start->copy()->subMinutes(11), false],
            ['open until end+60m', 'judge', $end->copy()->addMinutes(60), true],
            ['closed at end+61m', 'judge', $end->copy()->addMinutes(61), false],
        ];

        foreach ($edges as [$name, $audience, $now, $expected]) {
            Carbon::setTestNow($now);
            // A wall-clock string must resolve to the same instant as the slot Carbon.
            $fromSlot = MeetingWindowService::isOpen($slot['start'], $slot['end'], $audience);
            $fromString = MeetingWindowService::isOpen('2026-09-14 01:20:00', '2026-09-14 01:25:00', $audience, $platform);

            $this->check('join window', "$name [storage $storage, platform $platform]",
                $fromSlot === $expected && $fromString === $expected,
                'slot=' . var_export($fromSlot, true) . ' string=' . var_export($fromString, true) . ' expected=' . var_export($expected, true));
        }

        Carbon::setTestNow($end->copy()->addSecond());
        $this->check('join window', "hasEnded right after end [storage $storage, platform $platform]",
            MeetingWindowService::hasEnded($slot['end']) && !MeetingWindowService::hasExpired($slot['end']), '');
    }

    /** Panel history / past-start locks use the same instants. */
    private function panelWindows(string $storage, string $platform): void
    {
        $window = PanelWindowService::build('2026-09-14', '01:20:00', 5, null, 2, $platform);

        Carbon::setTestNow($window['end']->copy()->subSecond());
        $before = PanelWindowService::isPast($window);
        Carbon::setTestNow($window['end']->copy()->addSecond());
        $after = PanelWindowService::isPast($window);
        Carbon::setTestNow($window['start']->copy()->subSecond());
        $startsInPast = PanelWindowService::startsInPast($window);

        $this->check('panel window', "history + past-start edges [storage $storage, platform $platform]",
            !$before && $after && !$startsInPast,
            "before=$before after=$after startsInPast=$startsInPast");
    }

    /** The viewer's zone changes the displayed time only. */
    private function display(string $storage, string $platform): void
    {
        $slot = ScreeningSlotService::slotFor('2026-09-14', '01:20:00', 5, 0, '01:30:00', $platform);

        foreach (self::VIEWER_ZONES as $viewerZone) {
            $viewer = new User();
            $viewer->timezone = $viewerZone;

            $shown = ScreeningSlotService::formatRange(ScreeningSlotService::forViewer($slot, $viewer));
            $zone = $viewerZone ?? $platform;
            $expected = $slot['start']->copy()->setTimezone($zone)->format('h:i A') . ' - ' . $slot['end']->copy()->setTimezone($zone)->format('h:i A');

            $this->check('display', 'viewer ' . ($viewerZone ?? '(none)') . " [storage $storage, platform $platform]",
                $shown === $expected, "shown=$shown expected=$expected");

            $pair = TimezoneService::wallClockForViewer('2026-09-14', '02:00 PM', $viewer, $platform);
            $expectedPair = Carbon::parse('2026-09-14 14:00', $platform)->setTimezone($zone);
            $this->check('display', 'email date/time for viewer ' . ($viewerZone ?? '(none)') . " [platform $platform]",
                $pair === ['date' => $expectedPair->format('Y-m-d'), 'time' => $expectedPair->format('h:i A')],
                json_encode($pair));
        }
    }

    /** PlatformDateTime reads wall-clock in the platform zone and keeps typed strings verbatim. */
    private function cast(string $storage, string $platform): void
    {
        $cast = new PlatformDateTime('Y-m-d H:i');
        $model = new MentorshipRequest();

        $read = TimezoneService::wallClock('2026-09-14 01:20:00', $platform);
        $this->check('cast', "wall-clock is an instant in the platform zone [storage $storage, platform $platform]",
            $read->equalTo(Carbon::parse('2026-09-14 01:20', $platform)) && $read->format('Y-m-d H:i') === '2026-09-14 01:20',
            $read->toIso8601String());

        $stored = $cast->set($model, 'zoom_date', '2026-09-14 01:20', []);
        $this->check('cast', "typed string stored verbatim [storage $storage]", $stored === '2026-09-14 01:20:00', (string) $stored);

        $serialized = $cast->serialize($model, 'zoom_date', Carbon::parse('2026-09-14 01:20', TimezoneService::platform())->toJSON(), []);
        $this->check('cast', "serialized format unchanged [storage $storage]", $serialized === '2026-09-14 01:20', (string) $serialized);

        $utcInstant = $cast->set($model, 'zoom_date', Carbon::parse('2026-09-13T22:20:00+00:00'), []);
        $expected = Carbon::parse('2026-09-13 22:20', 'UTC')->setTimezone(TimezoneService::platform())->format('Y-m-d H:i:s');
        $this->check('cast', "an instant with an offset is converted to platform wall-clock [storage $storage]",
            $utcInstant === $expected, "stored=$utcInstant expected=$expected");
    }

    /** Zoom gets platform wall-clock + zone; Teams gets the UTC instant. */
    private function providers(string $storage, string $platform): void
    {
        try {
            $zoom = (new \ReflectionClass(ZoomService::class))->newInstanceWithoutConstructor();
            $teams = (new \ReflectionClass(TeamsService::class))->newInstanceWithoutConstructor();
            $instant = Carbon::parse('2026-09-14 01:20', $platform);

            $zoomWall = $zoom->toZoomTimeFormat('2026-09-14 01:20:00', $platform);
            $zoomUnix = $zoom->toZoomTimeFormat($instant->timestamp, $platform);
            $teamsWall = $teams->toZoomTimeFormat('2026-09-14 01:20:00', null, $platform);

            $this->check('providers', "Zoom wall-clock + unix timestamp [storage $storage, platform $platform]",
                $zoomWall === '2026-09-14T01:20:00' && $zoomUnix === '2026-09-14T01:20:00', "wall=$zoomWall unix=$zoomUnix");
            $this->check('providers', "Teams UTC instant [storage $storage, platform $platform]",
                $teamsWall === $instant->copy()->utc()->toIso8601String(), (string) $teamsWall);
        } catch (Throwable $e) {
            $this->check('providers', "meeting providers [storage $storage, platform $platform]", false, $e->getMessage());
        }
    }

    /** Training timestamps are instants whatever the zones. */
    private function trainingInstants(): void
    {
        $ts = Carbon::parse('2026-09-13 10:00', 'UTC')->timestamp;

        Carbon::setTestNow(Carbon::createFromTimestamp($ts)->subMinutes(10));
        $open = MeetingWindowService::isOpen((string) $ts, (string) ($ts + 3600), 'founder');
        Carbon::setTestNow(Carbon::createFromTimestamp($ts)->subMinutes(11));
        $closed = MeetingWindowService::isOpen((string) $ts, (string) ($ts + 3600), 'founder');

        $this->check('training', 'unix timestamps are instants', $open && !$closed, "open=$open closed=$closed");
    }

    private function check(string $group, string $name, bool $ok, string $detail): void
    {
        $this->results[] = compact('group', 'name', 'ok', 'detail');
    }
}
