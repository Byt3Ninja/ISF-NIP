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
		$serviceCategories = ServiceCategory::with(['organization'])
			->whereHas('organization', function ($q) use ($orgIds) {
				$q->whereIn('organizations.id', $orgIds);
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
		$result = ServiceCategory::create($request->all());
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
		$result = $serviceCategory->update($request->all());
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
