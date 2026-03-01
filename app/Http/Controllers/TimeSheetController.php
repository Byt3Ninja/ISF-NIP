<?php

namespace App\Http\Controllers;

use App\Cycle;
use App\Organization;
use App\TimeSheet;
use App\Category;
use App\User;
use Illuminate\Http\Request;
use App\Jobs\ExportFTSData;
Use App\Settings;

class TimeSheetController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request)
	{
		$setting = Settings::findOrfail('1');
		if( auth()->user()->hasRole('Admin') )
		{
			$timeSheets= TimeSheet::all();
			$users = User::all();
			$cycles = Cycle::all();
		}
		else
		{
			$timeSheets= TimeSheet::where('user_id', auth()->user()->id)->get();
			if( auth()->user()->hasAnyRole(['organizationManager', 'subadmin']) )
			{
				$user = auth()->user();
				$orgIds = $user->organizations_ids;
				$cycles = Cycle::whereIn('organization_id', $orgIds)->get();
				$users = User::whereIn('organization_id', $orgIds)->get();
				$users = $users->filter(function($user) { return $user->hasAnyRole(['manager', 'coordinator']); })->values();
			}
			elseif( auth()->user()->hasRole('manager') )
			{
				$organization = Organization::where('user_id', '=', auth()->user()->id)->first();
				$cycles = Cycle::where('organization_id', '=', $organization->id)->get();
				$users = User::where('organization_id','=', $organization->id)->get();
				$users = $users->filter(function($user) { return $user->hasAnyRole(['coordinator']); })->values();
			}
			elseif( auth()->user()->hasRole('coordinator') )
			{
				$users = collect();
			}
		}

		if( $request->has('start_date') && $request->has('end_date') && $request->start_date != null && $request->end_date != null )
		{
			$timeSheets = $timeSheets->whereBetween('date', [$request->input('start_date'), $request->input('end_date')]);
		}

		if( $request->has('cycle_name') && $request->cycle_name != null )
		{
			$timeSheets = $timeSheets->where('program_id', $request->cycle_name);
		}
		$categories = Category::where('category_type', 'TimeSheet')->get();
		return view('admin.TimeSheets.index', compact('timeSheets','categories','users','cycles', 'setting'));
	}


	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		$request->validate([
			'title' => 'required|string|max:255',
			'TimeSheet_date' => 'required|date',
			'hours_worked' => 'required|numeric',
			'description' => 'required|string',
			'category' => 'nullable|sometimes|exists:categories,id',
			'program' => 'required|exists:cycles,id',
			'status' => 'required|numeric',
		]);
		$usedId = $request->has('user_id') && trim( $request->user_id ) ? $request->user_id : Auth()->user()->id;
		$timeSheet = new TimeSheet();
		$timeSheet->title = $request->title;
		$timeSheet->user_id = $usedId;
		$timeSheet->date = $request->TimeSheet_date;
		$timeSheet->hours_worked = $request->has('hours_worked') ? $request->hours_worked : 0;
		$timeSheet->description = $request->has('description') ? $request->description : null;
		$timeSheet->category_id = $request->has('category') ? $request->category : null;
		$timeSheet->program_id = $request->has('program') ? $request->program : null;
		$timeSheet->status = $request->has('status') ? $request->status : 0;
		$timeSheet->save();

		return back()->with(['class'=>'success','message'=>__('StoredSuccessfully')]);
	}

	/**
	 * Display the specified resource.
	 */
	public function show(string $id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(string $id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, string $id)
	{
		// dd($request->all());
		$request->validate([
			'title' => 'required|string|max:255',
			'TimeSheet_date' => 'required|date',
			'hours_worked' => 'required|numeric',
			'description' => 'required|string',
			'category' => 'nullable|sometimes|exists:categories,id',
			'program' => 'required|exists:cycles,id',
			'user_id' => 'nullable|sometimes|exists:users,id',
			'status' => 'required|numeric',
		]);
		
		$timeSheet = TimeSheet::find($id);
		$usedId = $request->has('user_id') && trim( $request->user_id ) ? $request->user_id : $timeSheet->user_id;
		$timeSheet->title = $request->title;
		$timeSheet->user_id = $usedId;
		$timeSheet->date = $request->TimeSheet_date;
		$timeSheet->hours_worked = $request->has('hours_worked') ? $request->hours_worked : 0;
		$timeSheet->description = $request->has('description') ? $request->description : null;
		$timeSheet->category_id = $request->has('category') ? $request->category : null;
		$timeSheet->program_id = $request->has('program') ? $request->program : null;
		$timeSheet->status = $request->has('status') ? $request->status : 0;
		$timeSheet->save();
		return back()->with(['class'=>'success','message'=>__('StoredSuccessfully')]);
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(string $id)
	{
		$timeSheet = TimeSheet::find($id);
		if( !$timeSheet ) { return back()->with(['class' => 'danger', 'message' => __('timesheetNotFound')]); }
		$timeSheet->forceDelete();
		return back()->with(['class' => 'success', 'message' => __('DeletedSuccessfully')]);
	}


	public function Finance(Request $request)
	{
		$setting = Settings::findOrfail('1');
		$timeSheets = collect();
		$organization = collect();

		$authUser = auth()->user();
		if( $authUser->hasRole('Admin') )
		{
			$users = User::all();
			$timeSheets = TimeSheet::all();
			$cycles = Cycle::all();
			$organization = Organization::all();
		}
		elseif( $authUser->hasAnyRole(['organizationManager', 'subadmin']) )
		{
			$orgIds = $authUser->organizations_ids;
			$organization = Organization::whereIn('id', $orgIds)->get();
			$cycles = Cycle::whereIn('organization_id', $orgIds)->get();
			$users = User::whereIn('organization_id', $orgIds)->get();
			$users = $users->filter(function($user) { return $user->hasAnyRole(['manager', 'coordinator']); })->values();
			$timeSheets = TimeSheet::whereIn('user_id', $users->pluck('id'))->get();
		}
		else
		{
			$timeSheets = TimeSheet::where('user_id', $authUser->id)->get();
			if( $authUser->hasRole('manager') )
			{
				$org = Organization::where('user_id', $authUser->id)->first();
				if( $org )
				{
					$cycles = Cycle::where('organization_id', $org->id)->get();
					$users = User::where('organization_id', $org->id)->get();
					$users = $users->filter(function($user) { return $user->hasAnyRole(['coordinator']); })->values();
					$organization = Organization::where('id', $org->id)->get();
				}
			}
			elseif( $authUser->hasRole('coordinator') )
			{
				$users = collect();
			}
		}

		if( $request->has('user_id') && $request->user_id != null )
		{
			$timeSheets = $timeSheets->filter(function($timeSheet) use ($request) { return $timeSheet->user_id == $request->input('user_id'); });
		}

		if( $request->has('org_id') && $request->org_id != null )
		{
			$userOrg = User::where('organization_id', $request->org_id)->pluck('id');
			$timeSheets = $timeSheets->filter(function($timeSheet) use ($userOrg) { return $userOrg->contains($timeSheet->user_id); });
		}

		if( $request->has('cycle_name') && $request->cycle_name != null ) { $timeSheets = $timeSheets->where('program_id', $request->cycle_name);
		}

		if( $request->has('start_date') && $request->start_date != null ) { $timeSheets = $timeSheets->where('date', '>=', $request->start_date); }
		
		if( $request->has('end_date') && $request->end_date != null ) { $timeSheets = $timeSheets->where('date', '<=', $request->end_date); }

		$categories = Category::all();

		$groupedTimeSheets = $timeSheets->load('user')->groupBy('user_id')->map(function ($timesheets) {
			return [
				'user' => $timesheets->first()->user,
				'total_hours' => $timesheets->sum('hours_worked'),
			];
		});

		return view('admin.TimeSheets.finance', compact('timeSheets', 'organization', 'categories', 'users', 'cycles', 'groupedTimeSheets', 'setting'));
	}

	public function exportFTS(Request $request)
	{
		$Tsheet = [
			'hours' => $request->input('hours'),
			'condition' => $request->input('condition'),
			'cycle_name' => $request->input('cycle_name'),
			'user_id' => $request->input('user_id'),
			'org_id' => $request->input(key: 'org_id'),
			'userEmail' => auth()->user()->email
		];

		ExportFTSData::dispatch($Tsheet);
		return response()->json(['message' => 'Export process started. You will receive an email with the download attached.']);
	}

	public function ClockUsers(Request $request)
	{
		$setting = Settings::findOrfail('1');
		$timeSheets = collect();
		$organization = collect();

		$authUser = auth()->user();
		if( $authUser->hasRole('Admin') )
		{
			$users = User::all();
			$timeSheets = TimeSheet::all();
			$cycles = Cycle::all();
			$organization = Organization::all();
		}
		elseif( $authUser->hasAnyRole(['organizationManager', 'subadmin']) )
		{
			$orgIds = $authUser->organizations_ids;
			$organization = Organization::whereIn('id', $orgIds)->get();
			$cycles = Cycle::whereIn('organization_id', $orgIds)->get();
			$users = User::whereIn('organization_id', $orgIds)->get();
			$users = $users->filter(function($user) { return $user->hasAnyRole(['manager', 'coordinator']); })->values();
			$timeSheets = TimeSheet::whereIn('user_id', $users->pluck('id'))->get();
		}
		else
		{
			$timeSheets = TimeSheet::where('user_id', $authUser->id)->get();

			if( $authUser->hasRole('manager') )
			{
				$org = Organization::where('user_id', $authUser->id)->first();
				if( $org )
				{
					$cycles = Cycle::where('organization_id', $org->id)->get();
					$users = User::where('organization_id', $org->id)->get();
					$users = $users->filter(function($user) { return $user->hasAnyRole(['coordinator']); })->values();
					$organization = Organization::where('id', $org->id)->get();
				}
			}
			elseif( $authUser->hasRole('coordinator') )
			{
				$users = collect();
			}
		}

		if( $request->has('user_id') && $request->user_id != null ) { $timeSheets = $timeSheets->filter(function($timeSheet) use ($request) { return $timeSheet->user_id == $request->input('user_id'); }); }

		if( $request->has('org_id') && $request->org_id != null )
		{
			$userOrg = User::where('organization_id', $request->org_id)->pluck('id');
			$timeSheets = $timeSheets->filter(function($timeSheet) use ($userOrg) { return $userOrg->contains($timeSheet->user_id); });
		}

		if( $request->has('cycle_name') && $request->cycle_name != null ) { $timeSheets = $timeSheets->where('program_id', $request->cycle_name); }

		if( $request->has('start_date') && $request->start_date != null ) { $timeSheets = $timeSheets->where('date', '>=', $request->start_date); }
		
		if( $request->has('end_date') && $request->end_date != null ) { $timeSheets = $timeSheets->where('date', '<=', $request->end_date); }

		$categories = Category::all();
		$groupedTimeSheets = $timeSheets->load('user')->groupBy('user_id')->map(function ($timeSheet) {
			return [
				'user' => $timeSheet->first()->user,
				'total_hours' => $timeSheet->sum('hours_worked'),
			];
		});

		return view('admin.TimeSheets.priceclock', compact('timeSheets', 'organization', 'categories', 'users', 'cycles', 'groupedTimeSheets','setting'));
	}

	public function updateClockUser(Request $request, string $id)
	{
		$request->validate([
			'pricetime' => 'required',
		]);
		
		$user = User::find($id);
		$user->houre_rate = $request->pricetime;
		$user->save();
		return back()->with(['class'=>'success','message'=>__('StoredSuccessfully')]);
	}
}
