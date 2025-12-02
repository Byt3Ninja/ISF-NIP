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
			->orderBy('growth.year', 'ASC')
			->orderBy('growth.month', 'ASC')
		->get();
		$records = $records->groupBy('year')->map(function ($records, $year) {
			return $records->sortBy('month')->values();
		})->values()->flatten();
		$previous = 0;
		foreach($records as $index => $record)
		{
			if( $index == 0 ) { $MoM = '–'; }
			else
			{
				$calculate_mom = (($record->newly - $previous) / $previous) * 100;
				$calculate_mom = ( round( $calculate_mom) );
				if( $calculate_mom == 0 ) { $MoM = '–'; }
				elseif( $calculate_mom > 0 ) { $MoM = abs($calculate_mom); }
				else { $MoM = 100 - abs($calculate_mom); }
				$MoM .= '%';
			}
			$records[$index]->MoM = $MoM;
			$previous = $record->total;
		}

		return $records;
	} 
}
