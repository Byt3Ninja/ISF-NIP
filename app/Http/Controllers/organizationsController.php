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
		$organizations = Organization::with(['user', 'cycles']);
		if( !empty($user->organization_id) && !is_null($user->organization_id) && trim($user->organization_id) )
		{
			$organizations = $organizations->where('id', $user->organization_id);
		}
		if($request->status !== null && $request->status !== 'all') {
		$organizations = $organizations->where('status', $request->status)->get();
		}else{
			$organizations = $organizations->get();
		}
		foreach ($organizations as $organization) {
			$organization->total_startups = Company::whereHas('cycle', function ($query) use ($organization) {
				$query->where('organization_id', $organization->id);
			})->count();
		}
		return view('admin.organizations.index', compact('organizations'));
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

		//store the image

		if ($request->hasFile('img_url')) {
            $image = $request->file('img_url');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
             $image->move(public_path('organizations/'), $imageName);
			 $organization->img_url = 'organizations/' . $imageName;
        }else {
			$img_url = '/images/background/logo.png';
			$organization->img_url =$img_url;
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


		//store the image
		if ($request->hasFile('img_url')) {
            $image = $request->file('img_url');
            $imageName = time() . '.' . $image->getClientOriginalExtension();

             $image->move(public_path('organizations/'), $imageName);
             $organization->img_url = 'organizations/' . $imageName;
        }

		// if ($request->file('img_url') != false) {
		// 	$img_file = $request->file('img_url'); // الملف المرفوع
		// 	// الحصول على اسم الصورة الأصلي أو اسم جديد حسب الحاجة
		// 	$counter = 1;
		// 	$imageName = str_replace('.' . $img_file->getClientOriginalExtension(), '', $img_file->getClientOriginalName()) . '-' . $counter++ . '.' . $img_file->getClientOriginalExtension();
		// 	$imageName = strtolower(str_replace(' ', '-', $imageName));
		// 	// تخزين الصورة في المجلد المحدد في إعدادات التخزين 'UsersLFMPublic'
		// 	Storage::disk('UsersLFMPublic')->put($imageName, file_get_contents($img_file));

		// 	// الحصول على رابط الصورة المخزنة
		// 	$imagePath = Storage::disk('UsersLFMPublic')->url($imageName);
		// } else {
		// 	$imagePath = $organization->img_url;
		// }


		//update
		// $organization->img_url = 'organizations/' . $imageName;

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
		}
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
