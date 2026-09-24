<?php

namespace App\Console\Commands;

use App\Services\MeetingWindowService;
use App\Services\PanelWindowService;
use App\Services\TimezoneService;
use App\Support\TimezoneSelfTest;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Read-only diagnosis of the timezone setup on THIS server (GV-9566).
 *
 * "Works locally, fails on the server" timing reports kept coming back because
 * a server's zones differed from a developer machine's. This prints every zone
 * involved, flags mismatches, replays the timezone rules against this server's
 * own PHP and MySQL, and lists when the next meetings actually open.
 *
 * Exit code: 0 = all checks pass, 1 = a rule check failed or the setup is broken.
 */
class TimezoneDoctor extends Command
{
    protected $signature = 'timezone:doctor
                            {--meetings=10 : How many upcoming screening panels to list}
                            {--details : Print every passing check, not only failures}';

    protected $description = 'Read-only check of the timezone setup and meeting windows on this server';

    private int $problems = 0;

    public function handle(): int
    {
        $this->zones();
        $this->clocks();
        $this->selfTest();
        $this->upcomingMeetings((int) $this->option('meetings'));

        $this->newLine();
        if ($this->problems > 0) {
            $this->error("{$this->problems} problem(s) found.");
            return self::FAILURE;
        }

        $this->info('All timezone checks passed.');
        return self::SUCCESS;
    }

    private function zones(): void
    {
        $this->line('<options=bold>1. Zones</>');

        $settingsZone = DB::table('settings')->where('id', 1)->value('timezone');
        $mysql = DB::selectOne('SELECT @@global.time_zone AS g, @@session.time_zone AS s, @@system_time_zone AS sys');
        $namedZones = DB::selectOne("SELECT CONVERT_TZ('2026-01-01 00:00:00', 'UTC', 'Africa/Cairo') AS probe")->probe !== null;

        $rows = [
            ['storage (APP_TIMEZONE)', config('app.timezone'), 'writes/reads created_at, *_at; must never change after install'],
            ['PHP default', date_default_timezone_get(), 'should equal storage'],
            ['General Settings timezone', $settingsZone ?: '(empty)', TimezoneService::isValid($settingsZone) ? 'valid' : 'empty/invalid -> falls back to storage'],
            ['platform (resolved)', TimezoneService::platform(), 'zone admins type meeting times in'],
            ['MySQL global / session', "{$mysql->g} / {$mysql->s}", "SYSTEM = {$mysql->sys}"],
            ['MySQL named zones loaded', $namedZones ? 'yes' : 'no', $namedZones ? '' : 'needed to pin the DB session to a named zone'],
            ['DB connection timezone', var_export(config('database.connections.' . config('database.default') . '.timezone'), true), 'null = MySQL session uses SYSTEM'],
        ];
        $this->table(['Setting', 'Value', 'Meaning'], $rows);

        $organizations = DB::table('organizations')->whereNotNull('time_zone')->where('time_zone', '!=', '')->get(['id', 'name_en', 'time_zone']);
        if ($organizations->isNotEmpty()) {
            $this->table(['Org', 'Name', 'time_zone', 'Valid'], $organizations->map(fn ($o) => [
                $o->id, $o->name_en, $o->time_zone, TimezoneService::isValid($o->time_zone) ? 'yes' : 'NO -> uses platform',
            ])->all());
        }

        if (date_default_timezone_get() !== config('app.timezone')) {
            $this->problem('PHP default timezone differs from APP_TIMEZONE.');
        }
        if ($settingsZone && !TimezoneService::isValid($settingsZone)) {
            $this->problem("General Settings timezone \"$settingsZone\" is not a valid zone.");
        }
    }

    private function clocks(): void
    {
        $this->newLine();
        $this->line('<options=bold>2. Clocks</>');

        $db = DB::selectOne('SELECT UTC_TIMESTAMP() AS utc, NOW() AS now_');
        $phpUtc = Carbon::now('UTC');
        $skew = abs($phpUtc->diffInSeconds(Carbon::parse($db->utc, 'UTC'), false));

        // Offset MySQL applies to TIMESTAMP columns vs the offset Laravel writes them in.
        $mysqlOffset = Carbon::parse($db->now_, 'UTC')->diffInMinutes(Carbon::parse($db->utc, 'UTC'), false) * -1;
        $appOffset = Carbon::now(config('app.timezone'))->utcOffset();

        $this->table(['Clock', 'Value'], [
            ['PHP now (UTC)', $phpUtc->toDateTimeString()],
            ['MySQL UTC_TIMESTAMP()', $db->utc],
            ['PHP vs MySQL skew', "{$skew}s"],
            ['platform now', TimezoneService::platformNow()->toDateTimeString() . ' ' . TimezoneService::platform()],
            ['Laravel write offset (storage)', $this->offset($appOffset)],
            ['MySQL session offset', $this->offset($mysqlOffset)],
        ]);

        if ($skew > 5) {
            $this->problem("PHP and MySQL clocks differ by {$skew}s (check NTP on both hosts).");
        }
        if ($appOffset !== $mysqlOffset) {
            $this->warn('  ! Laravel writes TIMESTAMP columns at ' . $this->offset($appOffset) . ' but MySQL reads them at '
                . $this->offset($mysqlOffset) . ': the instants MySQL records are shifted. PHP reads back what it wrote, '
                . 'but MySQL NOW()/UNIX_TIMESTAMP() on those columns are off. Pin the DB session zone to APP_TIMEZONE.');
        }
    }

    private function selfTest(): void
    {
        $this->newLine();
        $this->line('<options=bold>3. Rule checks (replayed on this server, no writes)</>');

        try {
            $results = (new TimezoneSelfTest())->run();
        } catch (Throwable $e) {
            $this->problem('Self-test crashed: ' . $e->getMessage());
            return;
        }

        $failed = array_filter($results, fn ($r) => !$r['ok']);
        $byGroup = collect($results)->groupBy('group')->map(fn ($g) => [
            $g->first()['group'], $g->where('ok', true)->count(), $g->where('ok', false)->count(),
        ])->values()->all();
        $this->table(['Group', 'Pass', 'Fail'], $byGroup);

        foreach ($results as $result) {
            if (!$result['ok'] || $this->option('details')) {
                $this->line(($result['ok'] ? '  <info>PASS</info> ' : '  <error>FAIL</error> ') . "[{$result['group']}] {$result['name']}  {$result['detail']}");
            }
        }

        if ($failed) {
            $this->problem(count($failed) . ' of ' . count($results) . ' rule checks failed.');
        }
    }

    private function upcomingMeetings(int $limit): void
    {
        if ($limit <= 0) {
            return;
        }

        $this->newLine();
        $this->line("<options=bold>4. Next {$limit} screening panels (when Join actually opens)</>");

        $panels = DB::table('screenings')
            ->whereNull('deleted_at')
            ->whereNotNull('session_date')
            ->whereNotNull('start_time')
            ->where('session_date', '>=', TimezoneService::platformToday())
            ->orderBy('session_date')->orderBy('start_time')
            ->get(['id', 'step_id', 'panel', 'users', 'session_date', 'start_time', 'end_time', 'interval', 'meeting_link'])
            ->unique(fn ($row) => $row->step_id . '-' . $row->panel)
            ->take($limit);

        if ($panels->isEmpty()) {
            $this->line('  (none scheduled)');
            return;
        }

        $rows = $panels->map(function ($row) {
            $zone = TimezoneService::forStep($row->step_id);
            $window = PanelWindowService::fromScreening($row);
            $start = $window['start'] ?? null;
            $end = $window['end'] ?? null;

            $state = !$start ? '-' : (MeetingWindowService::hasExpired($end)
                ? 'completed'
                : (MeetingWindowService::isOpen($start, $end, 'judge') ? 'judges can join' : 'not open yet'));

            return [
                "{$row->step_id}/{$row->panel}",
                $zone,
                $start ? $start->format('Y-m-d H:i') . ' - ' . $end->format('H:i') : '-',
                $start ? $start->copy()->subHours(MeetingWindowService::EARLY_JUDGE_HOURS)->format('Y-m-d H:i') : '-',
                $start ? $start->copy()->subMinutes(MeetingWindowService::EARLY_FOUNDER_MINUTES)->format('Y-m-d H:i') : '-',
                $end ? $end->copy()->addMinutes(MeetingWindowService::LATE_MINUTES)->format('Y-m-d H:i') : '-',
                $start ? $start->copy()->utc()->format('Y-m-d H:i') . 'Z' : '-',
                empty($row->meeting_link) ? 'NO LINK' : 'ok',
                $state,
            ];
        })->all();

        $this->table(['Step/panel', 'Platform zone', 'Slot (platform)', 'Judges open', 'Founders open', 'Closes', 'Start (UTC)', 'Link', 'Now'], $rows);
    }

    private function offset(int $minutes): string
    {
        return sprintf('%s%02d:%02d', $minutes < 0 ? '-' : '+', intdiv(abs($minutes), 60), abs($minutes) % 60);
    }

    private function problem(string $message): void
    {
        $this->problems++;
        $this->error('  ' . $message);
    }
}
