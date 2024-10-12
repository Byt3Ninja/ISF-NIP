<?php

namespace App\Http\Controllers;
use App\Country;
use App\Events\WebMessage;
use App\FundingRound;
use App\Sector;
use App\Team_member;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUpdateClientCompanyRequest;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\State;
use App\City;
use App\Company;
use App\Currency;
use App\Like;
use App\Milestone;
use App\Settings;
use App\SustainableIcon;
use \Illuminate\Validation\ValidationException;
use DB;
use Auth;
use Log;
use Exception;
use Session;
use Storage;
use Intervention\Image\ImageManagerStatic as Image;


class ClientCompanyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(){
        $this->middleware('auth');
    }
    public function show($id)
    {
        try{
            $company = Company::with('state')
                                ->with('city')
                                ->with('user')
                                ->findorfail($id);
if(!empty($company->Sustainable_development)){
                                $sustainableDevelopment = json_decode($company->Sustainable_development, true);

                                $icons = SustainableIcon::whereIn('title', $sustainableDevelopment)->get();
}else{
    $icons = '';
}
                                // استرجاع الأيقونات المناسبة
                                // $icons = SustainableIcon::where('title', $sustainableDevelopment)->get();
                                
                                
            return view('admin/clients/company_profile', compact('company','icons'));
        }catch (Exception $e){
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }
    /**
     * Show the form for editing/Createing the specified resource.
     *
     * @param  \App\Company  $Type
     * @return \Illuminate\Http\Response
     */
    public function editOrCreate(){

        
        try{
            $client = User::where('HaveCompany',1)->with('company')
                ->with('company.city')
                ->find(Auth::id());
            $countries = Country::all();
            $states = State::all();
            $cities = City::all();
            $Setting = Settings::where('id', 1)->first();
            $sectors = Sector::whereNull('parent_id')->get();
            $funding_rounds=auth()->user()->company?auth()->user()->company->fundingRounds:[];

            $teamMembers = Team_Member::all(); // جلب جميع الأعضاء
            $MileStones = Milestone::all(); // جلب جميع الأعضاء
            $Currencies = Currency::all(); // جلب جميع الأعضاء

            return view('admin/clients/company', compact(
                'client','Currencies','MileStones', 'teamMembers', 'states', 'cities','countries','sectors','funding_rounds','Setting'
            ));
        }catch (Exception $e){
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            dd($e);
            abort(500);
        }
    }
    /**
     * Update/Store the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateOrStore(StoreUpdateClientCompanyRequest $request)
    {
        
        try{
            $user = Auth::user();
            $companyId = $user->company_id;
            $data = $request->all();


            // $data['institutions'] = json_encode($request->institutions);
            // if ($request->img_url) {
            //     $img_url = $request->img_url;
            //     $data['logo' ]= $img_url;
            // }
            // if ($request->hasFile('business_model')) {
            //     $business_model = $request->file('business_model');
            //     $business_model = $business_model->store('users', ['disk' => 'public_uploads']);
            //     $data['business_model' ]= $business_model;
            // }
            // if ($request->hasFile('business_plan')) {
            //     $business_plan = $request->file('business_plan');
            //     $business_plan = $business_plan->store('users', ['disk' => 'public_uploads']);
            //     $data['business_plan' ]= $business_plan;
            // }
            // if ($request->hasFile('pitch_deck')) {
            //     $pitch_deck = $request->file('pitch_deck');
            //     $pitch_deck = $pitch_deck->store('users', ['disk' => 'public_uploads']);
            //     $data['pitch_deck' ]= $pitch_deck;
            // }
            // if ($request->hasFile('financial_plan')) {
            //     $financial_plan = $request->file('financial_plan');
            //     $financial_plan = $financial_plan->store('users', ['disk' => 'public_uploads']);
            //     $data['financial_plan' ]= $financial_plan;
            // }

           
if($data['CompanyN']==1){

    $validatedData = $request->validate([
        'name_en' => 'required|string|max:255',
        'name_ar' => 'required|string|max:255',
        'country_id' => 'required|exists:countries,id',
        'state_id' => 'required|exists:states,id',
        'city_id' => 'required|exists:cities,id',
        'sector' => 'required|string|max:255',
        'address_en' => 'required|string|max:500',
        'address_ar' => 'required|string|max:500',
        'est_date' => 'required|date',
    ], [
        'name_en.required' => __('admin.English Name is required'),
        'name_ar.required' => __('admin.Arabic Name is required'),
        'country_id.required' => __('admin.Country is required'),
        'country_id.exists' => __('admin.Invalid Country selected'),
        'state_id.required' => __('admin.State is required'),
        'state_id.exists' => __('admin.Invalid State selected'),
        'city_id.required' => __('admin.City is required'),
        'city_id.exists' => __('admin.Invalid City selected'),
        'sector.required' => __('admin.Sector is required'),
        'address_en.required' => __('admin.English Address is required'),
        'address_ar.required' => __('admin.Arabic Address is required'),
        'est_date.required' => __('admin.Established Date must be a valid date'),
    ]);

            $result = Company::updateOrCreate(['id' => $companyId], $data);

            $msgtype = 'UpdateComp';
            if(!$companyId) {
                User::where('id', Auth::id())->update(['company_id' => $result->id]);
                $msgtype = 'NewComp';
            }

}
if($data['CompanyN']==2){

    $validatedData = $request->validate([
        'phone' => 'required|numeric|digits_between:8,15',
        'WhatsApp' => 'required|numeric|digits_between:8,15',
        'email' => 'required|email|max:255',
    ], [
        'phone.required' => __('admin.Phone is required'),
        'phone.numeric' => __('admin.Phone must be a valid number'),
        'phone.digits_between' => __('admin.Phone must be between 8 and 15 digits'),
        
        'WhatsApp.required' => __('admin.WhatsApp is required'),
        'WhatsApp.numeric' => __('admin.WhatsApp must be a valid number'),
        'WhatsApp.digits_between' => __('admin.WhatsApp must be between 8 and 15 digits'),
        
        'email.required' => __('admin.E-mail is required'),
        'email.email' => __('admin.Invalid E-mail format'),
        'email.max' => __('admin.E-mail must not exceed 255 characters'),
    ]);

            // $result = Company::updateOrCreate(['id' => $companyId], $data);
            if ($companyId) {
                // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                $result = Company::where('id', $companyId)->update([
                    'phone' => $data['phone'],  // مثال لإضافة حقل الهاتف
                    'whatsapp' => $data['WhatsApp'],  // مثال لإضافة حقل الواتساب
                    'email' => $data['email'],  // مثال لإضافة حقل البريد الإلكتروني
                ]);
                
                $msgtype = 'UpdateComp';
            } else {
                // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                $result = Company::create([
                    'phone' => $data['phone'],  // مثال لإضافة حقل الهاتف
                    'whatsapp' => $data['WhatsApp'],  // مثال لإضافة حقل الواتساب
                    'email' => $data['email'],  // مثال لإضافة حقل البريد الإلكتروني
                ]);
                
                $msgtype = 'NewComp';
            }

            $msgtype = 'UpdateComp';
            if(!$companyId) {
                User::where('id', Auth::id())->update(['company_id' => $result->id]);
                $msgtype = 'NewComp';
            }

}
if($data['CompanyN']==3){
    $validatedData = $request->validate([
        'company_description' => 'required|string|max:500',
        'Sustainable_development' => 'nullable|array',
        'Sustainable_development.*' => 'in:no_poverty,zero_hunger,good_health,quality_education,gender_equality,clean_water,clean_energy,decent_work,industry_innovation,reduced_inequalities,sustainable_cities,responsible_consumption,climate_action,life_below_water,life_on_land,peace_justice,partnership_goals',
    ], [
        'company_description.required' => __('admin.Company description must not exceed'),
        'company_description.max' => __('admin.Company description must not exceed 500 characters'),
        'Sustainable_development.required' => __('admin.sustainable development is Empty'),
        'Sustainable_development.*.in' => __('admin.sustainable development option'),
    ]);
            // $result = Company::updateOrCreate(['id' => $companyId], $data);
            if ($companyId) {
                // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                $result = Company::where('id', $companyId)->update([
                    'info_company' => $data['company_description'],
                    'Sustainable_development' => json_encode($data['Sustainable_development']),
                ]);
                $msgtype = 'UpdateComp';
            } else {
                // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                $result = Company::create([
                    'info_company' => $data['company_description'],
                    'Sustainable_development' => json_encode($data['Sustainable_development']),
                ]);
                $msgtype = 'NewComp';
            }

            $msgtype = 'UpdateComp';
            if(!$companyId) {
                User::where('id', Auth::id())->update(['company_id' => $result->id]);
                $msgtype = 'NewComp';
            }

}
if($data['CompanyN']==4){
    $request->validate([
        'Currency' => 'required|string',
        'pre_money_valuation' => 'required|numeric|min:0',
        'raised_fund' => 'required|numeric|min:0',
        'stage' => 'required|string',
        'targeted_market' => 'required|string',
        'business_type' => 'required|string',
    ], [
        'Currency.required' => __('admin.Currency is required'),
    
        'pre_money_valuation.required' => __('admin.Pre-money valuation is required'),
        'pre_money_valuation.numeric' => __('admin.Pre-money valuation must be a number'),
        'pre_money_valuation.min' => __('admin.Pre-money valuation must be a positive number'),
    
        'raised_fund.required' => __('admin.Raised fund is required'),
        'raised_fund.numeric' => __('admin.Raised fund must be a number'),
        'raised_fund.min' => __('admin.Raised fund must be a positive number'),
    
        'stage.required' => __('admin.Stage is required'),
    
        'targeted_market.required' => __('admin.Targeted market is required'),
    
        'business_type.required' => __('admin.Business type is required'),
    ]);
            // $result = Company::updateOrCreate(['id' => $companyId], $data);
            if ($companyId) {
                // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                $result = Company::where('id', $companyId)->update([
                    'Currency' => $data['Currency'],
                    'Pre_money_Valuation' => $data['pre_money_valuation'],
                    'raised_fund' => $data['raised_fund'],
                    'stage' => $data['stage'],
                    'targeted_market' => $data['targeted_market'],
                    'business_type' => $data['business_type'],
                ]);
                $msgtype = 'UpdateComp';
            } else {
                // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                $result = Company::create([
                    'Currency' => $data['Currency'],
                    'Pre_money_Valuation' => $data['pre_money_valuation'],
                    'raised_fund' => $data['raised_fund'],
                    'stage' => $data['stage'],
                    'targeted_market' => $data['targeted_market'],
                    'business_type' => $data['business_type'],
                ]);
                $msgtype = 'NewComp';
            }

            $msgtype = 'UpdateComp';
            if(!$companyId) {
                User::where('id', Auth::id())->update(['company_id' => $result->id]);
                $msgtype = 'NewComp';
            }

}
if($data['CompanyN']==5){
    $request->validate([
        'working_team' => 'required|string',
        'team_num' => 'required|numeric',
    ], [
        'working_team.required' => __('admin.Currency is required'),
        'team_num.required' => __('admin.Raised fund is required'),
        'team_num.numeric' => __('admin.Raised fund must be a number'),
    ]);
            // $result = Company::updateOrCreate(['id' => $companyId], $data);
            if ($companyId) {
                // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                $result = Company::where('id', $companyId)->update([
                    'team_num' => $data['team_num'],
                    'working_team_summary' => $data['working_team'],
                ]);
                $msgtype = 'UpdateComp';
            } else {
                // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                $result = Company::create([
                    'team_num' => $data['team_num'],
                    'working_team_summary' => $data['working_team'],
                ]);
                $msgtype = 'NewComp';
            }

            $msgtype = 'UpdateComp';
            if(!$companyId) {
                User::where('id', Auth::id())->update(['company_id' => $result->id]);
                $msgtype = 'NewComp';
            }

}
if($data['CompanyN']==6){
    
    $validator = Validator::make($request->all(), [
        'pitch_deck' => 'nullable|mimes:pdf,ppt,pptx|max:10240', // يجب أن يكون ملف PDF أو PPT وحجمه أقل من 10MB
        'pitch_deck_video' => 'nullable|url', // يجب أن يكون رابط صالح
        'pitch_deck_info' => 'nullable', // يجب أن يكون إما نعم أو لا
        'pitch_deck_summary' => 'nullable|string|max:500', // نص من 500 حرف كحد أقصى
        'problem_facing' => 'nullable|string|max:500', // نص من 500 حرف كحد أقصى
        'solution_problem' => 'nullable|string|max:500', // نص من 500 حرف كحد أقصى
        'documents.*' => 'nullable|mimes:pdf,ppt,pptx|max:20480', // مستند PDF أو PPT وحجمه لا يتجاوز 20MB
    ], [
        'pitch_deck.mimes' => __('admin.Pitch_Deck must be a pdf or ppt file'),
        'pitch_deck.max' => __('admin.Pitch_Deck max size is 10MB'),
        'pitch_deck_video.url' => __('admin.Pitch_Deck_Video must be a valid URL'),
        'pitch_deck_info.required' => __('admin.Pitch_Deck_Information is required'),
        'pitch_deck_summary.required' => __('admin.Pitch_Deck_Summary is required'),
        'pitch_deck_summary.string' => __('admin.Pitch_Deck_Summary must be a string'),
        'pitch_deck_summary.max' => __('admin.Pitch_Deck_Summary max length is 500 characters'),
        'documents.*.mimes' => __('admin.Documents must be a pdf or ppt file'),
        'documents.*.max' => __('admin.Documents max size is 20MB'),
    ]);
    
            // $result = Company::updateOrCreate(['id' => $companyId], $data);
            if ($companyId) {
                
                // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                $result = Company::where('id', $companyId)->update([
                    'pitch_deck' => $data['pitch_deck'],
                    'pitch_deck_video' => $data['pitch_deck_video'],
                    'pitch_deck_description' => $data['pitch_deck_info'],
                    'pitch_deck_summary' => $data['pitch_deck_summary'],
                    'documents' => isset($data['documents']) ? json_encode($data['documents']) : null,
                ]);
                $msgtype = 'UpdateComp';
                
            } else {
                // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                $result = Company::create([
                    'pitch_deck' => $data['pitch_deck'],
                    'pitch_deck_video' => $data['pitch_deck_video'],
                    'pitch_deck_description' => $data['pitch_deck_info'],
                    'pitch_deck_summary' => $data['pitch_deck_summary'],
                    'documents' => isset($data['documents']) ? json_encode($data['documents']) : null,
                ]);
                $msgtype = 'NewComp';
            }

            $msgtype = 'UpdateComp';
            if(!$companyId) {
                User::where('id', Auth::id())->update(['company_id' => $result->id]);
                $msgtype = 'NewComp';
            }

}
if($data['CompanyN']==8){
    
    $validator = Validator::make($request->all(), [
        'website' => 'nullable|url', // يجب أن يكون ملف PDF أو PPT وحجمه أقل من 10MB
        'img_url' => 'nullable|url', // يجب أن يكون رابط صالح
        'facebook' => 'nullable|url', // يجب أن يكون إما نعم أو لا
        'twitter' => 'nullable|url', // نص من 500 حرف كحد أقصى
        'linkedin' => 'nullable|url', // نص من 500 حرف كحد أقصى
        'youtube' => 'nullable|url', // نص من 500 حرف كحد أقصى
        'google_map' => 'nullable|url', // مستند PDF أو PPT وحجمه لا يتجاوز 20MB
    ],  [
        'website.url' => __('admin.Website_Not_URL'),
        'img_url.url' => __('admin.img_url_Not_URL'),
        'facebook.url' => __('admin.facebook_Not_URL'),
        'twitter.url' => __('admin.twitter_Not_URL'),
        'linkedin.url' => __('admin.linkedin_Not_URL'),
        'youtube.url' => __('admin.youtube_Not_URL'),
        'google_map.url' => __('admin.google_map_Not_URL'),
    ]);
    
            // $result = Company::updateOrCreate(['id' => $companyId], $data);
            if ($companyId) {
                
                // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                $result = Company::where('id', $companyId)->update([
                    'website' => $data['website'],
                    'logo' => $data['img_url'],
                    'facebook' => $data['facebook'],
                    'twitter' => $data['twitter'],
                    'linkedin' => $data['linkedin'],
                    'youtube' => $data['youtube'],
                    'google_map' => $data['google_map'],
                ]);
                $msgtype = 'UpdateComp';
                
            } else {
                // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                $result = Company::create([
                    'website' => $data['website'],
                    'logo' => $data['img_url'],
                    'facebook' => $data['facebook'],
                    'twitter' => $data['twitter'],
                    'linkedin' => $data['linkedin'],
                    'youtube' => $data['youtube'],
                    'google_map' => $data['google_map'],
                ]);
                $msgtype = 'NewComp';
            }

            $msgtype = 'UpdateComp';
            if(!$companyId) {
                User::where('id', Auth::id())->update(['company_id' => $result->id]);
                $msgtype = 'NewComp';
            }

}



               









            if($result) {
                event(new WebMessage($msgtype, $user->first_name_en));
                return redirect()->route('my_company')->with(['msg' => 'StoredSuccessfully', 'class' => 'success']);
            }
            else {
                return back()->withInput()->with(['msg' => 'FailedStore', 'class' => 'danger']);
                }
        }catch (ValidationException $e) {
            // عند وجود خطأ في التحقق، إرجاع رسائل الأخطاء للمستخدم
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            // تسجيل الأخطاء الأخرى
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            return redirect()->back()->with(['msg' => __('An unexpected error occurred, please try again later.'), 'class' => 'danger']);
        }
    }

     /**
     * Update/Store the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function likeService(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'company_id' => 'required|numeric',
                'service_id' => 'required|numeric',
                'current_state' => 'required',
                'rate' => 'numeric',
            ]);
            if ($validator->fails())
                return $this->validationErrorsResponse($validator->errors());
            $user = Auth::user()->id;
            $current_state = $request->current_state;
            $company_id = $request->company_id;
            $service_id = $request->service_id;
            if($current_state == 'unliked'){ //Like
                $result = Like::updateOrCreate(
                        $request->only(['company_id','service_id']) + ['user_id' => $user],
                        $request->all() + ['user_id' => $user]
                    );
            }elseif($current_state == 'liked'){ //Unlike
                Like::where('company_id', $company_id)
                        ->where('service_id', $service_id)
                        ->where('user_id', $user)
                        ->delete();
            }
            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been Deleted successfully";
            $dataContent = ['row_id'=> $result->id];
            if($result)
                return $this->returnApiResult($code, $status, $message, $dataContent);
            else
                return $this->unknownErrorHappenedMsg();
        }catch (Exception $e){ dd($e);
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }
   
    public function AddTeams(Request $request){
        $user = Auth::user();
            $companyId = $user->company_id;  
// تحقق من صحة البيانات
$validator = Validator::make($request->all(), [
    'founder_name' => 'required|string|max:255',
    'founder_email' => 'required|email|max:255',
    'founder_linkedin' => 'nullable|url|max:255',
], [
    'founder_name.required' => __('admin.Name is Required founder'),
    'founder_email.required' => __('admin.Email is Required founder'),
    'founder_email.email' => __('admin.Email is Not Correct founder'),
    'founder_linkedin.url' => __('admin.Linkedin URL is Not Correct founder'),
]);

// إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
if ($validator->fails()) {
    return response()->json([
        'errors' => $validator->errors(),
    ], 422); // حالة الخطأ 422
}

$founder = 'Founder';
// إنشاء وحفظ بيانات العضو في قاعدة البيانات
$teamMember = new Team_member(); // افترض أن لديك موديل اسمه TeamMember
$teamMember->TeamMember = $founder;
$teamMember->Name = $request->input('founder_name');
$teamMember->Email = $request->input('founder_email');
$teamMember->LinkedIn = $request->input('founder_linkedin');
$teamMember->Company_id = $companyId;
$teamMember->save();

// استجابة بعد الحفظ
return response()->json([
    'state' => __('admin.founder_added'),
]);
    }
    public function AddTeamsSherHolder(Request $request){
        
        $user = Auth::user();
            $companyId = $user->company_id;
            
        // تحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'shareholder_name' => 'required|string|max:255',
            'shareholder_role' => 'required|max:255',
            'shareholder_ownership_percentage' => 'required',
        ], [
            'shareholder_name.required' => __('admin.Name is Required Shareholder'),
            'shareholder_role.required' => __('admin.Role is Required Shareholder'),
            'shareholder_ownership_percentage.required' => __('admin.Precent is required Shareholder'),
        ]);
       
        // تحقق من النسب المئوية الكلية
        $totalPercentage = Team_member::where('Company_id', $companyId)
        ->sum('PresentagShereholder');

        $newPercentage = $request->shareholder_ownership_percentage;

            // تحقق من أن النسبة المئوية الجديدة لا تتجاوز 100%
            if ($totalPercentage + $newPercentage > 100) {
            return response()->json([
            'errors' => [
            'shareholder_ownership_percentage' => [__('admin.Total ownership percentage cannot exceed 100%')],
            ],
            ], 422); // حالة الخطأ 422
            }
        // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422); // حالة الخطأ 422
        }
        $founder = 'Shareholder';
        // إنشاء وحفظ بيانات العضو في قاعدة البيانات
        $teamMember = new Team_member(); // افترض أن لديك موديل اسمه TeamMember
        $teamMember->TeamMember = $founder;
        $teamMember->Name = $request->shareholder_name;
        $teamMember->RoleShereholder = $request->shareholder_role;
        $teamMember->PresentagShereholder = $request->shareholder_ownership_percentage;
        $teamMember->Company_id = $companyId;
        $teamMember->save();
        
        // استجابة بعد الحفظ
        return response()->json([
            'state' => __('admin.Shareholder_added'),
        ]);
        
    }

    public function AddTeamsInvistor(Request $request){
        $user = Auth::user();
            $companyId = $user->company_id;
        // تحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'investor_name' => 'required|string|max:255',
            'investment_type' => 'required|max:255',
        ], [
            'investor_name.required' => __('admin.Name is Required Investor'),
            'investment_type.required' => __('admin.Type is Required Investor'),
        ]);
       
        // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422); // حالة الخطأ 422
        }
        
        $founder = 'Investor';
        // إنشاء وحفظ بيانات العضو في قاعدة البيانات
        $teamMember = new Team_member(); // افترض أن لديك موديل اسمه TeamMember
        $teamMember->TeamMember = $founder;
        $teamMember->Name = $request->investor_name;
        $teamMember->investorType = $request->investment_type;
        $teamMember->Company_id = $companyId;
        $teamMember->save();
        
        // استجابة بعد الحفظ
        return response()->json([
            'state' => __('admin.Investor_added'),
        ]);
        

    }

    public function AddMileStone(Request $request){
        $user = Auth::user();
            $companyId = $user->company_id;
        // تحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'achieved_milestones' => 'required|string|max:255',
            'achieved_milestone_name' => 'required|max:255',
            'achieved_milestone_description' => 'required|max:255',
        ], [
            'achieved_milestones.required' => __('admin.Choose achieved milestones'),
            'achieved_milestone_name.required' => __('admin.Name is Required MileStone'),
            'achieved_milestone_description.required' => __('admin.milestone achieved description'),
        ]);
        
        // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422); // حالة الخطأ 422
        }
        
        // إنشاء وحفظ بيانات العضو في قاعدة البيانات
        $mileStone = new Milestone(); // افترض أن لديك موديل اسمه TeamMember
        $mileStone->plan = $request->achieved_milestones;
        $mileStone->name = $request->achieved_milestone_name;
        $mileStone->description = $request->achieved_milestone_description;
        $mileStone->Company_id = $companyId;
        $mileStone->save();
        
        // استجابة بعد الحفظ
        return response()->json([
            'state' => __('admin.MileStone_added'),
        ]);
        

    }











}
