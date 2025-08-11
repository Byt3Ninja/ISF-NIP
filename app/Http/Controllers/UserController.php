<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserApiRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\User\UserFilterRequest;
use App\Services\UserService;
use App\User;
use App\Cycle;
use App\Role as R;
use App\Company;
use App\Country;
use App\Team_member;
use Illuminate\Http\Request;
use App\Mail\WelcomeJudgerMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
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

		$users = $this->userService->index($request->all())['data'];


		return view('admin/users/index', compact('users'));
	}




	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$this->authorize('create',Auth::user());
		$roles =$this->userService->roleList();
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


		// if ($request->hasFile('img_url')) {
		// 	//store the image
		// 	$img_url = $request->file('img_url');
		// 	//get the path of the stores image
		// 	$img_url = $img_url->store('users', ['disk' => 'public_uploads']);
		// } else {
		// 	$img_url = '';
		// }
		// $inputs = $request->except(['img_url']) + ['img_url' => $img_url];
		// $inputs['password'] = Hash::make($inputs['password']);
		// $inputs['password_confirmation'] = Hash::make($inputs['password_confirmation']);
		// $result = User::create($inputs);

		// $result->first_name_ar = $request->first_name_en;
		// $result->last_name_ar = $request->last_name_en;
		// $result->save();

		// Storage::disk('UsersLFM')->makeDirectory("$result->id");
		// Storage::disk('UsersLFM')->makeDirectory("$result->id/BusinessPlans");
		// Storage::disk('UsersLFM')->makeDirectory("$result->id/FinancialPlans");
		// Storage::disk('UsersLFM')->makeDirectory("$result->id/PitchDeck");
		// Storage::disk('UsersLFM')->makeDirectory("$result->id/Shared");
		// Storage::disk('UsersLFM')->makeDirectory("$result->id/Others");

		// $result->allotRoleById($request->role);
		// $data = [];
		// $name = $result->first_name_en . ' ' . $result->last_name_en;
		// if ($request->role == 'Judger') {
		// 	// إرسال الإيميل الترحيبي
		// 	$data = [
		// 		'name' => $name,
		// 		'email' => $result->email,
		// 		'role' => 'Judger',
		// 	];
		// 	// إرسال الإشعار
		// 	if (!in_array(env('APP_ENV', 'local'), ['local'])) {
		// 		try {
		// 			$result->notify(new WelcomeJudgerNotification($data));
		// 		} catch (\Throwable $th) {
		// 			// throw $th;
		// 		}
		// 	}
		// } else {

		// 	// إرسال الإيميل الترحيبي
		// 	$data = [
		// 		'name' => $name,
		// 		'email' => $result->email,
		// 		'password' => $result->password,
		// 		'role' => '',
		// 	];
		// 	// إرسال الإشعار
		// 	// if( !in_array(env('APP_ENV', 'local'), ['local']) )
		// 	// {
		// 	try {
		// 		$result->notify(new WelcomeJudgerNotification($data));
		// 	} catch (\Throwable $th) {
		// 		throw $th;
		// 	}
		// 	// }
		// }


		if ($result['user']) {
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
		$roles = $this->userService->roleList();
		$countries=$this->userService->countries();

		return view('admin/users/edit', compact('user', 'roles', 'countries'));
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
			$this->authorize('update', Auth::user());
			$result=$this->userService->update($user,$request->validated());



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
	public function updatePassword(UpdateUserPasswordRequest $request,User $user)
	{
		$this->authorize('updatePassword', Auth::user());
		
		$result= $this->userService->updatePassword($user, $request->validated());

		if ($result) {
			return redirect()->route('users.index')->with(['msg' => 'StoredSuccessfully', 'class' => 'success']);
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
		$this->authorize('destroy', Auth::user());
		$result = $this->userService->destroy($user);

		if ($result) {
			return redirect()->route('users.index')->with(['msg' => 'DeletedSuccessfully']);
		} else {
			return back()->withInput()->with(['msg' => 'FailedDelete']);
		}
	}

	public function companies(Request $request)
	{
		$l = app()->getLocale();
		$cycle_id = null;
		$errorMessage = '';

		if (Auth::user()->hasRole('Registered')) {
			$organization_id = auth()->user()->organization_id;
			$company = auth()->user()->company;
			if ($company && $company->cycle != null) {
				$company_id = $company->id;
				$cycle_id = $company->cycle;
				// $companies = Company::when(!is_null($organization_id))->where('organization_id',$organization_id)->where('cycle', $cycle_id)->get();
				$name = ($l == 'ar') ? 'name_ar' : 'name_en';
				$companies = Company::when(!is_null($organization_id))->where('organization_id', $organization_id)
					->where('cycle', $cycle_id)
					->when($request->search, function ($query) use ($request, $name) {
						$query->where($name, 'like', '%' . $request->search . '%');
					})
					->when($request->sector, function ($query) use ($request) {
						$query->where('sector', 'like', '%' . $request->sector . '%');
					})
					->when($request->stage, function ($query) use ($request) {
						$query->where('stage', 'like', '%' . $request->stage . '%');
					})
					->where('id', $company_id)
					->get();
					
			} else {
				$companies = [];
				$errorMessage = $cycle_id === null ? 'انت غير مشترك في البرامج الحالية' : null;
			}
		} else if (Auth::user()->hasRole('organizationManager')) {

			$organization_id = auth()->user()->organization_id;
			$cycle_ids = Cycle::where('organization_id', $organization_id)->get();

			// foreach ($cycle_ids as $cycle) {
			//	 $cycle_companies = Company::where('cycle', $cycle->id)->get();
			//	 $companies = $companies->merge($cycle_companies); // دمج المجموعات
			// }

			$companies = collect();
			$name = ($l == 'ar') ? 'name_ar' : 'name_en';
			foreach ($cycle_ids as $cycle) {

				$cycle_companies = Company::where('cycle', $cycle->id)
					->when($request->search, function ($query) use ($request, $name) {
						$query->where($name, 'like', '%' . $request->search . '%');
					})
					->when($request->sector, function ($query) use ($request) {
						$query->where('sector', 'like', '%' . $request->sector . '%');
					})
					->when($request->stage, function ($query) use ($request) {
						$query->where('stage', 'like', '%' . $request->stage . '%');
					})
					->get();
				$companies = $companies->merge($cycle_companies);
			}
			$errorMessage =  "";
		} else if (Auth::user()->hasRole('Mentor')) {
			$companies = Company::when($request->search, function ($query) use ($request, $l) {
				$name = ($l == 'ar') ? 'name_ar' : 'name_en';
				$query->where($name, 'like', '%' . $request->search . '%');
			})
				->when($request->sector, function ($query) use ($request) {
					$query->where('sector', 'like', '%' . $request->sector . '%');
				})
				->when($request->stage, function ($query) use ($request) {
					$query->where('stage', 'like', '%' . $request->stage . '%');
				})
				->get();

			$errorMessage = "";
		} else {

			// $organization_id=auth()->user()->organization_id;
			// $companies = Company::when(!is_null($organization_id))->where('organization_id',$organization_id)->get();
			$l = app()->getLocale(); // أو أي طريقة أخرى للحصول على اللغة
			$name = ($l == 'ar') ? 'name_ar' : 'name_en';
			$cycle_ids = Cycle::where('organization_id', $request->orgnization)->get();

			$companies = Company::when($request->search, function ($query) use ($request, $name) {
				$query->where($name, 'like', '%' . $request->search . '%');
			})
				->when($request->sector, function ($query) use ($request) {
					$query->where('sector', 'like', '%' . $request->sector . '%');
				})
				->when($request->stage, function ($query) use ($request) {
					$query->where('stage', 'like', '%' . $request->stage . '%');
				})
				->get();

			if (!empty($request->orgnization)) {
				// تصفية الشركات بناءً على الـ cycle_ids
				$companies = $companies->filter(function ($company) use ($cycle_ids) {
					return $cycle_ids->contains('id', $company->id);
				});
			}
			$errorMessage =  "";
		}

		return view('admin/users/companies', compact('companies', 'errorMessage'));
	}

	public function showImportForm()
	{
		return view('admin/users/uploadExcel');
	}

	// معالجة الشيت واستيراد المستخدمين
	public function import(Request $request)
	{
		// التحقق من صحة الملف
		$request->validate([
			'file' => 'required|mimes:xlsx,xls',
		]);

		$file = $request->file('file');
		$spreadsheet = IOFactory::load($file->getPathname());

		$data = $spreadsheet->getActiveSheet()->toArray();

		foreach ($data as $row) {
			// هنا قم بمعالجة الصفوف
			// $user = User::create([
			//	 'name' => $row[0],
			//	 'email' => $row[1],
			//	 'password' => Hash::make($row[2]),
			// ]);

			$user = User::create([
				'email'	=> $row[1],   // تأكد من وجود العمود
				'first_name_en'	=> $row[2],   // تأكد من وجود العمود
				'last_name_en'	=> $row[3],   // تأكد من وجود العمود
				'first_name_ar'	=> $row[2],   // تأكد من وجود العمود
				'last_name_ar'	=> $row[3],   // تأكد من وجود العمود
				'phone'		 => $row[4] . "" . $row[5],   // تأكد من وجود العمود
				'HaveCompany'	=> (int)$row[8],   // تأكد من وجود العمود
				'country_id'	=> (int)$row[7],   // تأكد من وجود العمود
				'organization_id'	=> '18',   // تأكد من وجود العمود
				'password' => Hash::make('123456789'),
				'email_verified_at' => now(),
			]);
			$user->allotRole('Registered');

			if (isset($row[8]) && (int)$row[8] == 1) { // تحقق إذا كان هناك شركة
				$companyId = Company::create([
					'country_id'	=> (int)$row[7],	// اسم الشركة
					'name_en'	=> $row[9],	// اسم الشركة
					'name_ar' => $row[9], // عنوان الشركة
					'address_en' => $row[9], // عنوان الشركة
					'address_ar' => $row[9], // عنوان الشركة
					'country_code' => $row[4], // عنوان الشركة
					'phone' => $row[5], // عنوان الشركة
					'whatsapp' => (int)$row[5], // عنوان الشركة
					'email' => $row[1], // عنوان الشركة
					'cycle' => '58', // عنوان الشركة
					'step' => '220', // عنوان الشركة
					'approved' => '1', // عنوان الشركة
					'approved_by' => '1', // عنوان الشركة
					'institutions' => '0', // عنوان الشركة
					'user_id' => $user->id,			   // ربط الشركة بالمستخدم
				]);

				$user->company_id = $companyId->id;
				$user->save();
			}
		}

		return redirect()->back()->with('success', 'تم رفع الشيت بنجاح!');
	}
}
