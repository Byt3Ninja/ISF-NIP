<?php

namespace App\Services;

use App\Company;
use App\Step;
use Carbon\Carbon;

/**
 * GV-3554: Mentorship is only offered while the founder's program is actually
 * sitting on its mentorship stage — not from that stage onward. Programs
 * advance in one of two ways (`cycles.step_type`):
 *
 *   - 'cycle' (run by step) — an admin moves each startup forward, so the
 *     company's `step` must point at the mentorship step itself.
 *   - 'time'  (run by time) — every startup follows the step's date range, so
 *     today must fall inside that step's from/to window.
 *
 * A program with no mentorship step (stype 3) is not gated at all: there is no
 * stage to wait for, so mentors stay visible exactly as before.
 */
class MentorshipAvailabilityService
{
    /** steps.stype value for the Mentorship step. */
    public const MENTORSHIP_STEP_TYPE = 3;

    /**
     * Whether the founder's company is currently on the mentorship stage.
     */
    public static function isActiveForCompany(?Company $company): bool
    {
        if (!$company || empty($company->cycle)) {
            return true;
        }

        $cycle = $company->cycledata;

        if (!$cycle) {
            return true;
        }

        $mentorshipSteps = Step::where('cycle_id', $cycle->id)
            ->where('stype', self::MENTORSHIP_STEP_TYPE)
            ->orderBy('arr')
            ->get();

        // No mentorship stage in this program — nothing to wait for.
        if ($mentorshipSteps->isEmpty()) {
            return true;
        }

        // A program may hold more than one mentorship step; any active one opens
        // the page.
        if ($cycle->step_type === 'time') {
            return $mentorshipSteps->contains(fn (Step $step) => self::isWithinStepWindow($step));
        }

        if ($cycle->step_type === 'cycle') {
            return $mentorshipSteps->contains(fn (Step $step) => self::isOnStep($company, $step));
        }

        // Unconfigured step_type: keep the previous, ungated behaviour.
        return true;
    }

    /**
     * Run-by-time programs: mentorship is open only inside the step's own
     * from/to window, and closes again once the stage has passed. Dates are
     * compared as whole days, matching how step ranges are read elsewhere.
     */
    private static function isWithinStepWindow(Step $mentorshipStep): bool
    {
        if (empty($mentorshipStep->from) || empty($mentorshipStep->to)) {
            return false;
        }

        // GV-9566: inclusive days in the organization's platform zone, not the server's.
        $today = TimezoneService::platformToday(TimezoneService::forStep($mentorshipStep->id));

        return Carbon::parse($mentorshipStep->from)->toDateString() <= $today
            && $today <= Carbon::parse($mentorshipStep->to)->toDateString();
    }

    /**
     * Run-by-step programs: the startup must be sitting on the mentorship step
     * itself, matched by step id rather than by ordering position.
     */
    private static function isOnStep(Company $company, Step $mentorshipStep): bool
    {
        return !empty($company->step)
            && (int) $company->step === (int) $mentorshipStep->id;
    }
}
