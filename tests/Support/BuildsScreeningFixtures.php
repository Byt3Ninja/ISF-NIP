<?php

namespace Tests\Support;

use App\Services\TimezoneService;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Dummy program -> final-screening step -> startup -> judges -> panels, written
 * straight to the tables the dashboards read. Everything is created inside the
 * test's transaction (see Tests\TestCase) and rolled back afterwards.
 */
trait BuildsScreeningFixtures
{
    /** Platform zone as an admin would set it in General Settings. */
    protected function setPlatformZone(?string $zone): void
    {
        DB::table('settings')->where('id', 1)->update(['timezone' => $zone]);

        // Absent when these tests are replayed against the pre-GV-9566 code.
        if (class_exists(TimezoneService::class)) {
            TimezoneService::forgetPlatform();
        }
    }

    /** Simulate a server whose APP_TIMEZONE differs from the admins' clock. */
    protected function setStorageZone(string $zone): void
    {
        config(['app.timezone' => $zone]);
        date_default_timezone_set($zone);
    }

    protected function makeUser(string $role, ?string $timezone, array $extra = []): User
    {
        $tag = Str::lower(Str::random(8));

        $id = DB::table('users')->insertGetId(array_merge([
            'first_name_en' => ucfirst($role) . $tag,
            'last_name_en' => 'Dummy',
            'first_name_ar' => ucfirst($role) . $tag,
            'last_name_ar' => 'Dummy',
            'email' => "gv9566-{$role}-{$tag}@example.invalid",
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'timezone' => $timezone,
            'must_change_password' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $extra));

        // Written like the app's own rows (model_type is required on this schema).
        DB::table('model_has_roles')->insert([
            'role_id' => DB::table('roles')->where('name', $role)->value('id'),
            'model_type' => User::class,
            'model_id' => $id,
        ]);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        return User::findOrFail($id);
    }

    /**
     * @return array{organization: int, cycle: int, step: int, company: int, founder: User}
     */
    protected function makeFinalScreeningProgram(?string $organizationZone = null): array
    {
        $organization = DB::table('organizations')->insertGetId([
            'name_en' => 'GV-9566 Dummy Org',
            'name_ar' => 'GV-9566 Dummy Org',
            'time_zone' => $organizationZone,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cycle = DB::table('cycles')->insertGetId([
            'organization_id' => $organization,
            'title' => 'GV-9566 Dummy Program',
            'type' => 'General_Program',
            'start' => '2026-09-01',
            'end' => '2026-10-31',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $step = DB::table('steps')->insertGetId([
            'cycle_id' => $cycle,
            'title' => 'GV-9566 Dummy Final Screening',
            'stype' => 5,
            'arr' => 1,
            'from' => '2026-09-01',
            'to' => '2026-10-31',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $company = DB::table('companies')->insertGetId([
            'name_en' => 'GV9566 Dummy Startup',
            'name_ar' => 'GV9566 Dummy Startup',
            'address_en' => 'Dummy address',
            'est_date' => '2024-01-01',
            'stage' => 1,
            'cycle' => $cycle,
            'step' => $step,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Inserted without model events: open its enrollment the way the application would.
        \App\CompanyEnrollment::syncFromPointer(\App\Company::findOrFail($company), null);

        // A complete founder profile, so the dashboard does not redirect to the profile form.
        $founder = $this->makeUser('registered', 'Africa/Cairo', [
            'company_id' => $company,
            'phone' => '1000000000',
            'country_id' => DB::table('countries')->value('id'),
            'address' => 'Dummy address',
        ]);

        return compact('organization', 'cycle', 'step', 'company', 'founder');
    }

    /**
     * One final panel = one `screenings` row per judge (+ the per-company finalscreenings row).
     *
     * @param  int[]  $judgeIds
     */
    protected function makeFinalPanel(int $step, int $company, int $panel, array $judgeIds, string $date, string $start, string $end, ?string $link): void
    {
        foreach ($judgeIds as $judgeId) {
            DB::table('screenings')->insert([
                'step_id' => $step,
                'user_id' => $judgeId,
                'panel' => $panel,
                'users' => json_encode([$company]),
                'session_date' => $date,
                'start_time' => $start,
                'end_time' => $end,
                'meeting_link' => $link,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('finalscreenings')->updateOrInsert(
            ['step_id' => $step, 'company_id' => $company],
            ['judges' => json_encode($judgeIds), 'join_url' => $link, 'datetime' => "$date $start", 'created_at' => now(), 'updated_at' => now()]
        );
    }

    /** The first <tr> of the page whose markup contains $needle. */
    protected function rowHtml(string $html, string $needle): ?string
    {
        foreach (preg_split('#<tr[\s>]#i', $html) as $chunk) {
            $row = explode('</tr>', $chunk)[0];
            if (str_contains($row, $needle)) {
                return $row;
            }
        }

        return null;
    }

    /** The same row as plain text, cells separated by "|". */
    protected function rowText(string $html, string $needle): ?string
    {
        $row = $this->rowHtml($html, $needle);

        return $row === null ? null : trim(preg_replace('/\s+/', ' ', strip_tags(str_replace('<td', ' | <td', $row))));
    }
}
