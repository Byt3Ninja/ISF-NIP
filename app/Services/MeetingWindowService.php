<?php

namespace App\Services;

use App\User;
use Carbon\Carbon;

/**
 * Decides whether a screening / final-screening meeting can be joined right now
 * (GV-8361, GV-8363, GV-8364).
 *
 * Screening slots are stored as bare wall-clock values (`session_date` +
 * `start_time`), with no timezone attached. Every join gate used to compare them
 * against the raw server clock, so a user whose profile timezone differed from
 * the server's saw the button open and close at the wrong moment — reported
 * repeatedly by QC as "Session Not Started Yet" on a session that had started.
 *
 * The rule here: the stored wall-clock time is interpreted as local time in the
 * viewer's own timezone, and "now" is taken in that same timezone. Both sides of
 * the comparison therefore live in one zone and the arithmetic is honest.
 *
 * Windows differ by audience:
 *   - judges  open EARLY_JUDGE_HOURS   before their slot (they host the panel)
 *   - founders open EARLY_FOUNDER_MINUTES before their slot
 * and both stay open until LATE_MINUTES after the slot ends, so a meeting that
 * runs over is still reachable but an expired one is not.
 */
class MeetingWindowService
{
    /** Judges may join this many hours before the slot starts. */
    public const EARLY_JUDGE_HOURS = 24;

    /** Startups may join this many minutes before their slot starts. */
    public const EARLY_FOUNDER_MINUTES = 10;

    /** Both may still join this many minutes after the slot ends. */
    public const LATE_MINUTES = 60;

    /**
     * The timezone a given user's meeting times should be read in.
     *
     * `users.timezone` is empty for most accounts, so an unset/invalid value
     * falls back to the app timezone — preserving today's behaviour for them.
     */
    public static function timezoneFor($user = null): string
    {
        $fallback = config('app.timezone') ?: 'UTC';
        $timezone = is_object($user) ? ($user->timezone ?? null) : null;

        if (empty($timezone) || !is_string($timezone)) {
            return $fallback;
        }

        // A stale or hand-edited profile value must never break the dashboard.
        try {
            new \DateTimeZone($timezone);
        } catch (\Exception $e) {
            return $fallback;
        }

        return $timezone;
    }

    /** "Now" as seen by this user. */
    public static function now($user = null): Carbon
    {
        return Carbon::now(self::timezoneFor($user));
    }

    /**
     * Re-read a slot boundary as wall-clock time in the user's timezone.
     *
     * ScreeningSlotService builds its Carbons on the server default zone. Only
     * the clock reading is meaningful (there is no stored offset), so the same
     * reading is re-anchored in the viewer's zone rather than converted.
     */
    public static function inUserZone($moment, $user = null): ?Carbon
    {
        if (empty($moment)) {
            return null;
        }

        $timezone = self::timezoneFor($user);

        try {
            $moment = $moment instanceof Carbon
                ? $moment
                : Carbon::parse((string) $moment);

            return Carbon::parse($moment->format('Y-m-d H:i:s'), $timezone);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Can this meeting be joined right now?
     *
     * @param  mixed  $start     slot start (Carbon or parsable wall-clock string)
     * @param  mixed  $end       slot end; null => only the opening edge is enforced
     * @param  mixed  $user      viewer, for timezone resolution
     * @param  string $audience  'judge' or 'founder'
     */
    public static function isOpen($start, $end = null, $user = null, string $audience = 'founder'): bool
    {
        $start = self::inUserZone($start, $user);

        if (is_null($start)) {
            return false;
        }

        $now = self::now($user);

        $opensAt = $audience === 'judge'
            ? $start->copy()->subHours(self::EARLY_JUDGE_HOURS)
            : $start->copy()->subMinutes(self::EARLY_FOUNDER_MINUTES);

        if ($now->lt($opensAt)) {
            return false;
        }

        $end = self::inUserZone($end, $user);

        // No end time recorded: nothing to expire against, so treat it as open.
        if (is_null($end)) {
            return true;
        }

        return $now->lte($end->copy()->addMinutes(self::LATE_MINUTES));
    }

    /**
     * Is the session itself over? (GV-7381)
     *
     * Unlike hasExpired(), this ignores the LATE_MINUTES grace: the grace keeps
     * an over-running meeting reachable, but for reporting purposes a session is
     * "Completed" the moment its end time passes.
     */
    public static function hasEnded($end, $user = null): bool
    {
        $end = self::inUserZone($end, $user);

        if (is_null($end)) {
            return false;
        }

        return self::now($user)->gt($end);
    }

    /**
     * Has the joinable window closed for good?
     * Used to show "Session Completed" instead of "Session Not Started Yet".
     */
    public static function hasExpired($end, $user = null): bool
    {
        $end = self::inUserZone($end, $user);

        if (is_null($end)) {
            return false;
        }

        return self::now($user)->gt($end->copy()->addMinutes(self::LATE_MINUTES));
    }
}
