<?php

namespace App;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StartupCustomerGrowth extends Model
{
	use HasFactory;

	protected $table = 'startup_customer_growth';
	protected $fillable = [
		'company_id',
		'year',
		'month',
		'total_customers',
		'new_customers',
		'segment',
		'notes',
	];

	public function company()
	{
		return $this->belongsTo(Company::class, 'company_id');
	}

	public static function records( $companyId )
	{
		$records = DB::table('startup_customer_growth AS growth')
			->select([
				'growth.id',
				'growth.year',
				'growth.month',
				'growth.total_customers AS total',
				'growth.new_customers AS newly',
				'growth.segment AS segment',
				'growth.notes AS notes',
			])
			->where('growth.company_id', $companyId)
			->get()
			->sortBy(function ($r) {
				return ((int) $r->year) * 100 + (int) $r->month;
			})
			->values();

		// Per-row month-over-month growth on TOTAL customers.
		$previousTotal = null;
		$recordsByYear = $records->groupBy('year');
		foreach ($records as $index => $record) {
			$records[$index]->MoM = self::momLabel($previousTotal, (int) $record->total);
			$previousTotal = (int) $record->total;

			// Year Cumulative Growth Calculation
			$currentYear = $record->year;
			$prevYearLastRecord = null;
			$prevYear = (int)$currentYear - 1;
			while ($prevYear >= $currentYear - 10) {
				if (isset($recordsByYear[$prevYear]) && $recordsByYear[$prevYear]->count() > 0) {
					$prevYearLastRecord = $recordsByYear[$prevYear]->last();
					break;
				}
				$prevYear--;
			}

			if ($prevYearLastRecord) {
				$yearBaseTotal = $prevYearLastRecord->total;
			} else {
				$firstOfCurrentYear = $recordsByYear[$currentYear]->first();
				$yearBaseTotal = $firstOfCurrentYear->total - $firstOfCurrentYear->newly;
				if ($yearBaseTotal <= 0) {
					$yearBaseTotal = $firstOfCurrentYear->total;
				}
			}

			if ($yearBaseTotal > 0) {
				$cumVal = (($record->total - $yearBaseTotal) / $yearBaseTotal) * 100;
				$cumValRounded = round($cumVal, 1);
				if ($cumValRounded == 0) {
					$YearCumulative = '0%';
				} else {
					$YearCumulative = ($cumValRounded > 0 ? '+' : '') . $cumValRounded . '%';
				}
			} else {
				$YearCumulative = '–';
			}
			
			$records[$index]->YearCumulative = $YearCumulative;
		}

		return [
			'records' => $records,
			'summary' => self::buildSummary($records),
		] + self::legacyMetrics($records);
	}

	/**
	 * Signed MoM label for a single step. Returns 'Base' when the previous
	 * value is zero (per ticket), 'Not available' when there is no previous.
	 */
	protected static function momLabel($previousTotal, int $currentTotal)
	{
		if ($previousTotal === null) {
			return __('startup.growth_not_available');
		}
		if ((int) $previousTotal === 0) {
			return __('startup.growth_base');
		}
		$pct = (($currentTotal - $previousTotal) / $previousTotal) * 100;
		return self::pct($pct);
	}

	/**
	 * Format a percentage with explicit sign, no decimals. e.g. +30%, -20%, 0%.
	 */
	protected static function pct($value, int $decimals = 0): string
	{
		$rounded = round($value, $decimals);
		$sign = $rounded > 0 ? '+' : '';
		return $sign . ($decimals > 0 ? number_format($rounded, $decimals) : (int) $rounded) . '%';
	}

	/**
	 * The 4 ticket-mandated summary metrics, computed on total_customers.
	 */
	protected static function buildSummary($records): array
	{
		$na = __('startup.growth_not_available');
		$base = __('startup.growth_base');

		if ($records->isEmpty()) {
			return [
				'total_active_customers' => $na,
				'mom_growth'             => $na,
				'ytd_growth'             => $na,
				'avg_monthly_growth'     => $na,
			];
		}

		$first = $records->first();
		$last = $records->last();
		$cFirst = (int) $first->total;
		$cLast = (int) $last->total;

		// 1. Total active customers in the latest period.
		$totalActive = $cLast;

		// 2. Month-over-month for the latest period.
		if ($records->count() < 2) {
			$mom = $na;
		} else {
			$prev = (int) $records[$records->count() - 2]->total;
			$mom = $prev === 0 ? $base : self::pct((($cLast - $prev) / $prev) * 100);
		}

		// 3. Year-to-date: first vs latest record within the latest year present.
		$latestYear = (int) $last->year;
		$yearRecords = $records->filter(fn($r) => (int) $r->year === $latestYear)->values();
		if ($yearRecords->count() < 2) {
			$ytd = $na;
		} else {
			$yearFirst = (int) $yearRecords->first()->total;
			$yearCurrent = (int) $yearRecords->last()->total;
			$ytd = $yearFirst === 0 ? $base : self::pct((($yearCurrent - $yearFirst) / $yearFirst) * 100);
		}

		// 4. Average monthly growth (geometric) across all recorded steps.
		$nMonths = $records->count() - 1;
		if ($nMonths < 1) {
			$avg = $na;
		} elseif ($cFirst <= 0) {
			$avg = $base;
		} else {
			$avg = self::pct((pow($cLast / $cFirst, 1 / $nMonths) - 1) * 100, 1);
		}

		return [
			'total_active_customers' => $totalActive,
			'mom_growth'             => $mom,
			'ytd_growth'             => $ytd,
			'avg_monthly_growth'     => $avg,
		];
	}

	/**
	 * Legacy keys still consumed by the unused _startup_growth.blade.php.
	 * Kept correct-ish so that partial does not break if ever rendered.
	 */
	protected static function legacyMetrics($records): array
	{
		$base = ['avgMonthlyGrowth' => '0%', 'growthSinceFirstRecord' => '0%', 'compoundAnnualGrowthRate' => '0%'];
		if ($records->isEmpty()) {
			return $base;
		}
		$first = (int) $records->first()->total;
		$last = (int) $records->last()->total;
		$intervals = $records->count() - 1;
		$yearsCount = $records->groupBy('year')->count();

		$base['growthSinceFirstRecord'] = $first > 0 ? round((($last - $first) / $first) * 100) . '%' : '0%';
		$base['avgMonthlyGrowth'] = ($first > 0 && $intervals > 0)
			? round((pow($last / $first, 1 / $intervals) - 1) * 100, 1) . '%' : '0%';
		$base['compoundAnnualGrowthRate'] = ($first > 0 && $yearsCount > 0)
			? round((pow($last / $first, 1 / $yearsCount) - 1) * 100, 1) . '%' : '0%';
		return $base;
	}
}
