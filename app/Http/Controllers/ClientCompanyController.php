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
use App\Stage;
use App\SustainableIcon;
use \Illuminate\Validation\ValidationException;
use URL, File, DB, Exception, Session, Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientCompanyController extends Controller
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
    public function show($id)
    {
        try {
            $company = Company::with('state')
                ->with('city')
                ->with('user')
                ->with('stage')
                ->findorfail($id);
            if (!empty($company->Sustainable_development)) {
                $sustainableDevelopment = json_decode($company->Sustainable_development, true);

                $icons = SustainableIcon::whereIn('title', $sustainableDevelopment)->get();
            } else {
                $icons = '';
            }
            // استرجاع الأيقونات المناسبة
            // $icons = SustainableIcon::where('title', $sustainableDevelopment)->get();

            $fonders = Team_Member::where('TeamMember', 'Founder')
                ->where('Company_id', $id)
                ->get();
            $Shareholders = Team_Member::where('TeamMember', 'Shareholder')
                ->where('Company_id', $id)->get();
            $Investors = Team_Member::where('TeamMember', 'Investor')
                ->where('Company_id', $id)->get();


            $Achieved_Milestones = Milestone::where('plan', 'Achieved_Milestones')
                ->where('Company_id', $id)->get();
            $Planned_Milestones = Milestone::where('plan', 'Planned_Milestones')
                ->where('Company_id', $id)->get();
            $Currencies = Currency::all(); // جلب جميع الأعضاء
            // مسار الملف
            $pathToFile = storage_path($company->pitch_deck);
            // تحقق من وجود الملف
            $fileExists = file_exists($pathToFile);




            return view('admin/clients/company_profile', compact('company', 'Currencies', 'Achieved_Milestones', 'Planned_Milestones', 'icons', 'fonders', 'Shareholders', 'Investors', 'fileExists', 'pathToFile'));
        } catch (Exception $e) {
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
    public function editOrCreate()
    {


        try {
            $client = User::where('HaveCompany', 1)->with('company')
                ->with('company.city')
                ->find(Auth::id());
            if (empty($client)) {
                return redirect()->route('my_profile')->with(['msg' => 'NoHaveCompany', 'class' => 'danger']);
            }
            $countries = Country::all();
            $states = State::all();
            $Setting = Settings::where('id', 1)->first();
            $sectors = Sector::whereNull('parent_id')->get();
            $funding_rounds = auth()->user()->company ? auth()->user()->company->fundingRounds : [];

            $teamMembers = Team_Member::all(); // جلب جميع الأعضاء

            if (auth()->user()->company) {
                $fonders = Team_Member::where('TeamMember', 'Founder')
                    ->where('Company_id', auth()->user()->company->id)->get();
                $Shareholders = Team_Member::where('TeamMember', 'Shareholder')
                    ->where('Company_id', auth()->user()->company->id)->get();
                $Investors = Team_Member::where('TeamMember', 'Investor')
                    ->where('Company_id', auth()->user()->company->id)->get();





                $Achieveds = Milestone::where('plan', 'Achieved_Milestones')
                    ->where('Company_id', auth()->user()->company->id)->get();
                $Planneds = Milestone::where('plan', 'Planned_Milestones')
                    ->where('Company_id', auth()->user()->company->id)->get();
                $Archiveds = Milestone::where('plan', 'Archived_Milestones')
                    ->where('Company_id', auth()->user()->company->id)->get();
            } else {
                $fonders = 'NA';
                $Shareholders = 'NA';
                $Investors = 'NA';
                $Achieveds = 'NA';
                $Planneds = 'NA';
                $Archiveds = 'NA';
            }
            $Stages = Stage::all();

            $Currencies = Currency::all(); // جلب جميع الأعضاء
            $json = File::get(base_path('database/dbFiles/countries.json'));
            $_countries = json_decode($json, true);
            $_countries = collect($_countries)->map(function ($country) {
                $country['flag'] = URL::to('app-assets/images/flags/' . strtolower($country['iso2']) . '.svg');
                return $country;
            });
            $countries = $countries->map(function ($country) use ($_countries) {
                $getCountry = $_countries->where('iso3', $country->iso3)->first();
                $flag = null;
                $emoji = null;
                if (!empty($getCountry)) {
                    $flag = $getCountry['flag'];
                    $emoji = $getCountry['emoji'];
                }
                $country->flag = $flag;
                $country->emoji = $emoji;
                return $country;
            });
            return view('admin/clients/company', compact(
                'client',
                '_countries',
                'fonders',
                'Shareholders',
                'Investors',
                'Currencies',
                'Achieveds',
                'Planneds',
                'Archiveds',
                'teamMembers',
                'states',
                'countries',
                'sectors',
                'funding_rounds',
                'Setting',
                'Stages'
            ));
        } catch (Exception $e) {
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
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

        try {
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


            if ($data['CompanyN'] == 1) {

                $validatedData = $request->validate([
                    'name_en' => 'required|string|max:255',
                    'name_ar' => 'required|string|max:255',
                    'country_id' => 'required|exists:countries,id',
                    'state_id' => 'required|exists:states,id',
                    'sector' => 'required|string|max:255',
                    'address_en' => 'required|string|max:500',
                    'address_ar' => 'required|string|max:500',
                    'est_date' => 'required|date',
                    'years_of_operations' => 'required|string|in:0-2,2-5,5-10,10+',
                    'product_or_service' => 'required|string|in:Product,Service,Product + Service',
                    'business_categories' => 'nullable|array',
                    'business_categories.*' => 'string|in:Food & Beverage,Technology,App / Website,Housewares / Home Design,Health / Wellness / Nutrition,Fitness,Sports,Beauty,Clothing / Fashion,Toys / Games,Children,Entertainment / Experiential,Pets,Music,Holiday,Other',
                    'other_category' => 'nullable|string|max:255',
                ], [
                    'name_en.required' => __('admin.English Name is required'),
                    'name_ar.required' => __('admin.Arabic Name is required'),
                    'country_id.required' => __('admin.Country is required'),
                    'country_id.exists' => __('admin.Invalid Country selected'),
                    'state_id.required' => __('admin.State is required'),
                    'state_id.exists' => __('admin.Invalid State selected'),
                    'sector.required' => __('admin.Sector is required'),
                    'address_en.required' => __('admin.English Address is required'),
                    'address_ar.required' => __('admin.Arabic Address is required'),
                    'est_date.required' => __('admin.Established Date must be a valid date'),
                    'years_of_operations.required' => __('admin.Years of Operations is required'),
                    'years_of_operations.in' => __('admin.Invalid Years of Operations selection'),
                    'product_or_service.required' => __('admin.Product or Service is required'),
                    'product_or_service.in' => __('admin.Invalid Product or Service selection'),
                ]);

                // Handle "Other" category logic
                if (in_array('Other', $validatedData['business_categories'] ?? [])) {
                    $validatedData['business_categories'] = array_diff($validatedData['business_categories'], ['Other']);
                    if (!empty($validatedData['other_category'])) {
                        $validatedData['business_categories'][] = $validatedData['other_category'];
                    }
                }

                $validatedData['business_categories'] = json_encode($validatedData['business_categories']);

                // $result = Company::updateOrCreate(['id' => $companyId], values: $data);

                // $msgtype = 'UpdateComp';

                if ($data['CreatedTax'] == 0) {
                    $CreatedTax = 0;
                    $cuntryReg = 0;
                    $Commercial_Register = '';
                    $Tax_Card = '';
                } else {
                    $CreatedTax = $data['CreatedTax'];
                    $cuntryReg = $data['Country_Register'];
                    $Commercial_Register = $data['Commercial_Register'];
                    $Tax_Card = $data['Tax_Card'];
                }



                if ($companyId) {

                    // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                    $result = Company::where('id', $companyId)->update([
                        'name_en' => $data['name_en'],
                        'name_ar' => $data['name_ar'],
                        'country_id' => $data['country_id'],
                        'state_id' => $data['state_id'],
                        'sector' => $data['sector'],
                        'address_en' => $data['address_en'],
                        'address_ar' => $data['address_ar'],
                        'est_date' => $data['est_date'],
                        'years_of_operations' => $data['years_of_operations'],
                        'product_or_service' => $data['product_or_service'],
                        'business_categories' => $validatedData['business_categories'],
                        'CreatedTax' => $CreatedTax,
                        'Commercial_Register' => $Commercial_Register,
                        'Tax_Card' => $Tax_Card,
                        'Country_Register_id' => $cuntryReg,
                    ]);


                    $msgtype = 'UpdateComp';
                } else {
                    $institutions = 'null';
                    $email = 'null';
                    $phone = '0';

                    // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                    $result = Company::create([
                        'name_en' => $data['name_en'],
                        'name_ar' => $data['name_ar'],
                        'country_id' => $data['country_id'],
                        'state_id' => $data['state_id'],
                        'sector' => $data['sector'],
                        'business_categories' => $validatedData['business_categories'],
                        'address_en' => $data['address_en'],
                        'address_ar' => $data['address_ar'],
                        'est_date' => $data['est_date'],
                        'years_of_operations' => $data['years_of_operations'],
                        'product_or_service' => $data['product_or_service'],
                        'CreatedTax' => $CreatedTax,
                        'Commercial_Register' => $Commercial_Register,
                        'Tax_Card' => $Tax_Card,
                        'Country_Register_id' => $cuntryReg,
                        'institutions' => $institutions,
                        'email' => $email,
                        'phone' => $phone,

                    ]);
                    $msgtype = 'NewComp';
                }


                if (!$companyId) {
                    User::where('id', Auth::id())->update(['company_id' => $result->id]);
                    $msgtype = 'NewComp';
                }
            }
            if ($data['CompanyN'] == 2) {

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
                        'country_code' => $data['country_code'],  // مثال لإضافة حقل الهاتف
                        'phone' => $data['phone'],  // مثال لإضافة حقل الهاتف
                        'whatsapp' => $data['WhatsApp'],  // مثال لإضافة حقل الواتساب
                        'email' => $data['email'],  // مثال لإضافة حقل البريد الإلكتروني
                    ]);

                    $msgtype = 'UpdateComp';
                } else {
                    // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                    $result = Company::create([
                        'country_code' => $data['country_code'],  // مثال لإضافة حقل الهاتف
                        'phone' => $data['phone'],  // مثال لإضافة حقل الهاتف
                        'whatsapp' => $data['WhatsApp'],  // مثال لإضافة حقل الواتساب
                        'email' => $data['email'],  // مثال لإضافة حقل البريد الإلكتروني
                    ]);

                    $msgtype = 'NewComp';
                }

                $msgtype = 'UpdateComp';
                if (!$companyId) {
                    User::where('id', Auth::id())->update(['company_id' => $result->id]);
                    $msgtype = 'NewComp';
                }
            }
            if ($data['CompanyN'] == 3) {
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
                if (!$companyId) {
                    User::where('id', Auth::id())->update(['company_id' => $result->id]);
                    $msgtype = 'NewComp';
                }
            }
            if ($data['CompanyN'] == 4) {

                $request->validate([
                    'Currency' => 'required',
                    'pre_money_valuation' => 'required|numeric|min:0',
                    'raised_fund' => 'required|numeric|min:0',
                    'Funding_Target' => 'required|numeric|min:0',
                    'stage' => 'required',
                    'development_stage' => 'required',
                    'targeted_market.*' => 'required',
                    'business_type' => 'required',
                ], [
                    'Currency.required' => __('admin.Currency is required'),

                    'pre_money_valuation.required' => __('admin.Pre-money valuation is required'),
                    'pre_money_valuation.numeric' => __('admin.Pre-money valuation must be a number'),
                    'pre_money_valuation.min' => __('admin.Pre-money valuation must be a positive number'),
                    'raised_fund.required' => __('admin.Raised fund is required'),
                    'raised_fund.numeric' => __('admin.Raised fund must be a number'),
                    'raised_fund.min' => __('admin.Raised fund must be a positive number'),
                    'stage.required' => __('admin.Stage is required'),
                    'development_stage.required' => __('admin.development_stage is required'),
                    'targeted_market.*.required' => __('admin.Targeted market is required'),
                    'business_type.required' => __('admin.Business type is required'),
                ]);

                // $result = Company::updateOrCreate(['id' => $companyId], $data);
                if ($companyId) {

                    // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                    $result = Company::where('id', $companyId)->update([
                        'Currency' => $data['Currency'],
                        'Pre_money_Valuation' => $data['pre_money_valuation'],
                        'raised_fund' => $data['raised_fund'],
                        'Funding_Target' => $data['Funding_Target'],
                        'stage' => $data['stage'],
                        'development_stage' => $data['development_stage'],
                        'targeted_market' => json_encode($data['targeted_market']),
                        'business_type' => $data['business_type'],
                        'financial_plan' => $data['financial_plan'],
                        'financial_plan_description' => $data['financial_plan_description'],
                    ]);


                    $msgtype = 'UpdateComp';
                } else {
                    // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                    $result = Company::create([
                        'Currency' => $data['Currency'],
                        'Pre_money_Valuation' => $data['pre_money_valuation'],
                        'raised_fund' => $data['raised_fund'],
                        'Funding_Target' => $data['Funding_Target'],
                        'stage' => $data['stage'],
                        'development_stage' => $data['development_stage'],
                        'targeted_market' => json_encode($data['targeted_market']),
                        'business_type' => $data['business_type'],
                        'financial_plan' => $data['financial_plan'],
                        'financial_plan_description' => $data['financial_plan_description'],
                    ]);
                    $msgtype = 'NewComp';
                }

                $msgtype = 'UpdateComp';
                if (!$companyId) {
                    User::where('id', Auth::id())->update(['company_id' => $result->id]);
                    $msgtype = 'NewComp';
                }
            }
            if ($data['CompanyN'] == 5) {

                $request->validate([
                    'team_num' => 'required|string',
                ], [
                    'team_num.numeric' => __('admin.Raised fund must be a number'),
                ]);
                // $result = Company::updateOrCreate(['id' => $companyId], $data);
                if ($companyId) {
                    // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                    $result = Company::where('id', $companyId)->update([
                        'team_num' => $data['team_num'],
                        'working_team_summary' => $data['working_team_summary'],
                    ]);
                    $msgtype = 'UpdateComp';
                } else {
                    // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                    $result = Company::create([
                        'team_num' => $data['team_num'],
                        'working_team_summary' => $data['working_team_summary'],
                    ]);
                    $msgtype = 'NewComp';
                }

                $msgtype = 'UpdateComp';
                if (!$companyId) {
                    User::where('id', Auth::id())->update(['company_id' => $result->id]);
                    $msgtype = 'NewComp';
                }
            }
            if ($data['CompanyN'] == 6) {

                $validator = Validator::make($request->all(), [
                    'pitch_deck_info' => 'nullable', // يجب أن يكون إما نعم أو لا
                    'pitch_deck_summary' => 'nullable|string|max:500', // نص من 500 حرف كحد أقصى
                    'problem_facing' => 'nullable|string|max:500', // نص من 500 حرف كحد أقصى
                    'solution_problem' => 'nullable|string|max:500', // نص من 500 حرف كحد أقصى
                ], [
                    'pitch_deck_info.required' => __('admin.Pitch_Deck_Information is required'),
                    'pitch_deck_summary.required' => __('admin.Pitch_Deck_Summary is required'),
                    'pitch_deck_summary.string' => __('admin.Pitch_Deck_Summary must be a string'),
                    'pitch_deck_summary.max' => __('admin.Pitch_Deck_Summary max length is 500 characters'),
                ]);


                // $result = Company::updateOrCreate(['id' => $companyId], $data);
                if ($companyId) {

                    // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                    $result = Company::where('id', $companyId)->update([
                        'pitch_deck_description' => $data['pitch_deck_info'],
                        'pitch_deck_summary' => $data['pitch_deck_summary'],
                        'problem' => $data['problem_facing'],
                        'solution' => $data['solution_problem'],
                    ]);
                    $msgtype = 'UpdateComp';
                } else {
                    // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                    $result = Company::create([
                        'pitch_deck_description' => $data['pitch_deck_info'],
                        'pitch_deck_summary' => $data['pitch_deck_summary'],
                        'problem' => $data['problem_facing'],
                        'solution' => $data['solution_problem'],
                    ]);
                    $msgtype = 'NewComp';
                }


                if (!$companyId) {
                    User::where('id', Auth::id())->update(['company_id' => $result->id]);
                    $msgtype = 'NewComp';
                }
            }
            if ($data['CompanyN'] == 8) {

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
                if (!$companyId) {
                    User::where('id', Auth::id())->update(['company_id' => $result->id]);
                    $msgtype = 'NewComp';
                }
            }
            if ($data['CompanyN'] == 9) {
                $validator = Validator::make($request->all(), [
                    'pitch_deck' => 'nullable|mimes:pdf,ppt,pptx|max:10240', // يجب أن يكون ملف PDF أو PPT وحجمه أقل من 10MB
                ], [
                    'pitch_deck.mimes' => __('admin.Pitch_Deck must be a pdf or ppt file'),
                    'pitch_deck.max' => __('admin.Pitch_Deck max size is 10MB'),

                ]);
                // $result = Company::updateOrCreate(['id' => $companyId], $data);
                if ($companyId) {
                    // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                    $result = Company::where('id', $companyId)->update([
                        'pitch_deck' => $data['pitch_deck'],
                    ]);
                    $msgtype = 'UpdateComp';
                } else {
                    // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                    $result = Company::create([
                        'pitch_deck' => $data['pitch_deck'],
                    ]);
                    $msgtype = 'NewComp';
                }
                if (!$companyId) {
                    User::where('id', Auth::id())->update(['company_id' => $result->id]);
                    $msgtype = 'NewComp';
                }
            }
            if ($data['CompanyN'] == 10) {
                $validator = Validator::make($request->all(), [
                    'pitch_deck_video' => 'nullable|url',
                ]);
                // $result = Company::updateOrCreate(['id' => $companyId], $data);
                if ($companyId) {
                    // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                    $result = Company::where('id', $companyId)->update([
                        'pitch_deck_video' => $data['pitch_deck_video'],
                    ]);
                    $msgtype = 'UpdateComp';
                } else {
                    // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                    $result = Company::create([
                        'pitch_deck_video' => $data['pitch_deck_video'],
                    ]);
                    $msgtype = 'NewComp';
                }
                if (!$companyId) {
                    User::where('id', Auth::id())->update(['company_id' => $result->id]);
                    $msgtype = 'NewComp';
                }
            }
            if ($data['CompanyN'] == 11) {
                // $validator = Validator::make($request->all(), [
                //     'documents.*' => 'nullable|array|mimes:pdf,ppt,pptx|max:20480',
                // ],[
                //     'documents.*.mimes' => __('admin.Documents must be a pdf or ppt file'),
                //     'documents.*.max' => __('admin.Documents max size is 20MB'),
                // ]);
                // $result = Company::updateOrCreate(['id' => $companyId], $data);
                if ($companyId) {

                    // dd($data['documents']);
                    // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                    $result = Company::where('id', $companyId)->update([
                        'documents' =>  $data['documents'],
                    ]);

                    $msgtype = 'UpdateComp';
                } else {
                    // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                    $result = Company::create([
                        'documents' => $data['documents'],
                    ]);
                    $msgtype = 'NewComp';
                }
                if (!$companyId) {
                    User::where('id', Auth::id())->update(['company_id' => $result->id]);
                    $msgtype = 'NewComp';
                }
            }
            if ($data['CompanyN'] == 12) {

                $request->validate([
                    'about_problem' => 'required',
                    'about_market_opportunity' => 'required',
                    'about_competitors' => 'required',
                    'about_monetize' => 'required',
                    'about_challenges' => 'required',
                ], [
                    'about_problem.required' => __('admin.about_problem is required'),
                    'about_market_opportunity.required' => __('admin.about_market_opportunity is required'),
                    'about_competitors.required' => __('admin.about_competitors is required'),
                    'about_monetize.required' => __('admin.about_monetize is required'),
                    'about_challenges.required' => __('admin.about_challenges is required'),
                ]);
                // $result = Company::updateOrCreate(['id' => $companyId], $data);
                if ($companyId) {
                    // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                    $result = Company::where('id', $companyId)->update([
                        'about_problem' => $data['about_problem'],
                        'about_market_opportunity' => $data['about_market_opportunity'],
                        'about_competitors' => $data['about_competitors'],
                        'about_monetize' => $data['about_monetize'],
                        'about_challenges' => $data['about_challenges'],
                    ]);
                    $msgtype = 'UpdateComp';
                } else {
                    // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                    $result = Company::create([
                        'about_problem' => $data['about_problem'],
                        'about_market_opportunity' => $data['about_market_opportunity'],
                        'about_competitors' => $data['about_competitors'],
                        'about_monetize' => $data['about_monetize'],
                        'about_challenges' => $data['about_challenges'],
                    ]);
                    $msgtype = 'NewComp';
                }

                $msgtype = 'UpdateComp';
                if (!$companyId) {
                    User::where('id', Auth::id())->update(['company_id' => $result->id]);
                    $msgtype = 'NewComp';
                }
            }
            if ($data['CompanyN'] == 13) {

                $request->validate([
                    'business_model' => 'required',
                    'business_model_description' => 'required',
                    'business_plan' => 'required',
                ], [
                    'business_model.required' => __('admin.business_model is required'),
                    'business_model_description.required' => __('admin.business_model_description is required'),
                    'business_plan.required' => __('admin.business_plan is required'),
                ]);
                // $result = Company::updateOrCreate(['id' => $companyId], $data);
                if ($companyId) {
                    // إذا كان موجوداً، نقوم بتحديث البيانات بناءً على الـ companyId
                    $result = Company::where('id', $companyId)->update([
                        'business_model' => $data['business_model'],
                        'other_business_model' => $data['other_business_model'],
                        'business_model_description' => $data['business_model_description'],
                        'business_plan' => $data['business_plan'],
                    ]);
                    $msgtype = 'UpdateComp';
                } else {
                    // إذا لم يكن هناك companyId، نستخدم `create` لإضافة مدخل جديد
                    $result = Company::create([
                        'business_model' => $data['business_model'],
                        'other_business_model' => $data['other_business_model'],
                        'business_model_description' => $data['business_model_description'],
                        'business_plan' => $data['business_plan'],
                    ]);
                    $msgtype = 'NewComp';
                }

                $msgtype = 'UpdateComp';
                if (!$companyId) {
                    User::where('id', Auth::id())->update(['company_id' => $result->id]);
                    $msgtype = 'NewComp';
                }
            }


            if ($result) {
                event(new WebMessage($msgtype, $user->first_name_en));
                return redirect()->route('my_company')->with(['msg' => 'StoredSuccessfully', 'class' => 'success']);
            } else {
                return back()->withInput()->with(['msg' => 'FailedStore', 'class' => 'danger']);
            }
        } catch (ValidationException $e) {
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
    public function likeService(Request $request)
    {
        try {
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
            if ($current_state == 'unliked') { //Like
                $result = Like::updateOrCreate(
                    $request->only(['company_id', 'service_id']) + ['user_id' => $user],
                    $request->all() + ['user_id' => $user]
                );
            } elseif ($current_state == 'liked') { //Unlike
                Like::where('company_id', $company_id)
                    ->where('service_id', $service_id)
                    ->where('user_id', $user)
                    ->delete();
            }
            //build the response
            $code = 200; //successful Request:
            $status = 'success';
            $message = "The data has been Deleted successfully";
            $dataContent = ['row_id' => $result->id];
            if ($result)
                return $this->returnApiResult($code, $status, $message, $dataContent);
            else
                return $this->unknownErrorHappenedMsg();
        } catch (Exception $e) {
            dd($e);
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }

    public function AddTeams(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;
        if ($request->TeamsNo == "Founders") {
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
        if ($request->TeamsNo == "Shareholders") {

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

        if ($request->TeamsNo == "Investors") {

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
    }

    public function EditTeams(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // إنشاء وحفظ بيانات العضو في قاعدة البيانات
        $teamMember = Team_member::where('id', $request->id)->first(); // افترض أن لديك موديل اسمه TeamMember
        if ($teamMember->TeamMember == "Founder") {

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

            $teamMember->Name = $request->input('founder_name');
            $teamMember->Email = $request->input('founder_email');
            $teamMember->LinkedIn = $request->input('founder_linkedin');
            $teamMember->save();

            // استجابة بعد الحفظ
            return response()->json([
                'state' => __('admin.founder_Edited'),
            ]);
        } elseif ($teamMember->TeamMember == "Shareholder") {

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

            // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422); // حالة الخطأ 422
            }


            // تحقق من النسب المئوية الكلية
            $totalPercentage = Team_member::where('Company_id', $companyId)
                ->where('id', '!=', $request->id)
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
            $teamMember->Name = $request->shareholder_name;
            $teamMember->RoleShereholder = $request->shareholder_role;
            $teamMember->PresentagShereholder = $request->shareholder_ownership_percentage;
            $teamMember->save();
            // استجابة بعد الحفظ
            return response()->json([
                'state' => __('admin.Shareholder_Edited'),
            ]);
        } elseif ($teamMember->TeamMember == "Investor") {
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

            $teamMember->Name = $request->investor_name;
            $teamMember->investorType = $request->investment_type;
            $teamMember->save();
            // استجابة بعد الحفظ
            return response()->json([
                'state' => __('admin.Investor_Edited'),
            ]);
        }
    }


    public function DeleteTeams(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // إنشاء وحفظ بيانات العضو في قاعدة البيانات
        $teamMember = Team_member::where('id', $request->id)->first(); // افترض أن لديك موديل اسمه TeamMember

        $teamMember->delete();

        // استجابة بعد الحفظ
        return redirect()->route('my_company')->with(['msg' => 'StoredSuccessfully', 'class' => 'success']);
    }



    public function AddMileStone(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;


        if ($request->Plan == "Achieved_Milestones") {
            // تحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'Plan' => 'required|string|max:255',
                'achieved_milestone_name' => 'required|max:255',
                'achieved_milestone_description' => 'required|max:255',
            ], [
                'Plan.required' => __('admin.Choose achieved milestones'),
                'achieved_milestone_name.required' => __('admin.Name is Required MileStone'),
                'achieved_milestone_description.required' => __('admin.milestone achieved description'),
            ]);

            // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422); // حالة الخطأ 422
            }

            $plan = $request->Plan;
            $name = $request->achieved_milestone_name;
            $desc = $request->achieved_milestone_description;
        }
        if ($request->Plan == "Planned_Milestones") {
            // تحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'Plan' => 'required|string|max:255',
                'planned_milestone_name' => 'required|max:255',
                'planned_milestone_description' => 'required|max:255',
            ], [
                'Plan.required' => __('admin.Choose achieved milestones'),
                'planned_milestone_name.required' => __('admin.Name is Required MileStone'),
                'planned_milestone_description.required' => __('admin.milestone achieved description'),
            ]);

            // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422); // حالة الخطأ 422
            }

            $plan = $request->Plan;
            $name = $request->planned_milestone_name;
            $desc = $request->planned_milestone_description;
        }
        if ($request->Plan == "Archived_Milestones") {
            // تحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'Plan' => 'required|string|max:255',
                'Archived_milestone_name' => 'required|max:255',
                'Archived_milestone_description' => 'required|max:255',
            ], [
                'Plan.required' => __('admin.Choose achieved milestones'),
                'Archived_milestone_name.required' => __('admin.Name is Required MileStone'),
                'Archived_milestone_description.required' => __('admin.milestone achieved description'),
            ]);

            // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422); // حالة الخطأ 422
            }

            $plan = $request->Plan;
            $name = $request->Archived_milestone_name;
            $desc = $request->Archived_milestone_description;
        }

        // إنشاء وحفظ بيانات العضو في قاعدة البيانات
        $mileStone = new Milestone(); // افترض أن لديك موديل اسمه TeamMember
        $mileStone->plan = $plan;
        $mileStone->name = $name;
        $mileStone->description = $desc;
        $mileStone->Company_id = $companyId;
        $mileStone->save();

        // استجابة بعد الحفظ
        return response()->json([
            'state' => __('admin.MileStone_added'),
        ]);
    }

    public function EditMileStone(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $id = $request->id;


        $mileStone = Milestone::where('id', $id)->first(); // افترض أن لديك موديل اسمه


        if ($mileStone->plan == "Achieved_Milestones") {
            // تحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'Achieved_name' => 'required|max:255',
                'Achieved_desc' => 'required|max:255',
            ], [
                'Achieved_name.required' => __('admin.Name is Required MileStone'),
                'Achieved_desc.required' => __('admin.milestone achieved description'),
            ]);

            // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422); // حالة الخطأ 422
            }


            $name = $request->Achieved_name;
            $desc = $request->Achieved_desc;
        }

        if ($mileStone->plan == "Planned_Milestones") {

            // تحقق من صحة البيانات
            $validator = Validator::make($request->all(), [

                'Planned_name' => 'required|max:255',
                'Planned_desc' => 'required|max:255',
            ], [

                'Planned_name.required' => __('admin.Name is Required MileStone'),
                'Planned_desc.required' => __('admin.milestone achieved description'),
            ]);

            // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422); // حالة الخطأ 422
            }


            $name = $request->Planned_name;
            $desc = $request->Planned_desc;
        }
        if ($mileStone->plan == "Archived_Milestones") {
            // تحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'Archived_name' => 'required|max:255',
                'Archived_desc' => 'required|max:255',
            ], [
                'Archived_name.required' => __('admin.Name is Required MileStone'),
                'Archived_desc.required' => __('admin.milestone achieved description'),
            ]);

            // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422); // حالة الخطأ 422
            }

            $name = $request->Archived_name;
            $desc = $request->Archived_desc;
        }

        // إنشاء وحفظ بيانات العضو في قاعدة البيانات


        $mileStone->name = $name;
        $mileStone->description = $desc;
        $mileStone->save();

        // استجابة بعد الحفظ
        return response()->json([
            'state' => __('admin.MileStone_Edited'),
        ]);
    }
    public function DeleteMileStone(Request $request)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // إنشاء وحفظ بيانات العضو في قاعدة البيانات
        $Milestone = Milestone::where('id', $request->id)->first(); // افترض أن لديك موديل اسمه TeamMember

        $Milestone->delete();

        // استجابة بعد الحفظ
        return redirect()->route('my_company')->with(['msg' => 'StoredSuccessfully', 'class' => 'success']);
    }


    
}
