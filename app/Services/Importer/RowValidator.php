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

    /**
     * Returns array of ['code' => string, 'field_key' => ?string] for each triggered rule.
     * Input: associative field_key => raw string value.
     */
    public function validateCrossFields(array $row): array
    {
        $errors = [];

        $start = trim($row['program_start_date'] ?? '');
        $end   = trim($row['program_end_date'] ?? '');
        if ($start !== '' && $end !== '') {
            $s = $this->parseMdy($start);
            $e = $this->parseMdy($end);
            if ($s && $e && $s->gt($e)) {
                $errors[] = ['code' => 'program_dates_order', 'field_key' => 'program_start_date'];
            }
        }

        $coFields = ['cofounder_name', 'cofounder_email', 'cofounder_phone', 'cofounder_working_commitment', 'cofounder_educational_status'];
        $anyCo = false;
        foreach ($coFields as $k) {
            if (trim($row[$k] ?? '') !== '') { $anyCo = true; break; }
        }
        if ($anyCo) {
            if (trim($row['cofounder_name'] ?? '') === '') {
                $errors[] = ['code' => 'cofounder_name_required', 'field_key' => 'cofounder_name'];
            }
            if (trim($row['cofounder_email'] ?? '') === '') {
                $errors[] = ['code' => 'cofounder_email_required', 'field_key' => 'cofounder_email'];
            }
        }

        $founderEmail   = strtolower(trim($row['founder_email']   ?? ''));
        $cofounderEmail = strtolower(trim($row['cofounder_email'] ?? ''));
        if ($founderEmail !== '' && $cofounderEmail !== '' && $founderEmail === $cofounderEmail) {
            $errors[] = ['code' => 'cofounder_email_distinct', 'field_key' => 'cofounder_email'];
        }

        $country = trim($row['country'] ?? '');
        $state   = trim($row['governorate'] ?? '');
        if ($country !== '' && $state !== '') {
            $cid = $this->ctx->countryIdFor($country);
            if ($cid !== null && $this->ctx->stateIdFor($cid, $state) === null) {
                $errors[] = ['code' => 'state_belongs_to_country', 'field_key' => 'governorate'];
            }
        }

        $flag   = strtolower(trim($row['is_previously_incubated'] ?? ''));
        $prevIn = trim($row['previous_incubator'] ?? '');
        if ($flag === 'no' && $prevIn !== '') {
            $errors[] = ['code' => 'prev_incubator_requires_flag', 'field_key' => 'previous_incubator'];
        }

        $total = $row['total_employees']  ?? '';
        $male  = $row['male_employees']   ?? '';
        $fem   = $row['female_employees'] ?? '';
        if (is_numeric($total) && is_numeric($male) && (int) $male > (int) $total) {
            $errors[] = ['code' => 'male_employees_gt_total', 'field_key' => 'male_employees'];
        }
        if (is_numeric($total) && is_numeric($fem) && (int) $fem > (int) $total) {
            $errors[] = ['code' => 'female_employees_gt_total', 'field_key' => 'female_employees'];
        }
        if (is_numeric($total) && is_numeric($male) && is_numeric($fem) && ((int) $male + (int) $fem) > (int) $total) {
            $errors[] = ['code' => 'male_plus_female_gt_total', 'field_key' => 'male_employees'];
        }

        $financial = ['average_annual_revenue', 'current_operating_income',
                      'forecast_operating_income_y1', 'forecast_operating_income_y2', 'forecast_operating_income_y3',
                      'current_revenue', 'forecast_revenue_y1', 'forecast_revenue_y2', 'forecast_revenue_y3'];
        $anyFin = false;
        foreach ($financial as $k) {
            if (trim($row[$k] ?? '') !== '') { $anyFin = true; break; }
        }
        if ($anyFin && trim($row['currency'] ?? '') === '') {
            $errors[] = ['code' => 'currency_required', 'field_key' => 'currency'];
        }

        $dob = trim($row['founder_date_of_birth'] ?? '');
        if ($dob !== '') {
            $d = $this->parseMdy($dob);
            if ($d && $d->gt(Carbon::today())) {
                $errors[] = ['code' => 'founder_dob_not_future', 'field_key' => 'founder_date_of_birth'];
            }
        }

        return $errors;
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
