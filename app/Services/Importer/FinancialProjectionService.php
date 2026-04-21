<?php

namespace App\Services\Importer;

use App\Company;
use App\FinancialProjection;
use Carbon\Carbon;

/**
 * Upserts up to 4 FinancialProjection rows (current year + Y+1/Y+2/Y+3) from importer data.
 */
class FinancialProjectionService
{
    public function upsertYearSeriesFromImport(Company $company, array $data, int $userId): void
    {
        $currency = $this->resolveCurrency($data);
        if ($currency === null) {
            return;
        }

        $baseYear = (int) Carbon::now()->year;

        $map = [
            0 => ['ebit_key' => 'current_operating_income',     'rev_key' => 'current_revenue'],
            1 => ['ebit_key' => 'forecast_operating_income_y1', 'rev_key' => 'forecast_revenue_y1'],
            2 => ['ebit_key' => 'forecast_operating_income_y2', 'rev_key' => 'forecast_revenue_y2'],
            3 => ['ebit_key' => 'forecast_operating_income_y3', 'rev_key' => 'forecast_revenue_y3'],
        ];

        foreach ($map as $offset => $keys) {
            $ebit = $this->numeric($data[$keys['ebit_key']] ?? null);
            $rev  = $this->numeric($data[$keys['rev_key']]  ?? null);
            if ($ebit === null && $rev === null) {
                continue;
            }

            $year = $baseYear + $offset;

            FinancialProjection::updateOrCreate(
                ['company_id' => $company->id, 'year' => $year],
                [
                    'user_id'        => $userId,
                    'currency'       => $currency,
                    'ebit'           => $ebit ?? 0,
                    'revenues'       => $rev  ?? 0,
                    'valuation_date' => Carbon::create($year, 1, 1)->toDateString(),
                ]
            );
        }
    }

    private function resolveCurrency(array $data): ?string
    {
        $raw = trim((string) ($data['currency'] ?? ''));
        return $raw === '' ? null : strtoupper($raw);
    }

    private function numeric($v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        return is_numeric($v) ? (float) $v : null;
    }
}
