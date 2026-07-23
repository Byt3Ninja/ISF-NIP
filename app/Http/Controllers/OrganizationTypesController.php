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
 
	/**
	 * Display a listing of the resource.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$user = auth()->user();
		$organizationTypes = OrganizationTypes::orderBy('id', 'desc')->get();

		return view('admin.organizationTypes.index', compact('organizationTypes'));
	}

	/**
	 * Show the form for creating a new resource.
	 * Note: This route is now optional as the modal handles creation
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		return view('admin.organizationTypes.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 */
	public function store(Request $request)
	{
		// Validate the request
		$validatedData = $this->validate($request, [
			'title_en' => ['required', 'string', 'max:255'],
			'title_ar' => ['required', 'string', 'max:255'],
		]);

		try {
			// Create new organization type
			$orgType = new OrganizationTypes;
			$orgType->title_en = $request->title_en;
			$orgType->title_ar = $request->title_ar;
			$orgType->save();

			// Check if request is AJAX
			if ($request->ajax() || $request->wantsJson()) {
				return response()->json([
					'success' => true,
					'message' => __('admin.StoredSuccessfully'),
					'data' => [
						'id' => $orgType->id,
						'title_en' => $orgType->title_en,
						'title_ar' => $orgType->title_ar,
						'created_at' => $orgType->created_at->format('Y-m-d H:i'),
						'updated_at' => $orgType->updated_at->format('Y-m-d H:i'),
					]
				], 201);
			}

			// Traditional redirect for non-AJAX requests
			return redirect()->route('organization-types.index')->with(['msg' => 'StoredSuccessfully']);

		} catch (Exception $e) {
			Log::error('Error creating organization type: ' . $e->getMessage());

			if ($request->ajax() || $request->wantsJson()) {
				return response()->json([
					'success' => false,
					'message' => __('admin.unexpected_error'),
					'error' => config('app.debug') ? $e->getMessage() : null
				], 500);
			}

			return redirect()->back()
				->withInput()
				->with(['message2' => 'unexpected_error']);
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 * Note: This route is now optional as the modal handles editing
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$orgType = OrganizationTypes::find($id);
		
		if (empty($orgType)) {
			return abort(404);
		}

		return view('admin.organizationTypes.edit', compact('orgType'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 */
	public function update($id, Request $request)
	{
		$orgType = OrganizationTypes::find($id);
		
		if (empty($orgType)) {
			if ($request->ajax() || $request->wantsJson()) {
				return response()->json([
					'success' => false,
					'message' => __('admin.NotFound')
				], 404);
			}
			return abort(404);
		}

		// Validate the request
		$validatedData = $this->validate($request, [
			'title_en' => ['required', 'string', 'max:255'],
			'title_ar' => ['required', 'string', 'max:255'],
		]);

		try {
			// Update organization type
			$orgType->title_en = $request->title_en;
			$orgType->title_ar = $request->title_ar;
			$orgType->save();

			// Reload the model to ensure fresh data
			$orgType->refresh();

			// Check if request is AJAX
			if ($request->ajax() || $request->wantsJson()) {
				return response()->json([
					'success' => true,
					'message' => __('admin.UpdatedSuccessfully'),
					'data' => [
						'id' => $orgType->id,
						'title_en' => $orgType->title_en,
						'title_ar' => $orgType->title_ar,
						'created_at' => $orgType->created_at->format('Y-m-d H:i'),
						'updated_at' => $orgType->updated_at->format('Y-m-d H:i'),
					]
				], 200);
			}

			// Traditional redirect for non-AJAX requests
			return redirect()->route('organization-types.index')->with(['msg' => 'UpdatedSuccessfully']);

		} catch (Exception $e) {
			Log::error('Error updating organization type: ' . $e->getMessage());

			if ($request->ajax() || $request->wantsJson()) {
				return response()->json([
					'success' => false,
					'message' => __('admin.unexpected_error'),
					'error' => config('app.debug') ? $e->getMessage() : null
				], 500);
			}

			return redirect()->back()
				->withInput()
				->with(['message2' => 'unexpected_error']);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 */
	public function destroy($id, Request $request)
	{
		try {
			$orgType = OrganizationTypes::find($id);
			
			if (empty($orgType)) {
				if ($request->ajax() || $request->wantsJson()) {
					return response()->json([
						'success' => false,
						'message' => __('admin.NotFound')
					], 404);
				}
				return abort(404);
			}

			// Check if organization type is being used
			// Uncomment and modify based on your relationships
			/*
			$organizationsCount = Organization::where('organization_type_id', $id)->count();
			if ($organizationsCount > 0) {
				if ($request->ajax() || $request->wantsJson()) {
					return response()->json([
						'success' => false,
						'message' => __('admin.Cannot delete organization type because it is being used by :count organization(s)', ['count' => $organizationsCount])
					], 422);
				}
				
				return redirect()->route('organization-types.index')
					->with(['message2' => 'Cannot delete organization type because it is being used']);
			}
			*/

			// Delete the organization type
			$orgType->delete();

			// Check if request is AJAX
			if ($request->ajax() || $request->wantsJson()) {
				return response()->json([
					'success' => true,
					'message' => __('admin.DeletedSuccessfully')
				], 200);
			}

			// Traditional redirect for non-AJAX requests
			return redirect()->route('organization-types.index')->with(['msg' => 'DeletedSuccessfully']);

		} catch (Exception $e) {
			Log::error('Error deleting organization type: ' . $e->getMessage());

			if ($request->ajax() || $request->wantsJson()) {
				return response()->json([
					'success' => false,
					'message' => __('admin.unexpected_error'),
					'error' => config('app.debug') ? $e->getMessage() : null
				], 500);
			}

			return redirect()->route('organization-types.index')
				->with(['message2' => 'unexpected_error']);
		}
	}
}