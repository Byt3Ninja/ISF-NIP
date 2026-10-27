<?php

namespace Tests\Unit\Timezone;

use App\Support\TimezoneSelfTest;
use Tests\TestCase;

/**
 * GV-9566: the same scenarios `php artisan timezone:doctor` replays on a server
 * (join windows, panel locks, viewer display, the PlatformDateTime cast, Zoom
 * and Teams payloads, training instants) across storage x platform x viewer zones.
 */
class TimezoneRulesTest extends TestCase
{
    public function test_every_timezone_rule_holds(): void
    {
        $results = (new TimezoneSelfTest())->run();
        $failures = array_filter($results, fn ($r) => !$r['ok']);

        $this->assertNotEmpty($results);
        $this->assertSame([], array_values(array_map(
            fn ($r) => "[{$r['group']}] {$r['name']}: {$r['detail']}",
            $failures
        )));
    }

    public function test_doctor_command_passes_on_this_environment(): void
    {
        $this->artisan('timezone:doctor', ['--meetings' => 0])->assertExitCode(0);
    }
}
