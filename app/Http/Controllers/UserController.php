<?php

namespace App\Http\Controllers;

use App\User;
use App\Cycle;
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
use  Log, Exception, Session, URL, File;
use App\Http\Requests\UpdateUserPasswordRequest;
use App\Notifications\WelcomeJudgerNotification;
use Illuminate\Support\Facades\Log as FacadesLog;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
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
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        try {

            $this->preProcessingCheck('view_user');
            $users = User::with('company')->with('roles')->get();
            return view('admin/users/index', compact('users'));
        } catch (Exception $e) {

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {

            $this->preProcessingCheck('add_user');
            $roles = Role::all();
            return view('admin/users/create', compact('roles'));
        } catch (Exception $e) {

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        try {

            $this->preProcessingCheck('add_user');

            $setting = DB::table('settings')->where('id', 1)->first();
            if ($setting->VFEmail == 1) {
            if ($request->hasFile('img_url')) {
                //store the image
                $img_url = $request->file('img_url');
                //get the path of the stores image
                $img_url = $img_url->store('users', ['disk' => 'public_uploads']);
            } else {
                $img_url = '';
            }
            
            $inputs = $request->except(['img_url']) + ['img_url' => $img_url];
            $inputs['password'] = Hash::make($inputs['password']);
            $inputs['password_confirmation'] = Hash::make($inputs['password_confirmation']);
            $result = User::create($inputs);

            Storage::disk('UsersLFM')->makeDirectory("$result->id");
            Storage::disk('UsersLFM')->makeDirectory("$result->id/BusinessPlans");
            Storage::disk('UsersLFM')->makeDirectory("$result->id/FinancialPlans");
            Storage::disk('UsersLFM')->makeDirectory("$result->id/PitchDeck");
            Storage::disk('UsersLFM')->makeDirectory("$result->id/Shared");
            Storage::disk('UsersLFM')->makeDirectory("$result->id/Others");

            $result->assignRole($request->role);
            $data =[];
            $name = $result->first_name_en . ' ' . $result->last_name_en;
            $user = User::find($result->id);

        // التحقق من أن المستخدم موجود
        if ($user) {
            $user->sendEmailVerificationNotification();
        }


            if($request->role == 'Judger'){
                // إرسال الإيميل الترحيبي
                $data = [
                    'Type' => 'Welcome',
                    'name' => $name,
                    'email' => $result->email,
                    'password' => $request->password,
                ];
            
                // إرسال الإشعار
                $result->notify(new WelcomeJudgerNotification($data));
            }
        }else{

            if ($request->hasFile('img_url')) {
                //store the image
                $img_url = $request->file('img_url');
                //get the path of the stores image
                $img_url = $img_url->store('users', ['disk' => 'public_uploads']);
            } else {
                $img_url = '';
            }
            $inputs = $request->except(['img_url']) + ['img_url' => $img_url];
            $inputs['password'] = Hash::make($inputs['password']);
            $inputs['password_confirmation'] = Hash::make($inputs['password_confirmation']);
            $result = User::create($inputs);

            $result->email_verified_at = now();
            $result->save();

            Storage::disk('UsersLFM')->makeDirectory("$result->id");
            Storage::disk('UsersLFM')->makeDirectory("$result->id/BusinessPlans");
            Storage::disk('UsersLFM')->makeDirectory("$result->id/FinancialPlans");
            Storage::disk('UsersLFM')->makeDirectory("$result->id/PitchDeck");
            Storage::disk('UsersLFM')->makeDirectory("$result->id/Shared");
            Storage::disk('UsersLFM')->makeDirectory("$result->id/Others");

            $result->assignRole($request->role);
            $data =[];
            $name = $result->first_name_en . ' ' . $result->last_name_en;
            if($request->role == 'Judger'){
                // إرسال الإيميل الترحيبي
                $data = [
                    'name' => $name,
                    'email' => $result->email,
                    'password' => $request->password,
                ];
            
                // إرسال الإشعار
                $result->notify(new WelcomeJudgerNotification($data));
            }

        }

            if ($result)
                return redirect()->route('users.index')->with(['msg' => 'StoredSuccessfully']);
            else
                return back()->withInput()->with(['msg' => 'FailedStore']);
        } catch (Exception $e) {
            dd($e);
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
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
        $this->preProcessingCheck('view_user');
        abort(401);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $User
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {

        try {

            $this->preProcessingCheck('edit_user');
            $roles = Role::all();
            $countries = Country::all();
            $json = File::get(base_path('database/dbFiles/countries.json'));
            $_countries = json_decode($json, true);
            $_countries = collect( $_countries )->map(function ($country) {
                $country['flag'] = URL::to('app-assets/images/flags/' . strtolower($country['iso2']) . '.svg');
                return $country;
            });
            $countries = $countries->map(function ( $country ) use ( $_countries ) {
                $getCountry = $_countries->where('iso3', $country->iso3)->first();
                $flag = null; $emoji = null;
                if( !empty( $getCountry ) ) { $flag = $getCountry['flag']; $emoji = $getCountry['emoji']; }
                $country->flag = $flag;
                $country->emoji = $emoji;
                return $country;
            });
            return view('admin/users/edit', compact('user', 'roles', 'countries'));
        } catch (Exception $e) {

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $User
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        try {

            $this->preProcessingCheck('edit_user');
            if ($user->id == 1) {
                return back()->with(['msg' => 'FailedStore']);
            }
            if ($request->hasFile('img_url')) {
                //store the image
                $img_url = $request->file('img_url');
                //get the path of the stores image
                $img_url = $img_url->store('users', ['disk' => 'public_uploads']);
                //update
                $result = $user->update($request->except(['img_url']) + ['img_url' => $img_url]);
            } else {
                $result = $user->update($request->all());
            }

            $user->roles()->detach();
            $user->assignRole($request->role);

            if ($result)
                return redirect()->route('users.index')->with(['msg' => 'StoredSuccessfully']);
            else
                return back()->withInput()->with(['msg' => 'FailedStore']);
        } catch (Exception $e) {

            return back()->withInput()->with(['msg' => 'emailExist']);


            dd($e);

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $User
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(UpdateUserPasswordRequest $request)
    {
        try {
            if ($request->id == 1) {
                return back()->with(['msg' => 'FailedStore', 'class' => 'danger']);
            }
            $this->preProcessingCheck('edit_user');

            $user = User::find($request->id);
            $password = Hash::make($request->password);
            $result = $user->where('id', $user->id)
                ->update(['password' => $password]);

            if ($result)
                return redirect()->route('users.index')->with(['msg' => 'StoredSuccessfully', 'class' => 'success']);
            else
                return back()->withInput()->with(['msg' => 'FailedStore', 'class' => 'danger']);
        } catch (Exception $e) {
            dd($e);

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
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
        if ($user->id == 1) {
            return response()->json([
                'state' => 'failed'
            ], 500);
        }
        try {
            $this->preProcessingCheck('delete_user');
            $user->roles()->detach();
            $user->delete();
            $user->company()->delete();
            return response()->json([
                'state' => 'success'
            ], 200);
        } catch (Exception $e) {

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            return response()->json([
                'state' => 'failed'
            ], 500);
        }
    }

    public function companies(Request $request)
    {
        // $this->preProcessingCheck('view_user');

        $l = app()->getLocale();
        $cycle_id = null;
        $errorMessage = '';

        if( Auth::user()->hasRole('Registered') )
        {
            return redirect()->route('dashboard')->with(['error' => 'cantAccessAllCompanies']);
            $organization_id = auth()->user()->organization_id;
            $company = auth()->user()->company;
            if( $company && $company->cycle != null )
            {
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
            //     $cycle_companies = Company::where('cycle', $cycle->id)->get();
            //     $companies = $companies->merge($cycle_companies); // دمج المجموعات
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

            $organization_id = auth()->user()->organization_id;
            $cycle_ids = Cycle::where('organization_id', $organization_id)
                ->whereHas('mentors', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->pluck('id');

            // foreach ($cycle_ids as $cycle) {
            //     $cycle_companies = Company::where('cycle', $cycle->id)->get();
            //     $companies = $companies->merge($cycle_companies); // دمج المجموعات
            // }

            $companies = collect();
            $name = ($l == 'ar') ? 'name_ar' : 'name_en';
            // استعراض الـ cycles للحصول على الشركات المرتبطة
            foreach ($cycle_ids as $cycle_id) {

                $cycle_companies = Company::where('cycle', $cycle_id)
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
            $errorMessage = $cycle_id === null ? 'انت غير مشترك في البرامج الحالية' : null;
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
            //     'name' => $row[0],
            //     'email' => $row[1],
            //     'password' => Hash::make($row[2]),
            // ]);

            $user = User::create([
                'email'    => $row[1],   // تأكد من وجود العمود
                'first_name_en'    => $row[2],   // تأكد من وجود العمود
                'last_name_en'    => $row[3],   // تأكد من وجود العمود
                'first_name_ar'    => $row[2],   // تأكد من وجود العمود
                'last_name_ar'    => $row[3],   // تأكد من وجود العمود
                'phone'         => $row[4] . "" . $row[5],   // تأكد من وجود العمود
                'HaveCompany'    => (int)$row[8],   // تأكد من وجود العمود
                'country_id'    => (int)$row[7],   // تأكد من وجود العمود
                'organization_id'    => '18',   // تأكد من وجود العمود
                'password' => Hash::make('123456789'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('Registered');

            if (isset($row[8]) && (int)$row[8] == 1) { // تحقق إذا كان هناك شركة
                $companyId = Company::create([
                    'country_id'    => (int)$row[7],    // اسم الشركة
                    'name_en'    => $row[9],    // اسم الشركة
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
                    'user_id' => $user->id,               // ربط الشركة بالمستخدم
                ]);

                $user->company_id = $companyId->id;
                $user->save();
            }
        }

        return redirect()->back()->with('success', 'تم رفع الشيت بنجاح!');
    }
}
