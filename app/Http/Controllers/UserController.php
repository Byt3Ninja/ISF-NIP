<?php

namespace App\Http\Controllers;

use App\Company;
use App\Country;
use Illuminate\Http\Request;
use \Spatie\Permission\Models\Role as Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserPasswordRequest;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Hash;
use App\User;

use DB;
use Log;
use Exception;
use Session;

use Illuminate\Support\Facades\Log as FacadesLog;

class UserController extends Controller
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
    public function index(){

        try{

            $this->preProcessingCheck('view_user');
            $users = User::with('company')->with('roles')->get();
            return view('admin/users/index', compact('users'));

        }catch (Exception $e){

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
        try{

            $this->preProcessingCheck('add_user');
            $roles = Role::all();
            return view('admin/users/create', compact('roles'));

        }catch (Exception $e){

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
        try{

            $this->preProcessingCheck('add_user');

            if ($request->hasFile('img_url')) {
                 //store the image
                $img_url = $request->file('img_url');
                //get the path of the stores image
                $img_url = $img_url->store('users', ['disk' => 'public_uploads']);
            }else{
                $img_url = '';
            }
            $inputs = $request->except(['img_url']) + ['img_url' => $img_url];
            $inputs['password'] = Hash::make( $inputs['password'] );
            $inputs['password_confirmation'] = Hash::make( $inputs['password_confirmation'] );
            $result = User::create( $inputs );

        Storage::disk('UsersLFM')->makeDirectory("$result->id");
        Storage::disk('UsersLFM')->makeDirectory("$result->id/BusinessPlans");
        Storage::disk('UsersLFM')->makeDirectory("$result->id/FinancialPlans");
        Storage::disk('UsersLFM')->makeDirectory("$result->id/PitchDeck");
        Storage::disk('UsersLFM')->makeDirectory("$result->id/Shared");
        Storage::disk('UsersLFM')->makeDirectory("$result->id/Others");

            $result->assignRole($request->role);

            if($result)
                return redirect()->route('users.index')->with( ['msg' => 'StoredSuccessfully'] );
            else
                return back()->withInput()->with( ['msg' => 'FailedStore'] );

        }catch (Exception $e){
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

        try{

            $this->preProcessingCheck('edit_user');
            $roles = Role::all();
            $countries = Country::all();
            return view('admin/users/edit', compact('user', 'roles','countries'));

        }catch (Exception $e){

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
        try{

            $this->preProcessingCheck('edit_user');
            if ($user->id==1){return back()->with( ['msg' => 'FailedStore'] );}
            if ($request->hasFile('img_url')) {
                //store the image
               $img_url = $request->file('img_url');
               //get the path of the stores image
               $img_url = $img_url->store('users', ['disk' => 'public_uploads']);
               //update
                $result = $user->update($request->except(['img_url']) + ['img_url' => $img_url]);

            }else{
                $result = $user->update($request->all());
            }

            $user->roles()->detach();
            $user->assignRole($request->role);

            if($result)
                return redirect()->route('users.index')->with( ['msg' => 'StoredSuccessfully'] );
            else
                return back()->withInput()->with( ['msg' => 'FailedStore'] );

        }catch (Exception $e){
            
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
        try{
            if ($request->id==1){return back()->with( ['msg' => 'FailedStore', 'class' => 'danger']  );}
            $this->preProcessingCheck('edit_user');

            $user = User::find($request->id);
            $password = Hash::make($request->password);
            $result = $user->where('id', $user->id)
                            ->update(['password' => $password]);

            if($result)
                return redirect()->route('users.index')->with( ['msg' => 'StoredSuccessfully', 'class' => 'success'] );
            else
                return back()->withInput()->with( ['msg' => 'FailedStore', 'class' => 'danger'] );

        }catch (Exception $e){dd($e);

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
        if ($user->id==1){return response()->json([
            'state' => 'failed'
        ], 500);}
        try{
            $this->preProcessingCheck('delete_user');
            $user->roles()->detach();
            $user->delete();
            $user->company()->delete();
            return response()->json([
                'state' => 'success'
            ], 200);

        }catch (Exception $e){

            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            return response()->json([
                'state' => 'failed'
            ], 500);

        }
    }

    public function companies () {
        $this->preProcessingCheck('view_user');
        $organization_id=auth()->user()->organization_id;
        $companies = Company::when(!is_null($organization_id),function ($query) use($organization_id){
            return $query->whereHas('cycledata',function ($q) use ($organization_id){
                $q->where('organization_id',$organization_id);
            });
        })->get();
        return view('admin/users/companies')->with('companies',$companies);
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
                'phone'         => $row[4]."".$row[5],   // تأكد من وجود العمود
                'HaveCompany'    => (int)$row[8],   // تأكد من وجود العمود
                'country_id'    => (int)$row[7],   // تأكد من وجود العمود
                'password' => Hash::make('123456789'),
    
               
            ]);

            if (isset($row[8]) && (int)$row[8] == 1) { // تحقق إذا كان هناك شركة
                $companyId = Company::create([
                    'country_id'    => (int)$row[7],    // اسم الشركة
                    'name_en'    => $row[9],    // اسم الشركة
                    'name_ar' => $row[9], // عنوان الشركة
                    'address_en' => $row[9], // عنوان الشركة
                    'address_ar' => $row[9], // عنوان الشركة
                    'country_code' => $row[4], // عنوان الشركة
                    'phone' => $row[5], // عنوان الشركة
                    'whatsapp' => $row[5], // عنوان الشركة
                    'email' => $row[1], // عنوان الشركة
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
