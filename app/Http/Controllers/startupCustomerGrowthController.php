<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\StartupCustomerGrowth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use stdClass;

class startupCustomerGrowthController extends Controller
{
	public function addRecord(Request $request)
	{
		$user = auth()->user();
		$current_year = date('Y');
		$years = array_merge(range($current_year - 10, $current_year), range($current_year + 1, $current_year + 10));
		$months = range(0, 12);
		$this->validate($request, [
			"add_growth_year" => "required|numeric|in:" . implode(',', $years),
			"add_growth_month" => "required|numeric|in:" . implode(',', $months),
			"add_growth_total_customers" => "required|integer|min:0",
			"add_growth_new_customers" => "required|integer|min:0|lte:add_growth_total_customers",
			"add_growth_segment" => "required|in:mixed,b2c,b2b,enterprise",
			"add_growth_notes" => "sometimes|nullable",
			"company_id" => "required|numeric|exists:companies,id"
		], [
			"add_growth_total_customers.integer" => __('startup.please_enter_a_valid_whole_number'),
			"add_growth_new_customers.integer" => __('startup.please_enter_a_valid_whole_number'),
			"add_growth_new_customers.lte" => __('startup.New customers cannot exceed total customers')
		]);

		if( $user->hasRole('registered') )
		{
			if( $user->company_id != $request->company_id )
			{
				return back()->with(['error' => 'NoHavePermission', 'class' => 'danger']);
			}
		}

		$record = StartupCustomerGrowth::where('company_id', $request->company_id)->where('year', $request->add_growth_year)->where('month', $request->add_growth_month)->first();
		if( $record ) { return back()->with(['error' => __('startup.customer_growth_already_exist')]); }
		else
		{
			$record = new StartupCustomerGrowth;
			$record->company_id = $request->company_id;
			$record->year = $request->add_growth_year;
			$record->month = $request->add_growth_month;
			$record->total_customers = $request->add_growth_total_customers;
			$record->new_customers = $request->add_growth_new_customers;
			$record->segment = $request->add_growth_segment;
			$record->notes = $request->add_growth_notes;
			$record->save();

			return back()->with(['success' => __('startup.customer_growth_record_added')]);
		}
	}

	public function updateRecord(Request $request)
	{
		$user = auth()->user();
		$current_year = date('Y');
		$years = array_merge(range($current_year - 10, $current_year), range($current_year + 1, $current_year + 10));
		$months = range(0, 12);
		$this->validate($request, [
			"record_id" => "required|numeric|exists:startup_customer_growth,id",
			"add_growth_year" => "required|numeric|in:" . implode(',', $years),
			"add_growth_month" => "required|numeric|in:" . implode(',', $months),
			"add_growth_total_customers" => "required|integer|min:0",
			"add_growth_new_customers" => "required|integer|min:0|lte:add_growth_total_customers",
			"add_growth_segment" => "required|in:mixed,b2c,b2b,enterprise",
			"add_growth_notes" => "sometimes|nullable",
			"company_id" => "required|numeric|exists:companies,id"
		], [
			"add_growth_total_customers.integer" => __('startup.please_enter_a_valid_whole_number'),
			"add_growth_new_customers.integer" => __('startup.please_enter_a_valid_whole_number'),
			"add_growth_new_customers.lte" => __('startup.New customers cannot exceed total customers')
		]);

		$record = StartupCustomerGrowth::where('id', $request->record_id)
			->where('company_id', $request->company_id)
			->first();
		if( !$record ) { return back()->with(['error' => __('startup.startup_not_found')]); }

		if( $user->hasRole('registered') && $user->company_id != $request->company_id )
		{
			return back()->with(['error' => 'NoHavePermission', 'class' => 'danger']);
		}

		$duplicate = StartupCustomerGrowth::where('company_id', $request->company_id)
			->where('year', $request->add_growth_year)
			->where('month', $request->add_growth_month)
			->where('id', '!=', $record->id)
			->first();
		if( $duplicate ) { return back()->with(['error' => __('startup.customer_growth_already_exist')]); }

		$record->year = $request->add_growth_year;
		$record->month = $request->add_growth_month;
		$record->total_customers = $request->add_growth_total_customers;
		$record->new_customers = $request->add_growth_new_customers;
		$record->segment = $request->add_growth_segment;
		$record->notes = $request->add_growth_notes;
		$record->save();

		return back()->with(['success' => __('startup.customer_growth_record_updated')]);
	}

	public function deleteRecord($id)
	{
		$user = auth()->user();
		$record = StartupCustomerGrowth::findOrFail($id);

		if( $user->hasRole('registered') )
		{
			if( $user->company_id != $record->company_id )
			{
				return back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']);
			}
		}

		$record->delete();

		return back()->with(['msg' => 'customer_growth_record_deleted', 'class' => 'success']);
	}

	public function exportRecords(Request $request, $company_id)
	{
		$user = auth()->user();
		if( $user->hasRole('registered') )
		{
			if( $user->company_id != $company_id )
			{
				return abort(403, 'Unauthorized');
			}
		}

		$records_metadata = StartupCustomerGrowth::records($company_id);
		$records = collect($records_metadata['records']);

		// Apply filters if passed
		if ($request->filled('year')) {
			$records = $records->filter(fn($r) => (string)$r->year === (string)$request->year)->values();
		}
		if ($request->filled('segment')) {
			$records = $records->filter(fn($r) => strtolower($r->segment) === strtolower($request->segment))->values();
		}

		$headers = [
			'Content-Type' => 'text/csv; charset=UTF-8',
			'Content-Disposition' => 'attachment; filename="customer_growth_history.csv"',
			'Pragma' => 'no-cache',
			'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
			'Expires' => '0'
		];

		$callback = function() use ($records) {
			$file = fopen('php://output', 'w');
			// Add UTF-8 BOM for Excel compatibility
			fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

			// Headers
			fputcsv($file, [
				__('startup.Year'),
				__('startup.Month'),
				__('startup.Total customers'),
				__('startup.New customers'),
				__('startup.MoM growth'),
				__('startup.Year cumulative growth'),
				__('startup.Segment'),
				__('startup.notes')
			]);

			foreach ($records as $record) {
				$monthName = $record->month ? __('startup.month_' . $record->month) : __('startup.Yearly');
				fputcsv($file, [
					$record->year,
					$monthName,
					$record->total,
					$record->newly,
					$record->MoM,
					$record->YearCumulative,
					strtoupper($record->segment),
					$record->notes
				]);
			}

			fclose($file);
		};

		return response()->stream($callback, 200, $headers);
	}
}
