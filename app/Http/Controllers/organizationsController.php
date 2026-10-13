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
use App\Role as Roles;
use Illuminate\Http\Request;
use \Spatie\Permission\Models\Role as Role;
use App\Http\Requests\UpdateClientProfileRequest;
use Intervention\Image\ImageManagerStatic as Image;
use App\Http\Requests\UpdateClientProfilePasswordRequest;

class organizationsController extends Controller
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
		$orgIds = $user->organizations_ids;
		$organizationsQuery = Organization::with(['user', 'assignedUsers', 'cycles']);

		// Filter by user's accessible organizations (unless admin)
		if (!empty($orgIds) && !$user->hasRole('admin')) {
			$organizationsQuery = $organizationsQuery->whereIn('id', $orgIds);
		}

		// Calculate stats from unfiltered organizations
		$statsOrganizations = $organizationsQuery->get();
		$stats = [
			['label' => __('admin.Total'), 'value' => $statsOrganizations->count(), 'icon' => 'building'],
			['label' => __('admin.Active'), 'value' => $statsOrganizations->where('status', 1)->count(), 'icon' => 'activity'],
			['label' => __('admin.Inactive'), 'value' => $statsOrganizations->where('status', 0)->count(), 'icon' => 'ban'],
			['label' => __('admin.Draft'), 'value' => $statsOrganizations->where('is_draft', true)->count(), 'icon' => 'file-text'],
		];

		// Now apply filters for display
		if($request->status !== null && $request->status !== 'all') {
			$organizationsQuery = $organizationsQuery->where('status', $request->status);
		}

		if($request->visibility !== null && $request->visibility !== 'all') {
			if( $request->visibility == 'draft' ) {
				$organizationsQuery = $organizationsQuery->where('is_draft', true);
			}
			else if( $request->visibility == 'published' ) {
				$organizationsQuery = $organizationsQuery->where('is_draft', false);
			}
		}

		// Search functionality
		if($request->search) {
			$search = $request->search;
			$organizationsQuery = $organizationsQuery->where(function($query) use ($search) {
				$query->where('name_en', 'LIKE', '%' . $search . '%')
					  ->orWhere('name_ar', 'LIKE', '%' . $search . '%')
					  ->orWhereHas('user', function($q) use ($search) {
						  $q->where('first_name_en', 'LIKE', '%' . $search . '%')
							->orWhere('last_name_en', 'LIKE', '%' . $search . '%')
							->orWhere('first_name_ar', 'LIKE', '%' . $search . '%')
							->orWhere('last_name_ar', 'LIKE', '%' . $search . '%')
							->orWhere('email', 'LIKE', '%' . $search . '%');
					  });
			});
		}

		// Sorting
		$sortOrder = $request->sort === 'oldest' ? 'asc' : 'desc';
		$organizationsQuery->orderBy('created_at', $sortOrder);

		$organizations = $organizationsQuery->paginate(9)->withQueryString();
		foreach ($organizations as $organization) {
			$organization->total_startups = Company::whereHas('cycle', function ($query) use ($organization) {
				$query->where('organization_id', $organization->id);
			})->count();
		}
		return view('admin.organizations.index', compact('organizations', 'stats'));
	}

	public function create()
	{
		//		$roles = Role::all();
		return view('admin.organizations.create');
	}

	public function store(Request $request)
	{
		$this->validate($request, [
			'first_name_en' => ['required', 'string', 'max:255'],
			'last_name_en' => ['required', 'string', 'max:255'],
			'phone' => ['required'],
			'email' => 'email|required|unique:users,email',
			'password' => ['required', 'string', 'min:8', 'confirmed'],
			'name_ar' => ['required', 'string', 'max:255'],
			'name_en' => ['required', 'string', 'max:255'],
		]);
		$role = Roles::where('default_for', 'organizationManager')->first();
		if (empty($role)) {
			$role = new Roles;
			$role->name = 'organizationManager';
			$role->default_for = 'organizationManager';
			$role->guard_name = 'web';
			$role->save();
		}
		$user = User::create($request->except(['_token', 'name_en', 'name_ar', 'password_confirmation']));
		$user->allotRole($role->default_for);

		$organization = new Organization;
		$organization->name_ar = $request->name_ar;
		$organization->name_en = $request->name_en;
		$organization->status = intval($request->status) == 1 || $request->status === true ? 1 : 0;
		$organization->user_id = $user->id;
		$organization->color = $request->color;

		// Store the image using Storage disk
		if ($request->hasFile('img_url')) {
			$image = $request->file('img_url');
			$organization->img_url = app(\App\Services\FileManagerService::class)->upload($image, '', 'organizations', 'image');
		} else {
			$organization->img_url = null;
		}
		//update
		$organization->save();

		$password = $request->password;

		$user->organization_id = $organization->id;
		$user->password = Hash::make($request->password);
		$user->email_verified_at = date('Y-m-d H:i:s');
		$user->HaveCompany = 0;
		$user->save();


		\Illuminate\Support\Facades\Notification::route('mail', $user->email)
			->notify(new \App\Notifications\OrganizationCreatedNotification($organization, $user, $password));

		return redirect()->route('organizations.index')->with(['msg' => 'StoredSuccessfully']);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\User  $Type
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Organization $organization)
	{
		return view('admin.organizations.edit', compact('organization'));
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
		$organization = Organization::with(['user'])->find($id);
		$except = ['_token', '_method', 'name_en', 'name_ar', 'status', 'password_confirmation'];
		if ($request->has('password') && isset($request->password) && trim($request->password)) {
		} else {
			$except[] = 'password';
		}
		$this->validate($request, [
			'first_name_en' => ['required', 'string', 'max:255'],
			'last_name_en' => ['required', 'string', 'max:255'],
			'phone' => ['required'],
			'email' => 'email|required|unique:users,email,' . $organization->user_id,
			'password' => ['nullable', 'string', 'min:8', 'confirmed'],
			'name_ar' => ['required', 'string', 'max:255'],
			'name_en' => ['required', 'string', 'max:255'],
		]);

		if (!in_array('password', $except)) {
			$request->merge(['password' => Hash::make($request->password)]);
		}
		$organization->name_ar = $request->name_ar;
		$organization->name_en = $request->name_en;
		$organization->status = intval($request->status) == 1 || $request->status === true ? 1 : 0;
		$organization->color = $request->color;


		// Store the image using Storage disk
		if ($request->hasFile('img_url')) {
			$image = $request->file('img_url');
			$organization->img_url = app(\App\Services\FileManagerService::class)->update($image, $organization->img_url, '', 'organizations', 'image');
		}

		$organization->save();
		$user = $organization->user;
		$user->update($request->except($except));
		return redirect()->route('organizations.index')->with(['msg' => 'StoredSuccessfully']);
	}

	public function destroy($id)
	{
		$organization = Organization::with(['user'])->find($id);
		if (!empty($organization)) {
			$user = $organization->user;
			if (!empty($user)) {
				$user->delete();
			}
			$organization->delete();

			if (request()->ajax()) {
				return response()->json([
					'success' => true,
					'message' => __('admin.DeletedSuccessfully')
				]);
			}

			return redirect()->route('organizations.index')->with(['msg' => 'DeletedSuccessfully']);
		}

		if (request()->ajax()) {
			return response()->json([
				'success' => false,
				'message' => __('admin.OrganizationNotFound')
			], 404);
		}

		return redirect()->route('organizations.index')->with(['message2' => 'OrganizationNotFound']);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Type  $Type
	 * @return \Illuminate\Http\Response
	 */
	public function updatePassword(UpdateClientProfilePasswordRequest $request)
	{
		try {
			$user = Auth::user();
			$password = Hash::make($request->password);
			$result = $user->where('id', $user->id)
				->update(['password' => $password]);
			if ($result)
				return redirect()->route('user.profile_view')->with(['msg' => 'StoredSuccessfully', 'class' => 'success']);
			else
				return back()->withInput()->with(['msg' => 'FailedStore', 'class' => 'danger']);
		} catch (Exception $e) {
			//log what happend
			Log::channel('system_exceptions')->info('Exceptions:', [$e]);
			abort(500);
		}
	}

	public function GetLFM($path = null, $file = null)
	{

		try {
			$instance = Storage::disk('UsersLFMPublic');

			// تحديد المسار الكامل الأساسي
			$fullPath = '/' . $path . '/' . $file;

			// التحقق مما إذا كان الملف مفقودًا
			if ($instance->missing($fullPath)) {
				// إذا كان الملف مفقودًا، إزالة الجزء `'/'.$path` من المسار
				$fullPath = '/' . $file;

				// التحقق مرة أخرى بعد تعديل المسار
				if ($instance->missing($fullPath)) {
					return response()->view('errors.404')->setStatusCode(404);
				}
			}

			// إذا كان الملف موجودًا، استرجاعه
			$file = $instance->response($fullPath);

			return $file;
		} catch (\Throwable $th) {
			return response()->view('errors.404')->setStatusCode(404);
		}
	}
}
