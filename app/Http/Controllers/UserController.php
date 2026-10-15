<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserApiRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UserFilterRequest;
use App\Services\UserService;
use App\User;
use App\Cycle;
use App\Company;
use App\Country;
use App\Settings;
use App\Role as R;
use App\Team_member;
use App\TeamFormation;
use Illuminate\Http\Request;
use App\Mail\WelcomeJudgerMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use \Spatie\Permission\Models\Role as Role;
use DB, Log, Exception, Session, URL, File;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Notifications\WelcomeJudgerNotification;
use Illuminate\Support\Facades\Log as FacadesLog;

class UserController extends Controller
{
	/**
	 * UserController constructor.
	 * @param UserService $userService
	 */
	protected $userService;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(UserService $userService)
	{
		$this->middleware('auth');
		$this->userService = $userService;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(UserFilterRequest $request)
	{

		$this->authorize('index', Auth::user());


		$users = $this->userService->index($request->all(),false)['data'];


		return view('admin/users/index', compact('users'));
	}

	public function startImpersonate($id)
	{
		$user = User::find($id);
		if( empty( $user ) )
		{
			Flash::error("User not found");
			return back()->withInput()->with(['msg' => 'FailedStore']);
		}

		// Logging in as someone else is the strongest action in the panel: require the
		// permission here too, and keep a non-admin inside their own organizations
		// (never onto a platform admin).
		$actor = auth()->user();
		abort_unless($actor->ableTo('users.login_as'), 403);
		if (!$actor->hasRole('admin')) {
			$inScope = $actor->ownsOrganization($user->organization_id)
				|| $actor->ownsOrganization($user->company?->cycledata?->organization_id);
			abort_if($user->hasRole('admin') || !$inScope, 403);
		}

		session([
			'impersonator_id' => auth()->id(),
		]);
		auth()->login($user, true);
		if( auth()->id() !== $user->id ) { Flash::error("User not found"); }
		return redirect('/dashboard');
	}

	public function stopImpersonate()
	{
		if (! session()->has('impersonator_id')) {
			abort(403);
		}
		$originalUser = User::find(session('impersonator_id'));
		if( !$originalUser->ableTo('users.login_as') ) {
			abort(401);
		}
		session()->forget('impersonator_id');
		auth()->login($originalUser, true);
		return redirect('/dashboard/users')->with('success', 'Impersonation ended');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()	
	{
		$this->authorize('create',Auth::user());
		$roles =$this->userService->roleList()['data'];
		return view('admin/users/create', compact('roles'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(StoreUserRequest $request)
	{
         $this->authorize('store',Auth::user());
		
		 $result = $this->userService->store($request->all());

		if ($result['data']) {
			return redirect()->route('users.index')->with(['msg' => 'StoredSuccessfully']);
		} else {
			return back()->withInput()->with(['msg' => 'FailedStore']);
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\user  $user
	 * @return \Illuminate\Http\Response
	 */
	public function show(User $user)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\User  $User
	 * @return \Illuminate\Http\Response
	 */
	public function edit(User $user)
	{
		$authUser = auth()->user();
		$roles = $this->userService->roleList()['data'];
		$countries = $this->userService->countries();

		// Only admins can assign all organizations, Sub-Admin sees their assigned orgs
		if ($authUser->hasRole('Admin')) {
			$organizations = \App\Organization::all();
		} elseif ($authUser->hasAnyRole(['organizationManager', 'subadmin'])) {
			$orgIds = $authUser->organizations_ids;
			$organizations = \App\Organization::whereIn('id', $orgIds)->get();
		} else {
			$organizations = collect();
		}

		return view('admin/users/edit', compact('user', 'roles', 'countries', 'organizations'));
	}

	/**
	 * Update the organizations assigned to a Sub-Admin user.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\User  $user
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function updateOrganizations(Request $request, User $user)
	{
		$this->authorize('update', $user);

		// Validate that user is Sub-Admin
		if (!$user->isSubAdmin()) {
			return back()->with(['msg' => 'UserNotSubAdmin']);
		}

		// Validate organization IDs
		$validated = $request->validate([
			'organization_ids' => 'nullable|array',
			'organization_ids.*' => 'exists:organizations,id',
		]);

		// Sync organizations
		$organizationIds = $validated['organization_ids'] ?? [];

		// A non-admin may only hand out organizations that are in their own scope.
		$actor = Auth::user();
		if (!$actor->hasRole('admin')) {
			foreach ($organizationIds as $organizationId) {
				abort_unless($actor->ownsOrganization($organizationId), 403);
			}
		}

		$user->organizations()->sync($organizationIds);

		return redirect()->route('users.edit', $user->id)
			->with(['success' => 'OrganizationsUpdatedSuccessfully']);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\User  $User
	 * @return \Illuminate\Http\Response
	 */
	public function update(UpdateUserApiRequest $request, User $user)
	{

			$this->authorize('update', $user);
		
			$result=$this->userService->update($user,$request->all());



		if ($result) {
			return redirect()->route('users.index')->with(['msg' => 'StoredSuccessfully']);
		} else {
			return back()->withInput()->with(['msg' => 'FailedStore']);
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\User  $User
	 * @return \Illuminate\Http\Response
	 */
	public function updatePassword(UpdateUserPasswordRequest $request /*,User $user*/)
	{
		$user = User::findOrFail($request->id);
		$this->authorize('updatePassword', $user);
	
		$result= $this->userService->updatePassword($user, $request->all());

		if ($result) {
			return redirect()->route('users.index')->with(['msg' => 'StoredSuccessfully', 'class' => 'success']);
		} else {
			return back()->withInput()->with(['msg' => 'FailedStore', 'class' => 'danger']);
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\User  $User
	 * @return \Illuminate\Http\Response
	 */
	public function updateMyPassword(UpdateUserPasswordRequest $request)
	{
		$this->authorize('updateMyPassword', Auth::user());
	
		$user = Auth::user();
		$result = $this->userService->updatePassword($user, $request->all());

		if ($result) {
			return redirect()->back()->with(['msg' => 'StoredSuccessfully', 'class' => 'success']);
		} else {
			return back()->withInput()->with(['msg' => 'FailedStore', 'class' => 'danger']);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\User  $User
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(User $user)
	{
		$this->authorize('destroy', $user);
		$result = $this->userService->destroy($user);

		if ($result) {
			return redirect()->route('users.index')->with(['msg' => 'DeletedSuccessfully']);
		} else {
			return back()->withInput()->with(['msg' => 'FailedDelete']);
		}
	}

	/**
	 * Summary of showImportForm
	 * @return array{select: array|string|null|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View}
	 */
	public function showImportForm()
	{
		$this->authorize('index', Auth::user());

		return view('admin/users/uploadExcel');
	}

	/**
	 * Summary of import
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function import(Request $request)
	{
		$this->authorize('index', Auth::user());
		// التحقق من صحة الملف
		$request->validate([
			'file' => 'required|mimes:xlsx,xls',
		]);

		$result = $this->userService->importUsers($request->file('file'));

		return redirect()->back()->with('success', 'تم رفع الشيت بنجاح!');
	}
	public function judger()
	{
		$judgers = User::whereHas('roles', function ($query) {
			$query->where('default_for', 'judger');
		})->get();

		return view('admin/users/judgers', compact('judgers'));
	}
}
