<?php

namespace App\Enums;

/**
 * mentorship_schedules.unit — how far before the session a reminder fires.
 */
enum ReminderScheduleUnit: string
{
    case MINUTES = 'minutes';
    case HOURS = 'hours';
    case DAYS = 'days';
    case WEEKS = 'weeks';

    /**
     * Resolve a stored unit, accepting the singular spelling; unknown units
     * fall back to hours (the only unit the program settings page saves).
     */
    public static function fromStored(?string $unit): self
    {
        $unit = strtolower(trim((string) $unit));

        return self::tryFrom($unit) ?? self::tryFrom($unit.'s') ?? self::HOURS;
    }

    public function toMinutes(float $value): int
    {
        return (int) round($value * match ($this) {
            self::MINUTES => 1,
            self::HOURS => 60,
            self::DAYS => 24 * 60,
            self::WEEKS => 7 * 24 * 60,
        });
    }
}
