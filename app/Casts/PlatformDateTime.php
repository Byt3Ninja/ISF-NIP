<?php

namespace App\Casts;

use App\Services\TimezoneService;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;

/**
 * A DATETIME column holding wall-clock time in the platform zone (GV-9566).
 *
 * Scheduled times (mentorship sessions, quiz windows, service bookings) are
 * stored without an offset. The stock `datetime` cast reads them in the storage
 * zone (APP_TIMEZONE), so every `$model->at < now()` comparison drifted by the
 * gap between the server and the admins' real clock. This cast reads them in
 * the platform zone of the owning organization instead, so the value is a real
 * instant: comparisons with now() are correct, and format() still prints the
 * wall-clock that was typed.
 *
 * Usage: PlatformDateTime::class . ':Y-m-d H:i,organization_id'
 *   1st arg  serialization format; omitted => ISO-8601 like the stock cast
 *   2nd arg  attribute holding the organization id; omitted => global platform zone
 */
class PlatformDateTime implements CastsAttributes, SerializesCastableAttributes
{
    public function __construct(
        private ?string $format = null,
        private ?string $organizationAttribute = null
    ) {
        $this->format = $format ?: null;
        $this->organizationAttribute = $organizationAttribute ?: null;
    }

    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return TimezoneService::wallClock($value, $this->zone($attributes));
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $zone = $this->zone($attributes);

        // An instant in any zone (e.g. an API client's offset) is converted;
        // a plain string is already platform wall-clock and is kept as typed.
        $moment = $value instanceof \DateTimeInterface
            ? Carbon::instance($value)->setTimezone($zone)
            : Carbon::parse((string) $value, $zone)->setTimezone($zone);

        return $moment->format('Y-m-d H:i:s');
    }

    public function serialize($model, string $key, $value, array $attributes)
    {
        if ($value === null || $value === '' || !$this->format) {
            return $value;
        }

        // toArray() has already turned the Carbon into an ISO-8601 UTC string
        // (HasAttributes::addCastAttributesToArray) by the time this runs.
        try {
            return Carbon::parse($value)->setTimezone($this->zone($attributes))->format($this->format);
        } catch (\Exception $e) {
            return $value;
        }
    }

    private function zone(array $attributes): string
    {
        $organizationId = $this->organizationAttribute
            ? ($attributes[$this->organizationAttribute] ?? null)
            : null;

        return TimezoneService::platform($organizationId ? (int) $organizationId : null);
    }
}
