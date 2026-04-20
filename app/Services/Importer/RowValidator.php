<?php

namespace App\Services\Importer;

use Carbon\Carbon;

class RowValidator
{
    public function __construct(private readonly ValidationContext $ctx) {}

    /**
     * Per-field dispatch. Returns null on pass, or the error code string to use.
     * $opts may supply: allowed_values, error_code (override which lang key is returned).
     */
    public function validateField(string $rule, string $value, array $opts): ?string
    {
        $value = trim($value);
        // Convention: empty value passes optional per-field rules; required_string is the gate.
        if ($rule !== 'required_string' && $value === '') {
            return null;
        }

        $err = $opts['error_code'] ?? $rule;
        return match ($rule) {
            'required_string'          => $value === '' ? $err : null,
            'string'                   => null,
            'email'                    => filter_var($value, FILTER_VALIDATE_EMAIL) ? null : $err,
            'phone'                    => preg_match('/^\+?[0-9\s\-()]{6,20}$/', $value) ? null : $err,
            'country_code'             => preg_match('/^\+?[0-9]{1,4}$/', $value) ? null : $err,
            'date_mdy'                 => $this->parseMdy($value) ? null : $err,
            'date_mdy_past'            => (function ($d) use ($err) {
                return $d && $d->lte(Carbon::today()) ? null : $err;
            })($this->parseMdy($value)),
            'numeric'                  => is_numeric($value) ? null : $err,
            'integer'                  => ctype_digit(ltrim($value, '-')) ? null : $err,
            'url', 'image_url'         => filter_var($value, FILTER_VALIDATE_URL) ? null : $err,
            'yes_no'                   => in_array(strtolower($value), ['yes', 'no'], true) ? null : $err,
            'country_name'             => $this->ctx->countryIdFor($value) !== null ? null : $err,
            'state_name_in_country'    => null,
            'sector_name'              => null,
            'currency_code'            => $this->ctx->isKnownCurrency($value) ? null : $err,
            'target_markets'           => $this->validateTargetMarkets($value) ? null : $err,

            'program_type_enum',
            'gender_enum',
            'working_commitment_enum',
            'educational_status_enum',
            'products_services_enum'   => in_array($value, $opts['allowed_values'] ?? [], true) ? null : $err,

            default                    => null,
        };
    }

    public function validateTargetMarkets(string $value): bool
    {
        foreach (array_map('trim', explode(',', $value)) as $m) {
            if ($m === '') continue;
            if ($this->ctx->countryIdFor($m) === null) return false;
        }
        return true;
    }

    private function parseMdy(string $value): ?Carbon
    {
        if (!preg_match('#^\d{1,2}/\d{1,2}/\d{4}$#', $value)) return null;
        try {
            $d = Carbon::createFromFormat('n/j/Y', $value);
            [$m, $d2, $y] = array_map('intval', explode('/', $value));
            if ($d->month !== $m || $d->day !== $d2 || $d->year !== $y) return null;
            return $d;
        } catch (\Throwable) {
            return null;
        }
    }
}
