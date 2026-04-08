<?php

namespace App\Http\Controllers;

use App\Service;
use App\ServiceCategory;
use App\Http\Requests\StoreServiceCategoryRequest;
use App\Http\Requests\UpdateServiceCategoryRequest;

use DB;
use Log;
use Exception;
use Session;

class ServiceCategoryController extends Controller
{

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(){

		$this->middleware('auth');


	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$serviceCategories = ServiceCategory::with(['organization', 'creator', 'creator.roles', 'creator.organizations'])
			->where(function ($q) use ($orgIds, $user) {
				// Categories assigned to a specific org the user can access
				$q->whereHas('organization', function ($q) use ($orgIds) {
					$q->whereIn('organizations.id', $orgIds);
				});
				// Categories with null organization_id (system-wide or SubAdmin "all")
				$q->orWhere(function ($q) use ($orgIds, $user) {
					$q->whereNull('organization_id')
					  ->where(function ($q) use ($orgIds, $user) {
						  // Created by Admin, or creator deleted/role changed = system-wide
						  $q->whereNull('created_by')
							->orWhereHas('creator', function ($q) {
								$q->whereHas('roles', function ($q) {
									$q->where('default_for', 'Admin');
								});
							})
							// Created by a non-SubAdmin (role changed) = system-wide fallback
							->orWhereHas('creator', function ($q) {
								$q->whereDoesntHave('roles', function ($q) {
									$q->where('default_for', 'subadmin');
								});
							})
							// Created by SubAdmin whose orgs overlap with current user's orgs
							->orWhereHas('creator', function ($q) use ($orgIds) {
								$q->whereHas('roles', function ($q) {
									$q->where('default_for', 'subadmin');
								})->whereHas('organizations', function ($q) use ($orgIds) {
									$q->whereIn('organizations.id', $orgIds);
								});
							});
					  });
				});
			})->get();
		return view('admin/settings/service_categories/index', compact('serviceCategories'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		return view('admin/settings/service_categories/create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreServiceCategoryRequest $request)
	{
		$data = $request->all();
		$data['organization_id'] = (!empty($data['organization_id']) && $data['organization_id'] !== 'all') ? $data['organization_id'] : null;
		$data['created_by'] = auth()->id();
		$result = ServiceCategory::create($data);
		if($result) { return redirect()->route('serviceCategories.index')->with( ['msg' => 'StoredSuccessfully'] ); }
		else { return back()->withInput()->with( ['msg' => 'FailedStore'] ); }
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\ServiceCategory  $ServiceCategory
	 * @return \Illuminate\Http\Response
	 */
	public function show(ServiceCategory $ServiceCategory)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\ServiceCategory  $ServiceCategory
	 * @return \Illuminate\Http\Response
	 */
	public function edit(ServiceCategory $serviceCategory)
	{
		return view('admin/settings/service_categories/edit', compact('serviceCategory'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\ServiceCategory  $ServiceCategory
	 * @return \Illuminate\Http\Response
	 */
	public function update(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory)
	{
		$data = $request->all();
		$data['organization_id'] = (!empty($data['organization_id']) && $data['organization_id'] !== 'all') ? $data['organization_id'] : null;
		$result = $serviceCategory->update($data);
		if( $result ) { return redirect()->route('serviceCategories.index')->with( ['msg' => 'StoredSuccessfully'] ); }
		else { return back()->withInput()->with( ['msg' => 'FailedStore'] ); }
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\ServiceCategory  $ServiceCategory
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(ServiceCategory $serviceCategory)
	{
		Service::where('service_category_id', $serviceCategory->id)->delete();
		$serviceCategory->delete();
		return response()->json(['state' => 'success'], 200);
	}
}
