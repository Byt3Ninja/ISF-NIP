<?php

namespace App\Http\Controllers;

use DB;
use Log;
use Auth;
use Hash;
use Session;
use Storage;
use App\User;
use Exception;
use App\Company;
use App\Organization;
use App\OrganizationTypes;
use App\Role as Roles;
use Illuminate\Http\Request;
use \Spatie\Permission\Models\Role as Role;
use App\Http\Requests\UpdateClientProfileRequest;
use Intervention\Image\ImageManagerStatic as Image;
use App\Http\Requests\UpdateClientProfilePasswordRequest;

class OrganizationTypesController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}
 
	public function index(Request $request)
	{
		$user = auth()->user();
		$organizationTypes = OrganizationTypes::get();

		return view('admin.organizationTypes.index', compact('organizationTypes'));
	}

	public function create()
	{
		return view('admin.organizationTypes.create');
	}

	public function store(Request $request)
	{
		$this->validate($request, [
			'title_en' => ['required', 'string', 'max:255'],
			'title_ar' => ['required', 'string', 'max:255'],
		]);

		$orgType = new OrganizationTypes;
		$orgType->title_en = $request->title_en;
		$orgType->title_ar = $request->title_ar;

		$orgType->save();

		return redirect()->route('organization-types.index')->with(['msg' => 'StoredSuccessfully']);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\User  $Type
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$orgType = OrganizationTypes::find($id);
		if( empty( $orgType ) ) { return abort(404); }
		return view('admin.organizationTypes.edit', compact('orgType'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Type  $Type
	 * @return \Illuminate\Http\Response
	 */
	public function update($id, Request $request)
	{
		$orgType = OrganizationTypes::find($id);
		if( empty( $orgType ) ) { return abort(404); }
		$this->validate($request, [
			'title_en' => ['required', 'string', 'max:255'],
			'title_ar' => ['required', 'string', 'max:255'],
		]);

		$orgType->title_en = $request->title_en;
		$orgType->title_ar = $request->title_ar;

		$orgType->save();

		return redirect()->route('organization-types.index')->with(['msg' => 'UpdatedSuccessfully']);
	}

	public function destroy($id)
	{
		$orgType = OrganizationTypes::find($id);
		if( !empty( $orgType ) )
		{
			$orgType->delete();
		}
	}
}
