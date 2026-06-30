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
		$months = range(1, 12);
		$this->validate($request, [
			"add_growth_year" => "required|numeric|in:" . implode(',', $years),
			"add_growth_month" => "required|numeric|in:" . implode(',', $months),
			"add_growth_total_customers" => "required|integer|min:0",
			"add_growth_new_customers" => "required|integer|min:0",
			"add_growth_segment" => "required|in:mixed,b2c,b2b,enterprise",
			"add_growth_notes" => "sometimes|nullable",
			"company_id" => "required|numeric|exists:companies,id"
		], [
			"add_growth_total_customers.integer" => __('startup.please_enter_a_valid_whole_number'),
			"add_growth_new_customers.integer" => __('startup.please_enter_a_valid_whole_number')
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
		$months = range(1, 12);
		$this->validate($request, [
			"record_id" => "required|numeric|exists:startup_customer_growth,id",
			"add_growth_year" => "required|numeric|in:" . implode(',', $years),
			"add_growth_month" => "required|numeric|in:" . implode(',', $months),
			"add_growth_total_customers" => "required|numeric|min:0",
			"add_growth_new_customers" => "required|numeric|min:0",
			"add_growth_segment" => "required|in:mixed,b2c,b2b,enterprise",
			"add_growth_notes" => "sometimes|nullable",
			"company_id" => "required|numeric|exists:companies,id"
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
}
