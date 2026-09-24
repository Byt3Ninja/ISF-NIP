<?php

namespace App\Services;

use App\Organization;
use App\Settings;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The one answer to "which timezone" (GV-9566).
 *
 * Time handling used to pick a zone ad hoc -- the server clock, the viewer's
 * profile, a hard-coded 'Africa/Cairo', or MySQL's SYSTEM zone -- so a server
 * whose APP_TIMEZONE differed from the admins' real clock opened and closed
 * meetings at the wrong moment, and it never reproduced locally. There are three
 * distinct zones, and each has exactly one job:
 *
 *   storage()   APP_TIMEZONE. How Laravel writes and reads TIMESTAMP columns
 *               (created_at, *_notified_at ...). Fixed per install: changing it
 *               re-interprets every stored timestamp, so no UI may touch it.
 *
 *   platform()  The zone admins author wall-clock values in: meeting slots,
 *               quiz windows, deadlines. Those columns (DATE/TIME/DATETIME) carry
 *               no offset, so this is what turns them into real instants.
 *               organizations.time_zone -> settings.timezone -> storage().
 *
 *   viewer()    users.timezone. DISPLAY ONLY. A meeting is one real instant for
 *               everyone; the viewer's zone decides how it is shown, never when
 *               it opens.
 */
class TimezoneService
{
    private const PLATFORM_CACHE_KEY = 'timezone.platform';
    private const ORGANIZATION_CACHE_KEY = 'timezone.organization.';

    /** @var array<int, int|null> step id => organization id, per process */
    private static array $stepOrganizations = [];

    /** @var array<string, bool> */
    private static array $validity = [];

    public static function isValid($timezone): bool
    {
        if (!is_string($timezone) || trim($timezone) === '') {
            return false;
        }

        return self::$validity[$timezone] ??= in_array(
            $timezone,
            DateTimeZone::listIdentifiers(DateTimeZone::ALL_WITH_BC),
            true
        );
    }

    public static function storage(): string
    {
        $timezone = config('app.timezone');

        return self::isValid($timezone) ? $timezone : 'UTC';
    }

    public static function platform(?int $organizationId = null): string
    {
        if ($organizationId) {
            $timezone = Cache::rememberForever(
                self::ORGANIZATION_CACHE_KEY . $organizationId,
                fn () => (string) (Organization::withTrashed()->whereKey($organizationId)->value('time_zone') ?? '')
            );

            if (self::isValid($timezone)) {
                return $timezone;
            }
        }

        $timezone = Cache::rememberForever(
            self::PLATFORM_CACHE_KEY,
            fn () => (string) (Settings::whereKey(1)->value('timezone') ?? '')
        );

        return self::isValid($timezone) ? $timezone : self::storage();
    }

    /** Platform zone of the organization that owns a step (step -> cycle -> organization). */
    public static function forStep($stepId): string
    {
        $stepId = (int) $stepId;

        if ($stepId <= 0) {
            return self::platform();
        }

        if (!array_key_exists($stepId, self::$stepOrganizations)) {
            $organizationId = DB::table('steps')
                ->join('cycles', 'cycles.id', '=', 'steps.cycle_id')
                ->where('steps.id', $stepId)
                ->value('cycles.organization_id');

            self::$stepOrganizations[$stepId] = $organizationId ? (int) $organizationId : null;
        }

        return self::platform(self::$stepOrganizations[$stepId]);
    }

    /**
     * The current wall-clock in a platform zone.
     *
     * For comparisons against stored wall-clock columns in SQL or as strings
     * (`zoom_date > ?`, `steps.to >= ?`): both sides must be read in the same
     * zone, and neither now() (storage zone) nor MySQL NOW() (DB host zone) is it.
     */
    public static function platformNow(?string $timezone = null): Carbon
    {
        return Carbon::now($timezone ?? self::platform());
    }

    /** Today's date (Y-m-d) in a platform zone, for DATE deadline columns. */
    public static function platformToday(?string $timezone = null): string
    {
        return self::platformNow($timezone)->toDateString();
    }

    /** The viewer's display zone; null when they have not chosen one. */
    public static function viewer($user = null): ?string
    {
        $timezone = is_object($user) ? ($user->timezone ?? null) : null;

        return self::isValid($timezone) ? $timezone : null;
    }

    /**
     * Read a stored wall-clock value as an instant in the given zone.
     *
     * A Carbon is taken for its clock reading only (it was usually built on the
     * storage zone), an integer is already an instant (unix timestamp).
     */
    public static function wallClock($value, ?string $timezone = null): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_int($value) || (is_string($value) && ctype_digit($value))) {
                return Carbon::createFromTimestamp((int) $value);
            }

            $clock = $value instanceof \DateTimeInterface
                ? $value->format('Y-m-d H:i:s')
                : (string) $value;

            return Carbon::parse($clock, $timezone ?? self::platform());
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * An instant as the viewer should see it.
     *
     * A viewer without a timezone of their own sees the instant in the zone it
     * was authored in, which is the platform zone of the data.
     */
    public static function forViewer(?Carbon $instant, $user = null): ?Carbon
    {
        if (is_null($instant)) {
            return null;
        }

        $timezone = self::viewer($user);

        return $timezone ? $instant->copy()->setTimezone($timezone) : $instant->copy();
    }

    /**
     * A platform wall-clock date + time, already formatted as strings,
     * re-expressed in the viewer's zone in the same formats. For notifications
     * that receive pre-formatted values. Viewers without a zone of their own, or
     * input that does not parse, get the strings back unchanged.
     *
     * @return array{date: string, time: string}
     */
    public static function wallClockForViewer($date, $time, $user, ?string $platform = null): array
    {
        $date = trim((string) $date);
        $time = trim((string) $time);
        $viewer = self::viewer($user);
        $platform = $platform ?? self::platform();

        if ($date === '' || $time === '' || !$viewer || $viewer === $platform) {
            return ['date' => $date, 'time' => $time];
        }

        $dateFormat = preg_match('/^\d{2}-\d{2}-\d{4}$/', $date) ? 'd-m-Y' : 'Y-m-d';
        $timeFormat = preg_match('/[ap]m$/i', $time)
            ? 'h:i A'
            : (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $time) ? 'H:i:s' : 'H:i');

        try {
            $moment = Carbon::createFromFormat($dateFormat . ' ' . $timeFormat, $date . ' ' . strtoupper($time), $platform)
                ->setTimezone($viewer);
        } catch (\Exception $e) {
            return ['date' => $date, 'time' => $time];
        }

        return ['date' => $moment->format($dateFormat), 'time' => $moment->format($timeFormat)];
    }

    public static function forgetPlatform(): void
    {
        Cache::forget(self::PLATFORM_CACHE_KEY);
    }

    public static function forgetOrganization($organizationId): void
    {
        Cache::forget(self::ORGANIZATION_CACHE_KEY . (int) $organizationId);
    }
}
