<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Derives per-startup screening meeting slots (GV-3295).
 *
 * A panel schedules ONE meeting that its startups join in sequence. When the
 * admin enables the interval switch, each startup gets its own window computed
 * from the panel start time, the interval, and the startup's position in the
 * panel's company list — rather than every startup showing the same window.
 *
 * Per the ticket clarification, slot N spans:
 *     start + (N * interval)  ..  start + ((N + 1) * interval)
 * displayed so consecutive slots do not appear to overlap:
 *     Startup 1: 10:00 - 10:30
 *     Startup 2: 10:31 - 11:00
 *     Startup 3: 11:01 - 11:30
 * i.e. every slot after the first is displayed starting one minute later, so
 * the boundary minute is not claimed by two startups at once.
 *
 * Nothing here is persisted: only `screenings.interval` is stored, so editing
 * the panel's start time reflows every slot automatically.
 */
class ScreeningSlotService
{
    /**
     * Resolve one startup's slot within a panel.
     *
     * @param  string|null  $sessionDate  Y-m-d
     * @param  string|null  $startTime    H:i or H:i:s
     * @param  int|null     $interval     minutes; null/0 => interval mode off
     * @param  int          $position     zero-based index of the startup in the panel
     * @param  string|null  $endTime      manually entered end time, used when interval is off
     * @return array{start: ?Carbon, end: ?Carbon, has_slot: bool}
     */
    public static function slotFor(?string $sessionDate, ?string $startTime, ?int $interval, int $position = 0, ?string $endTime = null): array
    {
        $empty = ['start' => null, 'end' => null, 'has_slot' => false];

        if (empty($sessionDate) || empty($startTime)) {
            return $empty;
        }

        try {
            $start = Carbon::parse($sessionDate . ' ' . $startTime);
        } catch (\Exception $e) {
            return $empty;
        }

        // Interval OFF: every startup shares the panel's manually entered window.
        if (empty($interval) || $interval <= 0) {
            $end = null;
            if (!empty($endTime)) {
                try {
                    $end = Carbon::parse($sessionDate . ' ' . $endTime);
                } catch (\Exception $e) {
                    $end = null;
                }
            }

            return ['start' => $start, 'end' => $end, 'has_slot' => true];
        }

        $position = max(0, $position);
        $slotStart = (clone $start)->addMinutes($position * $interval);
        $slotEnd = (clone $start)->addMinutes(($position + 1) * $interval);

        // Shift the displayed start of every slot but the first by one minute so
        // the shared boundary minute belongs to exactly one startup.
        if ($position > 0) {
            $slotStart->addMinute();
        }

        return ['start' => $slotStart, 'end' => $slotEnd, 'has_slot' => true];
    }

    /**
     * Zero-based position of a company within a panel's ordered company list.
     * Returns 0 when the company is absent, so it falls back to the panel start.
     */
    public static function positionOf($companyIds, $companyId): int
    {
        $companyIds = is_array($companyIds) ? $companyIds : (json_decode($companyIds, true) ?: []);
        $index = array_search((int) $companyId, array_map('intval', array_values($companyIds)), true);

        return $index === false ? 0 : (int) $index;
    }

    /**
     * Convenience: resolve a slot directly from a Screening row + company id.
     *
     * @return array{start: ?Carbon, end: ?Carbon, has_slot: bool}
     */
    public static function slotForCompany($screening, $companyId): array
    {
        if (empty($screening)) {
            return ['start' => null, 'end' => null, 'has_slot' => false];
        }

        return self::slotFor(
            $screening->session_date ?? null,
            $screening->start_time ?? null,
            isset($screening->interval) ? (int) $screening->interval : null,
            self::positionOf($screening->users ?? [], $companyId),
            $screening->end_time ?? null
        );
    }

    /**
     * "10:00 AM - 10:30 AM", or just the start when no end is known.
     */
    public static function formatRange(array $slot, string $format = 'h:i A'): string
    {
        if (empty($slot['start'])) {
            return '';
        }

        if (empty($slot['end'])) {
            return $slot['start']->format($format);
        }

        return $slot['start']->format($format) . ' - ' . $slot['end']->format($format);
    }
}
