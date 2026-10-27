<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Decides whether a screening / final-screening / training meeting can be
 * joined right now (GV-8361, GV-8363, GV-8364, GV-9566).
 *
 * A meeting is ONE real instant, the same for every participant. Slot times
 * are stored as bare wall-clock values, so they are turned into instants in the
 * platform zone of the data they belong to (TimezoneService) and compared with
 * the real clock. The viewer's own timezone only changes how a time is shown,
 * never when a meeting opens.
 *
 * GV-9566: this used to re-read the slot in each VIEWER's zone, falling back to
 * APP_TIMEZONE. A server running on UTC opened meetings 3h late for every Cairo
 * user without a profile timezone, and a Kolkata judge saw the same meeting open
 * 2.5h before a Cairo judge.
 *
 * Accepted moments:
 *   - Carbon             an instant (ScreeningSlotService / PanelWindowService build them in the platform zone)
 *   - int / digit string a unix timestamp (training sessions)
 *   - other string       wall-clock in $timezone, default TimezoneService::platform()
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

    public static function moment($value, ?string $timezone = null): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        return TimezoneService::wallClock($value, $timezone);
    }

    /**
     * Can this meeting be joined right now?
     *
     * @param  mixed        $start     slot start
     * @param  mixed        $end       slot end; null => only the opening edge is enforced
     * @param  string       $audience  'judge' or 'founder'
     * @param  string|null  $timezone  zone of wall-clock STRING values; default platform
     */
    public static function isOpen($start, $end = null, string $audience = 'founder', ?string $timezone = null): bool
    {
        $start = self::moment($start, $timezone);

        if (is_null($start)) {
            return false;
        }

        $now = Carbon::now();

        $opensAt = $audience === 'judge'
            ? $start->copy()->subHours(self::EARLY_JUDGE_HOURS)
            : $start->copy()->subMinutes(self::EARLY_FOUNDER_MINUTES);

        if ($now->lt($opensAt)) {
            return false;
        }

        $end = self::moment($end, $timezone);

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
    public static function hasEnded($end, ?string $timezone = null): bool
    {
        $end = self::moment($end, $timezone);

        return !is_null($end) && Carbon::now()->gt($end);
    }

    /**
     * Has the joinable window closed for good?
     * Used to show "Session Completed" instead of "Session Not Started Yet".
     */
    public static function hasExpired($end, ?string $timezone = null): bool
    {
        $end = self::moment($end, $timezone);

        return !is_null($end) && Carbon::now()->gt($end->copy()->addMinutes(self::LATE_MINUTES));
    }
}
