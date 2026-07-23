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
            'products_services_enum'   => $this->matchProductsServices($value, $opts['allowed_values'] ?? []) !== null ? null : $err,
            'years_bucket_enum'        => $this->normalizeYearsBucket($value) !== null ? null : $err,

            default                    => null,
        };
    }

    /**
     * Runs full validation over a dataset.
     * $rows: positional rows (string[] each, aligned to $headers)
     * $mappings: field_key => header_index|null
     */
    public function validateRows(array $rows, array $mappings): RowValidationResult
    {
        $errors   = [];
        $warnings = [];

        foreach (ImporterFieldSchema::required() as $key => $field) {
            if (!array_key_exists($key, $mappings) || $mappings[$key] === null) {
                $errors[] = new RowError(0, $key, 'field_required', ['field' => $field['label_en']]);
            }
        }
        if (!empty($errors)) {
            return new RowValidationResult($errors, [], count($rows));
        }

        foreach ($rows as $idx => $row) {
            $rowNumber = $idx + 2; // header is row 1
            $resolved  = $this->resolveRow($row, $mappings);

            foreach ($resolved as $fieldKey => $value) {
                $field = ImporterFieldSchema::find($fieldKey);
                if (!$field) continue;

                $valueStr = trim((string) $value);

                if (($field['required'] ?? false) && $valueStr === '') {
                    $errors[] = new RowError(
                        $rowNumber,
                        $fieldKey,
                        $this->requiredCodeFor($fieldKey),
                        ['field' => $field['label_en']]
                    );
                    continue;
                }

                $rule = $field['validator'] ?? null;
                if ($rule === 'string' || $rule === null) continue;

                $errorCode = $this->errorCodeFor($fieldKey, $rule);
                $opts = [
                    'allowed_values' => $field['allowed_values'] ?? null,
                    'error_code'     => $errorCode,
                ];
                $err = $this->validateField($rule, $valueStr, $opts);
                if ($err !== null) {
                    $errors[] = new RowError($rowNumber, $fieldKey, $err, ['field' => $field['label_en']]);
                }
            }

            foreach ($this->validateCrossFields($resolved) as $xerr) {
                $errors[] = new RowError($rowNumber, $xerr['field_key'], $xerr['code']);
            }
        }

        return new RowValidationResult($errors, $warnings, count($rows));
    }

    private function resolveRow(array $row, array $mappings): array
    {
        $out = [];
        foreach ($mappings as $fieldKey => $idx) {
            if ($idx === null) continue;
            $out[$fieldKey] = $row[$idx] ?? '';
        }
        return $out;
    }

    private function requiredCodeFor(string $fieldKey): string
    {
        static $map = [
            'program_name'         => 'program_name_required',
            'program_type'         => 'program_type_required',
            'program_start_date'   => 'program_start_date_required',
            'program_end_date'     => 'program_end_date_required',
            'founder_first_name'   => 'founder_first_name_required',
            'founder_last_name'    => 'founder_last_name_required',
            'founder_email'        => 'founder_email_required',
            'founder_gender'       => 'founder_gender_required',
            'founder_phone'        => 'founder_phone_required',
            'founder_country_code' => 'founder_country_code_required',
            'startup_name'         => 'startup_name_required',
            'country'              => 'country_required',
        ];
        return $map[$fieldKey] ?? 'field_required';
    }

    private function errorCodeFor(string $fieldKey, string $rule): string
    {
        static $map = [
            'founder_email'    => 'founder_email_invalid',
            'company_email'    => 'company_email_invalid',
            'cofounder_email'  => 'cofounder_email_invalid',
            'founder_phone'    => 'founder_phone_invalid',
            'cofounder_phone'  => 'cofounder_phone_invalid',
            'program_start_date' => 'program_start_date_invalid',
            'program_end_date'   => 'program_end_date_invalid',
            'founder_date_of_birth' => 'founder_dob_invalid',
            'founder_join_date'  => 'founder_join_date_invalid',
            'establishment_date' => 'establishment_date_invalid',
            'program_name'       => 'program_name_required',
            'founder_first_name' => 'founder_first_name_required',
            'founder_last_name'  => 'founder_last_name_required',
            'startup_name'       => 'startup_name_required',
            'program_type'       => 'program_type_enum',
            'founder_gender'     => 'founder_gender_enum',
            'founder_working_commitment'   => 'founder_working_commitment_enum',
            'founder_educational_status'   => 'founder_educational_status_enum',
            'cofounder_working_commitment' => 'cofounder_working_commitment_enum',
            'is_previously_incubated'      => 'is_previously_incubated_enum',
            'generate_revenue'             => 'generate_revenue_enum',
            'product_or_service'           => 'product_or_service_enum',
            'company_website'  => 'company_website_invalid',
            'company_facebook' => 'company_facebook_invalid',
            'company_logo'     => 'company_logo_invalid',
            'mvp_link'         => 'mvp_link_invalid',
            'country'          => 'country_required',
            'currency'         => 'currency_invalid',
            'target_markets'   => 'target_markets_invalid',
            'founder_country_code' => 'founder_country_code_invalid',
            'total_employees'  => 'employees_non_numeric',
            'male_employees'   => 'employees_non_numeric',
            'female_employees' => 'employees_non_numeric',
            'average_annual_revenue'        => 'numeric_required',
            'current_operating_income'      => 'numeric_required',
            'forecast_operating_income_y1'  => 'numeric_required',
            'forecast_operating_income_y2'  => 'numeric_required',
            'forecast_operating_income_y3'  => 'numeric_required',
            'current_revenue'               => 'numeric_required',
            'forecast_revenue_y1'           => 'numeric_required',
            'forecast_revenue_y2'           => 'numeric_required',
            'forecast_revenue_y3'           => 'numeric_required',
        ];
        return $map[$fieldKey] ?? $rule;
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

    /**
     * Returns the canonical-cased allowed value matching $value (case-insensitive),
     * or null if no match. Used by enum validators and by canonicaliseRow().
     */
    public function matchEnumCaseInsensitive(string $value, array $allowed): ?string
    {
        $needle = mb_strtolower(trim($value), 'UTF-8');
        if ($needle === '') return null;
        foreach ($allowed as $allowedValue) {
            if (mb_strtolower($allowedValue, 'UTF-8') === $needle) {
                return $allowedValue;
            }
        }
        return null;
    }

    /**
     * Replaces enum-field values in $resolvedRow with their canonical-cased
     * counterparts (e.g. 'student' → 'Student'). Non-enum fields and
     * non-matching values are left untouched. Empty values pass through.
     */
    public function canonicaliseRow(array $resolvedRow): array
    {
        foreach ($resolvedRow as $fieldKey => $value) {
            $field = ImporterFieldSchema::find($fieldKey);
            if (!$field) continue;
            $v = trim((string) $value);
            if ($v === '') continue;

            $validator = $field['validator'] ?? null;
            $allowed   = $field['allowed_values'] ?? null;

            if ($validator === 'products_services_enum') {
                $canon = $this->matchProductsServices($v, $allowed ?? []);
            } elseif ($validator === 'years_bucket_enum') {
                $canon = $this->normalizeYearsBucket($v);
            } elseif (is_array($allowed) && !empty($allowed)) {
                $canon = $this->matchEnumCaseInsensitive($v, $allowed);
            } else {
                $canon = null;
            }

            if ($canon !== null) {
                $resolvedRow[$fieldKey] = $canon;
            }
        }
        return $resolvedRow;
    }

    /**
     * Match Product/Service input, accepting plural forms and "and"/"&"/"+" variants
     * as synonyms for the canonical "Product + Service".
     */
    public function matchProductsServices(string $value, array $allowed): ?string
    {
        $direct = $this->matchEnumCaseInsensitive($value, $allowed);
        if ($direct !== null) return $direct;

        $n = mb_strtolower(trim($value), 'UTF-8');
        $n = preg_replace('/\s+/', ' ', $n);

        $aliases = [
            'products'              => 'Product',
            'services'              => 'Service',
            'products and services' => 'Product + Service',
            'product and service'   => 'Product + Service',
            'products & services'   => 'Product + Service',
            'product & service'     => 'Product + Service',
            'products + services'   => 'Product + Service',
        ];
        if (isset($aliases[$n]) && in_array($aliases[$n], $allowed, true)) {
            return $aliases[$n];
        }
        return null;
    }

    /**
     * Map either a canonical bucket (0-2/2-5/5-10/10+) or a plain number of years
     * to one of the four canonical buckets. Returns null when input is unparseable.
     * Bucket boundaries are inclusive of the lower bound, exclusive of the upper.
     */
    public function normalizeYearsBucket(string $value): ?string
    {
        $v = trim($value);
        if ($v === '') return null;

        foreach (['0-2', '2-5', '5-10', '10+'] as $bucket) {
            if (strcasecmp($v, $bucket) === 0) return $bucket;
        }
        if (is_numeric($v)) {
            $n = (float) $v;
            if ($n < 0)  return null;
            if ($n < 2)  return '0-2';
            if ($n < 5)  return '2-5';
            if ($n < 10) return '5-10';
            return '10+';
        }
        return null;
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
