<?php

namespace App\Http\Controllers;

use App\City;
use App\Like;
use App\Step;
use App\User;
use App\Cycle;
use App\Stage;
use App\State;
use App\Sector;
use App\Company;
use App\Country;
use App\Currency;
use App\Settings;
use App\Milestone;
use App\Technology;
use App\Team_member;
use App\FundingRound;
use App\Organization;
use App\StaticCategory;
use App\CompanyRelation;
use App\SustainableIcon;
use App\Events\WebMessage;
use App\ScreeningEvaluation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\CompanyService;
use App\ScreeningEvaluationResults;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use \Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManagerStatic as Image;
use App\Http\Requests\StoreUpdateClientCompanyRequest;
use URL, File, DB, Exception, Session, Storage, stdClass;

class ClientCompanyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected CompanyService $companyService;
    public function __construct(CompanyService $companyService)
    {
        $this->middleware('auth');
        $this->companyService = $companyService;
    }
    public function show($id)
    {
        // try {
            $user = auth()->user();
            $user_id = $user->id;
            $company = Company::with('state')->with(['city', 'user', 'stage'])->find($id);
            if( empty( $company ) ) { return abort(404); }

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


            // Load all milestones for this company (new unified structure)
            $milestones = Milestone::where('Company_id', $id)->orderBy('created_at')->get();
            
            // For backward compatibility, create empty collections for old template variables
            $Achieved_Milestones = collect();
            $Planned_Milestones = collect();
            $Currencies = Currency::where('enabled',1)->get(); // جلب جميع الأعضاء
            
            // Startup Technologies
            $technologies = CompanyRelation::where('company_id', $id)
                ->whereNotNull('technology_id')
                ->with('technology')
                ->get()
                ->pluck('technology')
                ->filter();
            
            // Startup Sectors
            $sectors = CompanyRelation::where('company_id', $id)
                ->whereNotNull('sector_id')
                ->with('sector')
                ->get()
                ->pluck('sector')
                ->filter();
            
            // Startup Industry
            $industry = CompanyRelation::where('company_id', $id)
                ->whereNotNull('industry_id')
                ->with('industry')
                ->get()
                ->pluck('industry')
                ->filter()
                ->first(); // Usually one industry per company
                
            // مسار الملف
            $pathToFile = storage_path($company->pitch_deck);
            // تحقق من وجود الملف
            $fileExists = file_exists($pathToFile);
            $company_current_step = $company->step;
            $evaluationForm = ScreeningEvaluation::getEvaluationData( $company->step );
            $isResponsesPublished = false;
            $final_comment = null;
            $formData = collect([]);
            if( !empty( $evaluationForm ) )
            {
                $current_step = $company->stepdata;
                if( $current_step->stype == 2 ) { $form = Step::where('cycle_id', $current_step?->cycle_id)->where('arr', $current_step?->arr - 1)->first(); }
                else
                {
                    $form = Step::where('cycle_id', $current_step?->cycle_id)->where('arr', $current_step?->arr)->first();
                }
                $formData = $form->formData;
                if( !empty( $formData ) ) { $formData = $formData->where('company_id', $company->id)->values(); }
                $formData = $formData->map(function ($item) {
                    $field = $item->field()->first();
                    $field_type = '';
                    if( isset( $field?->options?->type ) ) { $field_type = $field?->options?->type; }
                    $item->field_type = $field_type;
                    return $item;
                });
                $myResponses = ScreeningEvaluationResults::where('company_id', $company->id)->where('evaluation_id', $evaluationForm->id)->where('user_id', $user_id)->get();
                $isResponsesPublished = $myResponses->where('status', true)->count() > 0 ? true : false;
                $myComment = ScreeningEvaluation::getMyFinalComment( $evaluationForm->id, $user_id, $company->id );
                $final_comment = $myComment->value;
                $evaluationForm->final_comment = $final_comment;
                $evaluationForm->categories->map(function ( $category ) use ( $myResponses ) {
                    $category->subCriteria->map(function ( $subCategory ) use ( $myResponses ) {
                        $checkResponse = $myResponses->where('criteria_id', $subCategory->id)->first();
                        $subCategory->result = new stdClass;
                        $subCategory->result->score = $checkResponse?->score;
                        $subCategory->result->comment = $checkResponse?->comment;
                        return $subCategory;
                    });
                    return $category;
                });
                $evaluationForm->isPublished = $isResponsesPublished;
            }
            return view('admin/clients/startup/company_profile', compact('company', 'formData', 'evaluationForm', 'Currencies', 'milestones', 'Achieved_Milestones', 'Planned_Milestones', 'icons', 'fonders', 'Shareholders', 'Investors', 'fileExists', 'pathToFile', 'technologies', 'sectors', 'industry'));
        // } catch (Exception $e) {
        //     //log what happend
        //     Log::channel('system_exceptions')->info('Exceptions:', [$e]);
        //     abort(500);
        // }
    }
    /**
     * Show the form for editing/Createing the specified resource.
     *
     * @param  \App\Company  $Type
     * @return \Illuminate\Http\Response
     */
    public function editOrCreate($id = null)
    {

        try {
            if ($id == null) {
                $client = User::where('HaveCompany', 1)->with('company')
                    ->with('company.city')
                    ->find(Auth::id());
                    if (empty($client)) {
                    return redirect()->route('user.profile_view')->with(['msg' => 'NoHaveCompany', 'class' => 'danger']);
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

                    // Load all milestones for this company (new unified structure)
                    $milestones = Milestone::where('Company_id', auth()->user()->company->id)
                        ->orderBy('created_at')->get();
                    
                    // For backward compatibility, create empty collections
                    $Achieveds = collect();
                    $Planneds = collect();
                    $Archiveds = collect();
                } else {
                    $fonders = 'NA';
                    $Shareholders = 'NA';
                    $Investors = 'NA';
                    $milestones = collect();
                    $Achieveds = 'NA';
                    $Planneds = 'NA';
                    $Archiveds = 'NA';
                }
                $Stages = Stage::all();

                $Currencies = Currency::where('enabled',1)->get(); // جلب جميع الأعضاء
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
            } else {
                
                // if (!Auth::user()->hasAnyRole('Admin|organizationManager')) {
                //     return redirect()->back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']);
                // }
             
                if (Auth::user()->hasRole('organizationManager')) {
                    $userorg = Organization::where('user_id', Auth::id())->first();
                    $cycles = Cycle::where('organization_id', $userorg->id)->with('companies')->get();
                    foreach ($cycles as $cycle) {
                        foreach ($cycle->companies as $company) {
                            if ($company->id == $id) {
                                $id = $company->id;
                                break;
                            } else {
                                $id = null;
                            }
                        }
                    }
                }
                if ($id == null) {
                    return redirect()->back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']);
                }
                $Company = Company::find($id);



                $client = User::where('company_id', $Company->id)->with('company')
                    ->with('company.city')
                    ->first();

                if (empty($client)) {
                    return redirect()->route('user.profile_view')->with(['msg' => 'NoHaveCompany', 'class' => 'danger']);
                }
                $countries = Country::all();
                $states = State::all();
                $Setting = Settings::where('id', 1)->first();
                $sectors = Sector::whereNull('parent_id')->get();
                $funding_rounds = $client->company ? $client->company->fundingRounds : [];

                $teamMembers = Team_Member::all(); // جلب جميع الأعضاء

                if ($client->company) {
                    $fonders = Team_Member::where('TeamMember', 'Founder')
                        ->where('Company_id', $client->company->id)->get();
                    $Shareholders = Team_Member::where('TeamMember', 'Shareholder')
                        ->where('Company_id', $client->company->id)->get();
                    $Investors = Team_Member::where('TeamMember', 'Investor')
                        ->where('Company_id', $client->company->id)->get();

                    // Load all milestones for this company (new unified structure)
                    $milestones = Milestone::where('Company_id', $client->company->id)
                        ->orderBy('created_at')->get();
                    
                    // For backward compatibility, create empty collections
                    $Achieveds = collect();
                    $Planneds = collect();
                    $Archiveds = collect();
                } else {
                    $fonders = 'NA';
                    $Shareholders = 'NA';
                    $Investors = 'NA';
                    $milestones = collect();
                    $Achieveds = 'NA';
                    $Planneds = 'NA';
                    $Archiveds = 'NA';
                }
                $Stages = Stage::all();

                $Currencies = Currency::where('enabled',1)->get(); // جلب جميع الأعضاء
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
            }

            $l = app()->getLocale();
            if ($client->company) {
                $companyRelations = CompanyRelation::where('company_id', $client->company->id)->get();
                $selectedIndustryId   = $companyRelations->pluck('industry_id')->filter()->first();
                $selectedSectorIds    = $companyRelations->pluck('sector_id')->filter()->toArray();
                $selectedTechnologyIds = $companyRelations->pluck('technology_id')->filter()->toArray();
                $technologies = DB::table('technologies')->get()->map(function ($tech) use ($l, $selectedTechnologyIds) {
                    return [
                        'id'       => $tech->id,
                        'name'     => $tech->name,
                        'selected' => in_array($tech->id, $selectedTechnologyIds),
                    ];
                })->values()->toArray();
                $allSectors = DB::table('sectors')->get();
                $industries = StaticCategory::with('sectors')->get();
            $data = $industries->map(function ($industry) use ($l, $allSectors, $selectedIndustryId, $selectedSectorIds) {
                if ($industry->sectors->isNotEmpty()) {
                    $sectorsList = $industry->sectors->map(function ($sector) use ($l, $selectedSectorIds) {
                        return [
                            'id'       => $sector->id,
                            'name'     => $sector->{"name_$l"} ?? $sector->name_en ?? 'N/A',
                            'selected' => in_array($sector->id, $selectedSectorIds),
                        ];
                    })->values()->toArray();
                } else {
                    $sectorsList = $allSectors->map(function ($sector) use ($l, $selectedSectorIds) {
                        return [
                            'id'       => $sector->id,
                            'name'     => $sector->{"name_$l"} ?? $sector->name_en ?? 'N/A',
                            'selected' => in_array($sector->id, $selectedSectorIds),
                        ];
                    })->values()->toArray();
                }
                return [
                    'id'       => $industry->id,
                    'name'     => $industry->name ?? $industry->name_en,
                    'selected' => $industry->id == $selectedIndustryId,
                    'sectors'  => $sectorsList,
                ];
            });
        }else{
           $industries = StaticCategory::with('sectors')->get();
           $technologies = DB::table('technologies')->get();
            $data = [];
        }
        $industries = $industries->map(function($industry) {
            if( empty( $industry->name_ar ) || is_null( $industry->name_ar ) || !trim( $industry->name_ar ) )
            {
                $industry->name_ar = $industry->name_en;
            }
            return $industry;
        });
            return view('admin/clients/company', compact(
                'client',
                '_countries',
                'fonders',
                'Shareholders',
                'Investors',
                'Currencies',
                'milestones',
                'Achieveds',
                'Planneds',
                'Archiveds',
                'teamMembers',
                'states',
                'countries',
                'sectors',
                'funding_rounds',
                'Setting',
                'Stages',
                'technologies',
                'industries',
                'data'
            ));
        } catch (Exception $e) {
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }

     public function deleteImage($id)
     {
        $user = Auth::user();
        $company = Company::where('id', $user->company_id)->first(); 
        $company->logo = null;
        $company->save();
        return response()->json(['status' => 'success']);
     }
    /**
     * Update/Store the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateOrStore(StoreUpdateClientCompanyRequest $request, $id = null)
    {
         try {
            $user = Auth::user();
            if( !is_null( $id ) )
            {
                if( !$user->hasanyRole('Admin|organizationManager') ) { return redirect()->back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']); }
                if( $user->hasRole('organizationManager') )
                {
                    $check = DB::select("SELECT
                            *
                        FROM
                            companies c
                        LEFT JOIN
                            cycles cy ON cy.id = c.cycle
                        LEFT JOIN
                            organizations o ON o.id = cy.organization_id
                        WHERE
                            c.id = $id 
                            AND o.user_id = " . $user->id);

                    $check = collect( $check )->first();
                    if( empty( $check ) ) { return redirect()->back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']); }
                    else
                    {
                        $id = $check->id;
                    }
                }
                if( is_null( $id ) ) { return redirect()->back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']); }
                $companyId = intval( $id );
                $company = Company::find( $companyId );
            }
            else
            {
                $companyId = intval( $user->company_id );
                $company = Company::find( $companyId );
            }
            $data = $request->all();
            if( $companyId )
            {
                $result = Company::find( $companyId );
                if( empty( $result ) )
                {
                    $result = new Company();
                    $msgtype = 'AddComp';
                } else { $msgtype = 'UpdateComp'; }
            }
            else
            {
                $result = new Company();
                $msgtype = 'AddComp';
            }

            if( $data['CompanyN'] == 1 )
            {

                $l = $request->input('locale', app()->getLocale());
                if($l == 'en'){
                $allCategories = [
                            'Food & Beverage',
                            'Technology',
                            'App / Website',
                            'Housewares / Home Design',
                            'Health / Wellness / Nutrition',
                            'Fitness',
                            'Sports',
                            'Beauty',
                            'Clothing / Fashion',
                            'Toys / Games',
                            'Children',
                            'Entertainment / Experiential',
                            'Pets',
                            'Music',
                            'Holiday',
                            'Other',
                        ];
                }else{
                        $allCategories = [
                            'الأطعمة والمشروبات',
                                'التكنولوجيا',
                                'تطبيق / موقع إلكتروني',
                                'الأدوات المنزلية / تصميم المنزل',
                                'الصحة / العافية / التغذية',
                                'اللياقة البدنية',
                                'الرياضة',
                                'الجمال',
                                'الملابس / الأزياء',
                                'الألعاب / الألعاب الإلكترونية',
                                'الأطفال',
                                'الترفيه / التجارب',
                                'الحيوانات الأليفة',
                                'الموسيقى',
                                'العطلات',
                                'أخرى',
                        ];
                }
                $validatedData = $request->validate([
                    'name_en' => 'required|string|max:255',
                    'country_id' => 'required|exists:countries,id',
                    'state_id' => 'required|exists:states,id',
                    'sector' => 'nullable|string|max:255',
                    'address_en' => 'required|string|max:500',
                    'est_date' => 'required|date',
                    'years_of_operations' => 'required|string',
                    'product_or_service' => 'required|string|in:Product,Service,Product + Service',
                    'business_categories' => 'nullable|array',
                    'other_category' => 'nullable|string|max:255',
                    'Commercial_Register' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
                    'Tax_Card' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
                ], [
                    'name_en.required' => __('admin.Name is required'),
                    'country_id.required' => __('admin.Country is required'),
                    'country_id.exists' => __('admin.Invalid Country selected'),
                    'state_id.required' => __('admin.State is required'),
                    'state_id.exists' => __('admin.Invalid State selected'),
                    'sector.required' => __('admin.Sector is required'),
                    'address_en.required' => __('admin.Address is required'),
                    'est_date.required' => __('admin.Established Date must be a valid date'),
                    'years_of_operations.required' => __('admin.Years of Operations is required'),
                    'product_or_service.required' => __('admin.Product or Service is required'),
                    'product_or_service.in' => __('admin.Invalid Product or Service selection'),
                    'Commercial_Register.file'     => __('admin.File_is_not_valid'),
                    'Commercial_Register.mimes'    => __('admin.Invalid_file_type'),
                    'Commercial_Register.max'      => __('admin.File_size_must_be_less_than_10MB'),
                    'Tax_Card.file'     => __('admin.File_is_not_valid'),
                    'Tax_Card.mimes'    => __('admin.Invalid_file_type'),
                    'Tax_Card.max'      => __('admin.File_size_must_be_less_than_10MB'),
                ]);

                // Handle "Other" category logic
                if( in_array('Other', $validatedData['business_categories'] ?? []) )
                {
                    $validatedData['business_categories'] = array_diff($validatedData['business_categories'], ['Other']);
                    if( !empty( $validatedData['other_category'] ?? '') )
                    {
                        $validatedData['business_categories'][] = 'Other';
                        $validatedData['business_categories'][] = $validatedData['other_category'];
                    }
                }

                $validatedData['business_categories'] = json_encode($validatedData['business_categories']);

                if( $data['CreatedTax'] == 0 )
                {
                    $CreatedTax = 0;
                    $cuntryReg = 0;
                    $Commercial_Register = '';
                    $Tax_Card = '';
                }
                else
                {
                    if( $request->hasFile('Commercial_Register') )
                    {
                        $file = $request->file('Commercial_Register');
                        $originalName = $file->getClientOriginalName();
                        $path = $companyId . '/documents/'; // بدون documents
                        
                        $file->storeAs($path, $originalName, [
                            'disk' => 'document'
                        ]);
                        $Commercial_Register = '/documents/' . $path . $originalName;
                    }else { $Commercial_Register = $company->Commercial_Register ?? 'null'; }
                    if( $request->hasFile('Tax_Card') )
                    {
                        $file = $request->file('Tax_Card');
                        $originalName = $file->getClientOriginalName();
                        $path = $companyId . '/documents/';
                        // تخزين الملف بنفس اسمه الأصلي في مجلد public مباشرة
                        $file->storeAs($path, $originalName, [
                            'disk' => 'document'
                        ]);
                        $Tax_Card = '/documents/'. $path . $originalName;
                    } else { $Tax_Card = $company->Tax_Card ?? 'null'; }
                    $CreatedTax = $data['CreatedTax'];
                    $cuntryReg = $data['Country_Register'];
                    // $Commercial_Register = $data['Commercial_Register'];
                    // $Tax_Card = $data['Tax_Card'];
                }
                $result->name_en = $data['name_en'];
                $result->name_ar = $data['name_en'];
                $result->country_id = $data['country_id'];
                $result->state_id = $data['state_id'];
                $result->sector = $data['sector'] ?? null;
                $result->address_en = $data['address_en'];
                $result->address_ar = $data['address_en'];
                $result->est_date = $data['est_date'];
                $result->years_of_operations = $data['years_of_operations'];
                $result->product_or_service = $data['product_or_service'];
                $result->business_categories = $validatedData['business_categories'];
                $result->CreatedTax = $CreatedTax;
                $result->Commercial_Register = $Commercial_Register;
                $result->Tax_Card = $Tax_Card;
                $result->Country_Register_id = $cuntryReg ?? 0;
            }
            if( $data['CompanyN'] == 2 )
            {
                $validatedData = $request->validate([
                    'phone' => 'required|numeric|digits_between:8,15',
                    'WhatsApp' => 'nullable',
                    'email' => 'required|email|max:255',
                    'website' => 'nullable|url', // يجب أن يكون ملف PDF أو PPT وحجمه أقل من 10MB
                    'img_url' => 'nullable', // يجب أن يكون رابط صالح
                    'facebook' => 'nullable|url', // يجب أن يكون إما نعم أو لا
                    'twitter' => 'nullable|url', // نص من 500 حرف كحد أقصى
                    'linkedin' => 'nullable|url', // نص من 500 حرف كحد أقصى
                    'youtube' => 'nullable|url', // نص من 500 حرف كحد أقصى
                    'google_map' => 'nullable|url', // مستند PDF أو PPT وحجمه لا يتجاوز 20MB
                    'instagram' => 'nullable|url', // مستند PDF أو PPT وحجمه لا يتجاوز 20MB
                    'tiktok' => 'nullable|url', // مستند PDF أو PPT وحجمه لا يتجاوز 20MB
                ], [
                    'phone.required' => __('admin.Phone is required'),
                    'phone.numeric' => __('admin.Phone must be a valid number'),
                    'phone.digits_between' => __('admin.Phone must be between 8 and 15 digits'),
                    'email.required' => __('admin.E-mail is required'),
                    'email.email' => __('admin.Invalid E-mail format'),
                    'email.max' => __('admin.E-mail must not exceed 255 characters'),
                    'website.url' => __('admin.Website_Not_URL'),
                    'facebook.url' => __('admin.facebook_Not_URL'),
                    'twitter.url' => __('admin.twitter_Not_URL'),
                    'linkedin.url' => __('admin.linkedin_Not_URL'),
                    'youtube.url' => __('admin.youtube_Not_URL'),
                    'google_map.url' => __('admin.google_map_Not_URL'),
                    'instagram.url' => __('admin.instagram_url_Not_URL'),
                    'tiktok.url' => __('admin.tiktok_url_Not_URL'),
                ]);

                // في الميثود التي تعالج الطلب
                if( $request->hasFile('img_url') )
                {
                    // الحصول على الصورة
                    $image = $request->file('img_url');
                    // حفظ الصورة داخل القرص 'public_uploads'
                    $img_path = $image->store('images/logo', 'public_uploads');

                    // إنشاء رابط الصورة
                    $img_url = Storage::disk('public_uploads')->url($img_path);
                } else { $img_url = $company->logo ?? 'null'; }

                $result->country_code = $data['country_code'];  // مثال لإضافة حقل الهاتف
                $result->phone = $data['phone'];  // مثال لإضافة حقل الهاتف
                $result->country_code_w_a = $data['country_code_w_a'];  // مثال لإضافة حقل الهاتف
                $result->whatsapp = $data['WhatsApp'];  // مثال لإضافة حقل الواتساب
                $result->email = $data['email'];  // مثال لإضافة حقل البريد الإلكتروني
                $result->website = $data['website'];
                $result->logo = $img_url;
                $result->facebook = $data['facebook'];
                $result->twitter = $data['twitter'];
                $result->linkedin = $data['linkedin'];
                $result->youtube = $data['youtube'];
                $result->google_map = $data['google_map'];
                $result->instagram = $data['instagram'];
                $result->tiktok = $data['tiktok'];
            }
            if( $data['CompanyN'] == 3 )
            {
                $validatedData = $request->validate([
                    'company_description' => 'nullable|string|max:500',
                    'Sustainable_development' => 'nullable|array',
                    'about_problem' => 'nullable',
                    'about_market_opportunity' => 'nullable',
                    'about_competitors' => 'nullable',
                    'about_monetize' => 'nullable',
                    'about_challenges' => 'nullable',
                ]);
                if (isset($data['Sustainable_development'])) {
                    $result->Sustainable_development = json_encode($data['Sustainable_development']);
                }else{
                    $result->Sustainable_development = '';
                }

                $result->info_company = $data['company_description'];
                // $result->Sustainable_development = json_encode($data['Sustainable_development']);
                $result->about_problem = $data['about_problem'];
                $result->about_market_opportunity = $data['about_market_opportunity'];
                $result->about_competitors = $data['about_competitors'];
                $result->about_monetize = $data['about_monetize'];
                $result->about_challenges = $data['about_challenges'];
            }
            if( $data['CompanyN'] == 4 )
            {
                $request->validate([
                    'Currency' => 'nullable',
                    'pre_money_valuation' => 'nullable|numeric|min:0',
                    'raised_fund' => 'nullable|numeric|min:0',
                    'Funding_Target' => 'nullable|numeric|min:0',
                    'stage' => 'nullable',
                    'development_stage' => 'nullable',
                    'targeted_market.*' => 'nullable',
                    'business_type' => 'nullable',
                ], [
                    'pre_money_valuation.numeric' => __('admin.Pre-money valuation must be a number'),
                    'pre_money_valuation.min' => __('admin.Pre-money valuation must be a positive number'),
                    'raised_fund.numeric' => __('admin.Raised fund must be a number'),
                    'raised_fund.min' => __('admin.Raised fund must be a positive number'),
                ]);

                $data['Funding_Target'] = $data['Funding_Target'] == null ? 0 : $data['Funding_Target'];
                if( !isset( $data['targeted_market'] ) || $data['targeted_market'] == null ) { $data['targeted_market'] = ['null']; }

                if( $request->hasFile('financial_plan') )
                {
                    $file = $request->file('financial_plan'); // التعامل مع ملف واحد فقط
                    if( $file->isValid() )
                    {
                        $originalName = $file->getClientOriginalName();
                        $folder = match (true) {
                            str_starts_with($file->getMimeType(), 'image/') => 'images',
                            str_starts_with($file->getMimeType(), 'video/') => 'videos',
                            in_array($file->getClientOriginalExtension(), ['pdf', 'doc', 'docx', 'ppt', 'pptx']) => 'documents',
                            default => 'others',
                        };
                        $path = $companyId . '/financial_plan/' . $folder . '/';
                        // تخزين الملف بنفس اسمه الأصلي في مجلد مخصص
                        $file->storeAs($path, $originalName, [
                            'disk' => 'pitch_deck'
                        ]);
                        $uploadedFile = 'financial_plan/' . $path . $originalName;
                    }
                } else { $uploadedFile = $company->financial_plan; }

                $data['financial_plan_description'] = $data['financial_plan_description'] == null ? '0' : $data['financial_plan_description'];

                $result->Currency = $data['Currency'];
                $result->Pre_money_Valuation = $data['pre_money_valuation'];
                $result->raised_fund = $data['raised_fund'];
                $result->Funding_Target = $data['Funding_Target'];
                $result->stage = $data['stage'];
                $result->development_stage = $data['development_stage'];
                $result->targeted_market = json_encode($data['targeted_market']);
                $result->business_type = $data['business_type'];
                $result->financial_plan = $uploadedFile;
                $result->financial_plan_description = $data['financial_plan_description'];
            }
            if( $data['CompanyN'] == 5 )
            {
                $request->validate([
                    'team_num' => 'required|string',
                ], [
                    'team_num.numeric' => __('admin.Raised fund must be a number'),
                ]);

                $result->team_num = $data['team_num'];
                $result->working_team_summary = $data['working_team_summary'];
            }
            if( $data['CompanyN'] == 6 )
            {
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

                $result->pitch_deck_description = $data['pitch_deck_info'];
                $result->pitch_deck_summary = $data['pitch_deck_summary'];
                $result->problem = $data['problem_facing'];
                $result->solution = $data['solution_problem'];
            }
            if( $data['CompanyN'] == 8 )
            {
                $validator = Validator::make($request->all(), [
                    'website' => 'nullable|url', // يجب أن يكون ملف PDF أو PPT وحجمه أقل من 10MB
                    'img_url' => 'nullable', // يجب أن يكون رابط صالح
                    'facebook' => 'nullable|url', // يجب أن يكون إما نعم أو لا
                    'twitter' => 'nullable|url', // نص من 500 حرف كحد أقصى
                    'linkedin' => 'nullable|url', // نص من 500 حرف كحد أقصى
                    'youtube' => 'nullable|url', // نص من 500 حرف كحد أقصى
                    'google_map' => 'nullable|url', // مستند PDF أو PPT وحجمه لا يتجاوز 20MB
                    'instagram' => 'nullable|url', // مستند PDF أو PPT وحجمه لا يتجاوز 20MB
                    'tiktok' => 'nullable|url', // مستند PDF أو PPT وحجمه لا يتجاوز 20MB
                ], [
                    'website.url' => __('admin.Website_Not_URL'),
                    'facebook.url' => __('admin.facebook_Not_URL'),
                    'twitter.url' => __('admin.twitter_Not_URL'),
                    'linkedin.url' => __('admin.linkedin_Not_URL'),
                    'youtube.url' => __('admin.youtube_Not_URL'),
                    'google_map.url' => __('admin.google_map_Not_URL'),
                    'instagram.url' => __('admin.instagram_url_Not_URL'),
                    'tiktok.url' => __('admin.tiktok_url_Not_URL'),
                ]);

                // في الميثود التي تعالج الطلب
                if( $request->hasFile('img_url') )
                {
                    // الحصول على الصورة
                    $image = $request->file('img_url');
                    // حفظ الصورة داخل القرص 'public_uploads'
                    $img_path = $image->store('images/logo', 'public_uploads');
                    // إنشاء رابط الصورة
                    $img_url = Storage::disk('public_uploads')->url($img_path);
                } else { $img_url = $company->logo; }

                $result->website = $data['website'];
                $result->logo = $img_url;
                $result->facebook = $data['facebook'];
                $result->twitter = $data['twitter'];
                $result->linkedin = $data['linkedin'];
                $result->youtube = $data['youtube'];
                $result->google_map = $data['google_map'];
                $result->instagram = $data['instagram'];
                $result->tiktok = $data['tiktok'];
            }
            if( $data['CompanyN'] == 9 )
            {
                $validator = Validator::make($request->all(), [
                    'pitch_deck' => 'nullable|mimes:pdf,ppt,pptx|max:10240', // يجب أن يكون ملف PDF أو PPT وحجمه أقل من 10MB
                ], [
                    'pitch_deck.mimes' => __('admin.Pitch_Deck must be a pdf or ppt file'),
                    'pitch_deck.max' => __('admin.Pitch_Deck max size is 10MB'),
                ]);

                if( $request->hasFile('pitch_deck') )
                {
                    $file = $request->file('pitch_deck');
                    $originalName = $file->getClientOriginalName();
                    $path = $companyId . '/pitch_decks/';
                    // تخزين الملف بنفس اسمه الأصلي في مجلد public مباشرة
                    $file->storeAs($path, $originalName, [
                        'disk' => 'pitch_deck'
                    ]);
                    $pitch_deck = 'pitch_decks/'. $path . $originalName;
                } else { return redirect()->back()->with(['msg' => 'No_Document_Found', 'class' => 'danger'])->withInput(); }

                $result->pitch_deck = $pitch_deck;
            }
            if( $data['CompanyN'] == 10 )
            {
                $validator = Validator::make($request->all(), [
                    'pitch_deck_video' => 'nullable', // يجب أن يكون ملف PDF او PPT وحجمه لا يتجاوز 20MB
                ]);

                if( $request->pitch_deck_video == null ) { return redirect()->back()->with(['msg' => 'No_Video_Found', 'class' => 'danger'])->withInput(); }
                $result->pitch_deck_video = $request->pitch_deck_video;
            }
            if( $data['CompanyN'] == 11 )
            {
                if( $request->hasFile('documents') )
                {
                    $files = $request->file('documents'); 
                    $uploadedFiles = []; 
                    foreach( $files as $file )
                    {
                        if( $file->isValid() )
                        {
                            $originalName = $file->getClientOriginalName();
                            $folder = match (true) {
                                str_starts_with($file->getMimeType(), 'image/') => 'images',
                                str_starts_with($file->getMimeType(), 'video/') => 'videos',
                                in_array($file->getClientOriginalExtension(), ['pdf', 'doc', 'docx', 'ppt', 'pptx']) => 'documents',
                                default => 'others',
                            };
                            $path = $companyId . '/pitch_decks/' . $folder . '/';
                            // تخزين الملف بنفس اسمه الأصلي في مجلد مخصص
                            $file->storeAs($path, $originalName, [
                                'disk' => 'pitch_deck'
                            ]);
                            $uploadedFiles[] = 'pitch_decks/' . $path . $originalName;
                        }
                    }
                } else { return redirect()->back()->with(['msg' => 'No_Document_Found', 'class' => 'danger'])->withInput(); }

                $result->documents = json_encode($uploadedFiles);
            }
            if( $data['CompanyN'] == 12 )
            {
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

                $result->about_problem = $data['about_problem'];
                $result->about_market_opportunity = $data['about_market_opportunity'];
                $result->about_competitors = $data['about_competitors'];
                $result->about_monetize = $data['about_monetize'];
                $result->about_challenges = $data['about_challenges'];
            }
            if( $data['CompanyN'] == 13 )
            {
                $request->validate([
                    'business_model' => 'required',
                    'business_model_description' => 'required|string|max:255',
                    'business_plan' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:20480', // max limit to be defined later from admin panel settings
                ], [
                    'business_model.required' => __('admin.business_model is required'),
                    'business_model_description.required' => __('admin.business_model_description is required'),
                ]);

                if( $request->hasFile('business_plan') )
                {
                    $file = $request->file('business_plan'); // التعامل مع ملف واحد فقط
                    if( $file->isValid() )
                    {
                        $originalName = $file->getClientOriginalName();
                        $folder = match (true) {
                            str_starts_with($file->getMimeType(), 'image/') => 'images',
                            str_starts_with($file->getMimeType(), 'video/') => 'videos',
                            in_array($file->getClientOriginalExtension(), ['pdf', 'doc', 'docx', 'ppt', 'pptx']) => 'documents',
                            default => 'others',
                        };
                        $path = $companyId . '/business_plan/' . $folder . '/';
                        // تخزين الملف بنفس اسمه الأصلي في مجلد مخصص
                        $file->storeAs($path, $originalName, [
                            'disk' => 'pitch_deck'
                        ]);
                        $uploadedFile = 'business_plan/' . $path . $originalName;
                    }
                } else { return redirect()->back()->with(['msg' => 'Business Plan file not selected', 'class' => 'danger'])->withInput(); }

                $result->business_model = $data['business_model'];
                $result->other_business_model = $data['other_business_model'];
                $result->business_model_description = $data['business_model_description'];
                $result->business_plan = $uploadedFile;
            }
            $result->save();
            if( $data['CompanyN'] == 14 )
            {               
                // Set up debug logging using Laravel's Log facade
                Log::debug('Starting company update process', [
                    'company_id' => $id,
                    'user_id' => $user->id,
                    'request_data' => [
                        'industry_id' => $request->industry_id,
                        'sector_ids' => $request->sector_ids,
                        'tech_tags' => $request->tech_tags,
                        'CompanyN' => $request->CompanyN,
                        'other_sector' => $request->other_sector,
                        'other_tech' => $request->other_tech
                    ]
                ]);

                    $rules = [
                        'industry_id' => 'required|exists:static_categories,id',
                        'sector_ids' => 'required',
                        'tech_tags' => 'nullable|array',
                        'tech_tags.*' => 'nullable|string|max:50',
                        'other_sector' => 'nullable|regex:/^[A-Za-z0-9\s\-\.]+$/|max:255',
                        'other_tech' => 'nullable|regex:/^[A-Za-z0-9\s\-\.]+$/|max:50',
                    ];

                    $messages = [
                        'industry_id.required' => __('admin.industry is required'),
                        'industry_id.exists' => __('admin.invalid industry selected'),
                        'sector_ids.required' => __('admin.sectors is required'),
                        'tech_tags.array' => __('admin.technologies must be an array'),
                        'tech_tags.*.string' => __('admin.technology name must be text'),
                        'tech_tags.*.max' => __('admin.technology name too long'), 
                        'other_sector.regex' => __('admin.other sector contains invalid characters'),
                        'other_tech.regex' => __('admin.other technology contains invalid characters'),
                        'other_sector.max' => __('admin.other sector name too long'),
                        'other_tech.max' => __('admin.other technology name too long'),
                    ];

                    $validator = \Validator::make($request->all(), $rules, $messages);

                // Normalize incoming sector and technology inputs
                // Process sector IDs
                $sectorIds = [];
                if (!empty($data['sector_ids'])) {
                    Log::debug('Processing sector IDs', ['raw' => $data['sector_ids']]);
                    
                    try {
                        if (is_string($data['sector_ids'])) {
                            $decoded = json_decode($data['sector_ids'], true);
                            if (is_array($decoded)) {
                                $sectorIds = array_filter($decoded);
                            }
                        } elseif (is_array($data['sector_ids'])) {
                            $sectorIds = array_filter($data['sector_ids']);
                        }

                        // Validate sectors exist
                        $validSectorIds = Sector::whereIn('id', $sectorIds)->pluck('id')->toArray();
                        $invalidSectorIds = array_diff($sectorIds, $validSectorIds);
                        
                        if (!empty($invalidSectorIds)) {
                            Log::warning('Invalid sector IDs provided', ['invalid_ids' => $invalidSectorIds]);
                            throw new \Exception(__('admin.invalid_sectors_selected'));
                        }
                        
                        $sectorIds = $validSectorIds;
                        
                    } catch (\Exception $e) {
                        Log::error('Error processing sector IDs', [
                            'error' => $e->getMessage(),
                            'raw_data' => $data['sector_ids']
                        ]);
                        return redirect()->back()->with([
                            'msg' => __('admin.error_processing_sectors'), 
                            'class' => 'danger'
                        ])->withInput();
                    }
                }

                // Process existing tech IDs
                $techIds = [];
                if (!empty($data['tech_ids'])) {
                    if (is_string($data['tech_ids'])) {
                        try {
                            $rawTechIds = json_decode($data['tech_ids'], true);
                            if (is_array($rawTechIds)) {
                                $validTechIds = Technology::whereIn('id', $rawTechIds)->pluck('id')->toArray();
                                $techIds = array_values(array_unique($validTechIds));
                            }
                        } catch (\Exception $e) {
                            Log::error('Error processing tech IDs', ['error' => $e->getMessage()]);
                        }
                    } elseif (is_array($data['tech_ids'])) {
                        $validTechIds = Technology::whereIn('id', $data['tech_ids'])->pluck('id')->toArray();
                        $techIds = array_values(array_unique($validTechIds));
                    }
                }

                // Process new tech tags
                $techTags = [];
                if (!empty($request->tech_tags)) {
                    if (is_array($request->tech_tags)) {
                        $techTags = array_filter(array_map('trim', $request->tech_tags));
                    } elseif (is_string($request->tech_tags)) {
                        try {
                            $decoded = json_decode($request->tech_tags, true);
                            if (is_array($decoded)) {
                                $techTags = array_filter(array_map('trim', $decoded));
                            }
                        } catch (\Exception $e) {
                            Log::error('Error processing tech tags', ['error' => $e->getMessage()]);
                        }
                    }
                }

                // Require at least one technology selection
                if (empty($techIds) && empty($techTags) && empty($request->other_tech)) {
                    return redirect()->back()->with([
                        'msg' => __('admin.please_select_technology'),
                        'class' => 'danger'
                    ])->withInput();
                }
                
                // Validate that we have valid arrays after decoding
                if (!is_array($sectorIds)) {
                    $sectorIds = [];
                }
                if (!is_array($techIds)) {
                    $techIds = [];
                }
                
                // Process "Other" sector if provided
                if (!empty(trim($data['other_sector'] ?? ''))) {
                    try {
                        $sectorName = trim($data['other_sector']);
                        
                        // Check if sector with similar name already exists (case-insensitive)
                        $existingSector = Sector::whereRaw('LOWER(name_en) = ?', [strtolower($sectorName)])
                            ->orWhereRaw('LOWER(name_ar) = ?', [strtolower($sectorName)])
                            ->first();
                            
                        if ($existingSector) {
                            $sectorIds[] = $existingSector->id;
                            Log::info('Using existing sector', [
                                'name' => $sectorName,
                                'id' => $existingSector->id
                            ]);
                        } else {
                            // Create new sector
                            $addSector = new Sector();
                            $addSector->name_en = $sectorName;
                            $addSector->name_ar = $sectorName; // Consider translation if needed
                            $addSector->save();
                            
                            // Associate with industry
                            $industry = StaticCategory::findOrFail($data['industry_id']);
                            $industry->sectors()->attach($addSector->id);
                            
                            $sectorIds[] = $addSector->id;
                            
                            Log::info('Created new sector', [
                                'name' => $sectorName,
                                'id' => $addSector->id
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing other sector', [
                            'name' => $sectorName ?? null,
                            'error' => $e->getMessage()
                        ]);
                        return redirect()->back()->with([
                            'msg' => __('admin.error_creating_sector'),
                            'class' => 'danger'
                        ])->withInput();
                    }
                }
                
                // Process "Other" technology if provided
                if (!empty(trim($data['other_tech'] ?? ''))) {
                    try {
                        $techName = trim($data['other_tech']);
                        
                        // Check if technology with similar name exists
                        $existingTech = Technology::whereRaw('LOWER(name) = ?', [strtolower($techName)])
                            ->first();
                            
                        if ($existingTech) {
                            $techIds[] = $existingTech->id;
                            Log::info('Using existing technology', [
                                'name' => $techName,
                                'id' => $existingTech->id
                            ]);
                        } else {
                            // Create new technology
                            $addTech = new Technology();
                            $addTech->name = $techName;
                            $addTech->save();
                            
                            $techIds[] = $addTech->id;
                            
                            Log::info('Created new technology', [
                                'name' => $techName,
                                'id' => $addTech->id
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing other technology', [
                            'name' => $techName ?? null,
                            'error' => $e->getMessage()
                        ]);
                        return redirect()->back()->with([
                            'msg' => __('admin.error_creating_technology'),
                            'class' => 'danger'
                        ])->withInput();
                    }
                }

                // Process freeform tech tags (create Technology rows if needed)
                if (!empty($techTags)) {
                    foreach ($techTags as $tag) {
                        // enforce max length
                        if (mb_strlen($tag) > 50) {
                            return redirect()->back()->with(['msg' => __('admin.Technology_name_too_long'), 'class' => 'danger'])->withInput();
                        }
                        // try find existing technology by name (case-insensitive)
                        $existing = Technology::whereRaw('LOWER(name) = ?', [mb_strtolower($tag)])->first();
                        if ($existing) {
                            $techIds[] = $existing->id;
                        } else {
                            $newTech = new Technology();
                            $newTech->name = $tag;
                            $newTech->save();
                            $techIds[] = $newTech->id;
                        }
                    }
                }

                DB::beginTransaction();
                try {
                    // Remove existing relations
                    CompanyRelation::where('company_id', $result->id)
                        ->where('user_id', $user->id)
                        ->delete();

                    $timestamp = now();
                    $relations = [];

                    // Add sector relations
                    foreach ($sectorIds as $sectorId) {
                        $relations[] = [
                            'company_id' => $result->id,
                            'user_id' => $user->id,
                            'industry_id' => $data['industry_id'],
                            'sector_id' => $sectorId,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp
                        ];
                    }

                    // Add technology relations
                    foreach ($techIds as $techId) {
                        $relations[] = [
                            'company_id' => $result->id,
                            'user_id' => $user->id,
                            'industry_id' => $data['industry_id'],
                            'technology_id' => $techId,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp
                        ];
                    }

                    // Bulk insert all relations
                    if (!empty($relations)) {
                        CompanyRelation::insert($relations);
                    }

                    DB::commit();
                    Log::info('Company relations updated', [
                        'company_id' => $result->id,
                        'sector_count' => count($sectorIds),
                        'tech_count' => count($techIds)
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to update company relations', [
                        'error' => $e->getMessage(),
                        'company_id' => $result->id
                    ]);
                    
                    return redirect()->back()
                        ->with('msg', __('admin.error_updating_company_relations'))
                        ->with('class', 'danger')
                        ->withInput();
                }   
            }
            if( is_null( $id ) )
            {
                User::where('id', $user->id)->update(['company_id' => $result->id]);
                $msgtype = 'NewComp';
            }

            if( $result ) {
                if( !in_array(env('APP_ENV', 'local'), ['local']) ) { event(new WebMessage($msgtype, $user->first_name_en)); } 

                // Use the result's ID if $id is null (new company creation)
                $redirectId = $id ?: $result->id;
                return redirect()->route('company.profile_view_mine', $redirectId)->with(['msg' => 'Startup_Info_Updated_Successfully', 'class' => 'success']);
            } else { return back()->withInput()->with(['msg' => 'Startup_Info_Updated_Failed', 'class' => 'danger']); }
        } catch (ValidationException $e) {
            // عند وجود خطأ في التحقق، إرجاع رسائل الأخطاء للمستخدم
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            // dd($e);
            // تسجيل الأخطاء الأخرى
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            return redirect()->back()->with(['msg' => 'please_complete_startup_info', 'class' => 'danger']);
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
            // dd($e);
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }

    public function AddTeams(Request $request, $id = null)
    {
        if( isset( $id ) && !is_null( $id ) ) { $user = User::where('company_id', $id)->first(); }
        else
        {
            $user = auth()->user();
        }
        // $user = Auth::user();
        $companyId = $user->company_id;
        if ($request->TeamsNo == "Founders")
        {
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
            if ($validator->fails())
            {
                if( $request->ajax() )
                {
                    return response()->json([
                        'errors' => $validator->errors(),
                    ], 422); // حالة الخطأ 422
                }
                else
                {
                    return redirect()->back()->with('error', 'inputs error')->withErrors($validator)->withInput();
                }
            }

            $email = $request->input('founder_email');
            $mail = Team_member::where('Email', $email)->where('Company_id', $companyId)->where('TeamMember', 'Founder')->first();

            if( $mail != null )
            {
                if( $request->ajax() ) { return response()->json(['state' => __('admin.founder_email_exist'),]); }
                else { return redirect()->back()->with('error', __('admin.founder_email_exist')); }
            }
            // $linkedin = $request->input('founder_linkedin');
            // $linked = Team_member::where('LinkedIn', $linkedin)->where('Company_id', $companyId)->where('TeamMember', 'Founder')->first();
            // if ($linked != null) 
            // {
            //     if( $request->ajax() ) { return response()->json(data: ['state' => __('admin.founder_linkedin_exist'),]); }
            //     else { return redirect()->back()->with('error', __('admin.founder_linkedin_exist')); }
            // }

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
            if( $request->ajax() ) { return response()->json(['state' => __('admin.founder_added'),]); }
            else { return redirect()->back()->with('success', __('admin.founder_added')); }
        }
        if ($request->TeamsNo == "Shareholders")
        {
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
            $totalPercentage = Team_member::where('Company_id', $companyId)->sum('PresentagShereholder');

            $newPercentage = $request->shareholder_ownership_percentage;

            // تحقق من أن النسبة المئوية الجديدة لا تتجاوز 100%
            if ($totalPercentage + $newPercentage > 100)
            {
                if( $request->ajax() )
                {
                    return response()->json([
                        'errors' => [
                            'shareholder_ownership_percentage' => [__('admin.Total ownership percentage cannot exceed 100%')],
                        ],
                    ], 422); // حالة الخطأ 422
                } else { return redirect()->back()->with('error', 'inputs error')->withErrors(__('admin.Total ownership percentage cannot exceed 100%'))->withInput(); } 
            }
            // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
            if ($validator->fails())
            {
                if( $request->ajax() )
                {
                    return response()->json([
                        'errors' => $validator->errors(),
                    ], 422); // حالة الخطأ 422
                } else { return redirect()->back()->with('error', 'inputs error')->withErrors($validator)->withInput(); }
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
            if( $request->ajax() ) { return response()->json(['state' => __('admin.Investor_added'),]); }
            else { return redirect()->back()->with('success', __('admin.founder_added')); }
        }

        if ($request->TeamsNo == "Investors")
        {
            // تحقق من صحة البيانات
            $validator = Validator::make($request->all(), [
                'investor_name' => 'required|string|max:255',
                'investment_type' => 'required|max:255',
            ], [
                'investor_name.required' => __('admin.Name is Required Investor'),
                'investment_type.required' => __('admin.Type is Required Investor'),
            ]);

            if ($validator->fails())
            {
                if( $request->ajax() )
                {
                    return response()->json([
                        'errors' => $validator->errors(),
                    ], 422); // حالة الخطأ 422
                } else { return redirect()->back()->with('error', 'inputs error')->withErrors($validator)->withInput(); }
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
            if( $request->ajax() ) { return response()->json(['state' => __('admin.Investor_added'),]); }
            else { return response()->json(['state' => __('admin.Investor_added'),]);  }
        }
    }

    public function EditTeams(Request $request, $id = null)
    {
        if( isset( $id ) && !is_null( $id ) ) { $user = User::where('company_id', $id)->first(); }
        else
        {
            $user = auth()->user();
        }
        // $user = Auth::user();
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


    public function DeleteTeams(Request $request, $id = null)
    {
        $user = User::where('company_id', $id)->first();
        // $user = Auth::user();
        $companyId = $user->company_id;

        // إنشاء وحفظ بيانات العضو في قاعدة البيانات
        $teamMember = Team_member::where('id', $request->id)->first(); // افترض أن لديك موديل اسمه TeamMember

        $teamMember->delete();

        // استجابة بعد الحفظ
        return redirect()->route('company.profile_view_mine', $companyId)->with(['msg' => 'DeletedSuccessfully', 'class' => 'success']);
    }



    public function AddMileStone(Request $request, $id = null)
    {
        try {
            // Get company from authenticated user or provided ID
            if ($id) {
                $user = User::where('company_id', $id)->first();
                $companyId = $user ? $user->company_id : $id;
                
                // Verify that current user has access to this company (allow admins)
                if (Auth::user()->company_id !== $companyId && !Auth::user()->hasanyRole('Admin|organizationManager')) {
                    return response()->json([
                        'errors' => ['permission' => [__('You do not have permission to access this company.')]],
                    ], 403);
                }
            } else {
                $companyId = Auth::user()->company_id;
            }

            if (!$companyId) {
                return response()->json([
                    'errors' => ['company' => [__('Startup not found')]],
                ], 422);
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:300',
                'description' => 'required|string|max:1000',
                'completion_percentage' => 'required|integer|min:0|max:100',
                'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx,xlsx|max:10240', // 10MB
            ], [
                'name.required' => __('startup.milestone_title_required'),
                'name.max' => __('startup.milestone_title_max_length'),
                'description.required' => __('startup.milestone_description_required'),
                'description.max' => __('startup.milestone_description_max_length'),
                'completion_percentage.required' => __('startup.completion_percentage_required'),
                'completion_percentage.integer' => __('startup.completion_percentage_numeric'),
                'completion_percentage.min' => __('startup.completion_percentage_range'),
                'completion_percentage.max' => __('startup.completion_percentage_range'),
                'documents.*.file' => __('startup.document_invalid_file'),
                'documents.*.mimes' => __('startup.milestone_document_invalid_type'),
                'documents.*.max' => __('startup.document_too_large'),
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Create new milestone
            $milestone = new Milestone();
            $milestone->name = $request->name;
            $milestone->description = $request->description;
            $milestone->completion_percentage = $request->completion_percentage;
            $milestone->Company_id = $companyId;
            $milestone->save();

            // Handle file uploads if any
            $documents = [];
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $index => $file) {
                    $fileName = time() . '_' . $index . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('milestones/' . $companyId, $fileName, 'public');
                    
                    $documents[] = [
                        'name' => $file->getClientOriginalName(),
                        'url' => asset('storage/' . $filePath),
                        'path' => $filePath,
                    ];
                }
                
                // Store documents info in milestone (you might want to create a separate table for this)
                $milestone->documents = json_encode($documents);
                $milestone->save();
            }

            return response()->json([
                'state' => __('admin.MileStone_added'),
                'success' => true,
                'milestone' => [
                    'id' => $milestone->id,
                    'name' => $milestone->name,
                    'description' => $milestone->description,
                    'completion_percentage' => $milestone->completion_percentage,
                    'documents' => $documents,
                ],
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error adding milestone: ' . $e->getMessage());
            return response()->json([
                'errors' => ['general' => [__('An error occurred while saving the milestone.')]],
            ], 500);
        }
    }

    public function EditMileStone(Request $request, $id = null)
    {
        try {
            // Get company from authenticated user or provided ID
            if ($id) {
                $user = User::where('company_id', $id)->first();
                $companyId = $user ? $user->company_id : $id;
                
                // Verify that current user has access to this company (allow admins)
                if (Auth::user()->company_id !== $companyId && !Auth::user()->hasanyRole('Admin|organizationManager')) {
                    return response()->json([
                        'errors' => ['permission' => [__('You do not have permission to access this company.')]],
                    ], 403);
                }
            } else {
                $companyId = Auth::user()->company_id;
            }

            if (!$companyId) {
                return response()->json([
                    'errors' => ['company' => [__('Startup not found')]],
                ], 422);
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:milestones,id',
                'name' => 'required|string|max:300',
                'description' => 'required|string|max:1000',
                'completion_percentage' => 'required|integer|min:0|max:100',
                'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx,xlsx|max:10240', // 10MB
                'remove_documents' => 'nullable|string', // JSON string of indices to remove
            ], [
                'id.required' => __('startup.milestone_id_required'),
                'id.exists' => __('startup.milestone_not_found'),
                'name.required' => __('startup.milestone_title_required'),
                'name.max' => __('startup.milestone_title_max_length'),
                'description.required' => __('startup.milestone_description_required'),
                'description.max' => __('startup.milestone_description_max_length'),
                'completion_percentage.required' => __('startup.completion_percentage_required'),
                'completion_percentage.integer' => __('startup.completion_percentage_numeric'),
                'completion_percentage.min' => __('startup.completion_percentage_range'),
                'completion_percentage.max' => __('startup.completion_percentage_range'),
                'documents.*.file' => __('startup.document_invalid_file'),
                'documents.*.mimes' => __('startup.milestone_document_invalid_type'),
                'documents.*.max' => __('startup.document_too_large'),
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Find the milestone and ensure it belongs to the company
            $milestone = Milestone::where('id', $request->id)
                                ->where('Company_id', $companyId)
                                ->first();

            if (!$milestone) {
                return response()->json([
                    'errors' => ['milestone' => [__('Milestone not found or you do not have permission to edit it.')]],
                ], 404);
            }

            // Update milestone
            $milestone->name = $request->name;
            $milestone->description = $request->description;
            $milestone->completion_percentage = $request->completion_percentage;
            $milestone->save();

            // Handle file uploads if any (append to existing documents)
            // Force model to refresh and apply casts
            $milestone->refresh();
            $existingDocuments = $milestone->documents;
            
            \Log::info('EditMileStone: Initial request info', [
                'milestone_id' => $milestone->id,
                'has_files' => $request->hasFile('documents'),
                'request_files' => $request->files->keys(),
                'all_input' => array_keys($request->all()),
                'files_in_documents' => $request->hasFile('documents') ? count($request->file('documents')) : 0
            ]);
            
            \Log::info('EditMileStone: Processing existing documents', [
                'milestone_id' => $milestone->id,
                'documents_type' => gettype($existingDocuments),
                'documents_value' => $existingDocuments,
                'has_files' => $request->hasFile('documents')
            ]);
            
            // Ensure existing documents is an array (handle JSON string or null)
            if (is_string($existingDocuments)) {
                try {
                    $decoded = json_decode($existingDocuments, true);
                    $existingDocuments = is_array($decoded) ? $decoded : [];
                } catch (\Exception $e) {
                    \Log::warning('Failed to decode milestone documents JSON', [
                        'milestone_id' => $milestone->id,
                        'documents_raw' => $existingDocuments,
                        'error' => $e->getMessage()
                    ]);
                    $existingDocuments = [];
                }
            } elseif (!is_array($existingDocuments)) {
                $existingDocuments = [];
            }
            
            // Handle document removals
            if ($request->has('remove_documents')) {
                $removeDocuments = json_decode($request->input('remove_documents'), true);
                if (is_array($removeDocuments)) {
                    \Log::info('EditMileStone: Removing documents', [
                        'remove_indices' => $removeDocuments,
                        'milestone_id' => $milestone->id
                    ]);
                    
                    // Remove documents from file system and filter from existing documents
                    foreach ($removeDocuments as $index) {
                        if (isset($existingDocuments[$index])) {
                            $document = $existingDocuments[$index];
                            
                            // Delete physical file if it exists
                            if (isset($document['path']) && \Storage::disk('public')->exists($document['path'])) {
                                \Storage::disk('public')->delete($document['path']);
                                \Log::info('EditMileStone: Physical file deleted', [
                                    'path' => $document['path'],
                                    'milestone_id' => $milestone->id
                                ]);
                            }
                        }
                    }
                    
                    // Remove documents from array by filtering out the specified indices
                    $existingDocuments = array_values(array_filter($existingDocuments, function($doc, $index) use ($removeDocuments) {
                        return !in_array($index, $removeDocuments);
                    }, ARRAY_FILTER_USE_BOTH));
                }
            }
            
            $newDocuments = [];

            if ($request->hasFile('documents')) {
                \Log::info('EditMileStone: Files detected', [
                    'files_count' => count($request->file('documents')),
                    'milestone_id' => $milestone->id
                ]);
                
                foreach ($request->file('documents') as $index => $file) {
                    $fileName = time() . '_' . $index . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('milestones/' . $companyId, $fileName, 'public');
                    
                    \Log::info('EditMileStone: File uploaded', [
                        'original_name' => $file->getClientOriginalName(),
                        'stored_path' => $filePath,
                        'index' => $index
                    ]);
                    
                    $newDocuments[] = [
                        'name' => $file->getClientOriginalName(),
                        'url' => asset('storage/' . $filePath),
                        'path' => $filePath,
                    ];
                }
                
                // Merge existing and new documents
                $allDocuments = array_merge($existingDocuments, $newDocuments);
                $milestone->documents = json_encode($allDocuments);
                $milestone->save();
                
                \Log::info('EditMileStone: Documents saved', [
                    'existing_count' => count($existingDocuments),
                    'new_count' => count($newDocuments),
                    'total_count' => count($allDocuments),
                    'milestone_id' => $milestone->id
                ]);
                
                // Return the merged documents directly
                $finalDocuments = $allDocuments;
            } else {
                // No new files uploaded, use existing documents
                $finalDocuments = $existingDocuments;
            }

            \Log::info('EditMileStone: Final response', [
                'milestone_id' => $milestone->id,
                'final_documents_count' => count($finalDocuments),
                'final_documents' => $finalDocuments
            ]);
            
            return response()->json([
                'state' => __('admin.MileStone_Edited'),
                'success' => true,
                'milestone' => [
                    'id' => $milestone->id,
                    'name' => $milestone->name,
                    'description' => $milestone->description,
                    'completion_percentage' => $milestone->completion_percentage,
                    'documents' => $finalDocuments,
                ],
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error editing milestone: ' . $e->getMessage());
            return response()->json([
                'errors' => ['general' => [__('An error occurred while updating the milestone.')]],
            ], 500);
        }
    }
    public function DeleteMileStone(Request $request, $id = null)
    {
        try {
            // Get company from authenticated user or provided ID
            if ($id) {
                $user = User::where('company_id', $id)->first();
                $companyId = $user ? $user->company_id : $id;
                
                // Verify that current user has access to this company (allow admins)
                if (Auth::user()->company_id !== $companyId && !Auth::user()->hasanyRole('Admin|organizationManager')) {
                    return response()->json([
                        'errors' => ['permission' => [__('You do not have permission to access this company.')]],
                    ], 403);
                }
            } else {
                $companyId = Auth::user()->company_id;
            }

            if (!$companyId) {
                return response()->json([
                    'errors' => ['company' => [__('admin.Company not found')]],
                ], 422);
            }

            // Validate the request
            $validator = Validator::make($request->all(), [
                'id' => 'required|integer|exists:milestones,id',
            ], [
                'id.required' => __('Milestone ID is required.'),
                'id.exists' => __('Milestone not found.'),
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Find the milestone and ensure it belongs to the company
            $milestone = Milestone::where('id', $request->id)
                                ->where('Company_id', $companyId)
                                ->first();

            if (!$milestone) {
                return response()->json([
                    'errors' => ['milestone' => [__('Milestone not found or you do not have permission to delete it.')]],
                ], 404);
            }

            // Delete associated files if they exist
            if ($milestone->documents && is_array($milestone->documents)) {
                $documents = $milestone->documents;
                foreach ($documents as $document) {
                    if (is_array($document) && isset($document['path']) && \Storage::disk('public')->exists($document['path'])) {
                        \Storage::disk('public')->delete($document['path']);
                    }
                }
            }

            // Delete the milestone
            $milestone->delete();

            return response()->json([
                'state' => __('admin.MileStone_deleted'),
                'success' => true,
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error deleting milestone: ' . $e->getMessage(), [
                'milestone_id' => $request->id ?? 'unknown',
                'company_id' => $companyId ?? 'unknown',
                'user_id' => Auth::id(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'errors' => ['general' => [__('An error occurred while deleting the milestone.')]],
            ], 500);
        }
    }


    public function deleteDocument(Request $request)
{
    $index = $request->input('index');
    $file = $request->input('file');
    
    // جلب الشركة الخاصة بالمستخدم
    $company = $request->user()->company;  // تأكد من استخدام العلاقة الصحيحة
    $documents = json_decode($company->documents, true);

    // التأكد من وجود الملف على الفهرس المحدد
    if (isset($documents[$index])) {
        $document = $documents[$index];

        // حذف الملف من قاعدة البيانات
        unset($documents[$index]);
        $company->documents = json_encode($documents);
        $company->save();

        // حذف الملف من التخزين
        Storage::disk('pitch_deck')->delete($document);
        
        return response()->json(['success' => true]);
    }

    return response()->json(['success' => false, 'message' => 'الملف غير موجود.']);
}

    public function companies(Request $request)
    {
        // Map the form parameters to the expected filter parameters
        $filters = [];
        
        // Handle search
        if ($request->filled('search')) {
            $filters['search'] = $request->get('search');
        }
        
        // Handle organization filter (Admin only)
        if ($request->filled('orgnization')) {
            $filters['organization'] = $request->get('orgnization');
        }
        
        // Handle program/cycle filter
        if ($request->filled('programFilter')) {
            $programFilter = $request->get('programFilter');
            if ($programFilter !== 'all') {
                $filters['programId'] = $programFilter;
            }
        }
        
        // Handle stage filter
        if ($request->filled('stage')) {
            $filters['stage'] = $request->get('stage');
        }
        
        // Handle sectors filter (multiple sectors)
        if ($request->filled('sectors')) {
            $sectors = $request->get('sectors');
            if (is_array($sectors) && !empty($sectors)) {
                // For multiple sectors, we'll need to modify the repository to handle this
                $filters['sectors'] = $sectors;
            }
        }
        
        $companies = $this->companyService->getCompanies($filters, isApi: false);
        
        // Pass the language variable
        $l = app()->getLocale();

        return view('admin/users/companies', compact('companies', 'l'));
    }
    
    public function addEmployee(Request $request, $id)
    {
        // try {
        //     // Validate company access
        //     $user = Auth::user();
        //     $company = Company::findOrFail($id);
            
        //     // Check permissions
        //     if ($user->company_id !== $company->id && !$user->hasAnyRole('Admin|organizationManager')) {
        //         return response()->json([
        //             'errors' => ['permission' => ['You do not have permission to add employees to this company.']]
        //         ], 403);
        //     }

        //     $validator = Validator::make($request->all(), [
        //         'full_name' => 'required|string|max:255',
        //         'job_title' => 'required|string|max:255',
        //         'gender' => 'required|in:male,female',
        //         'employment_type' => 'required|in:full-time,part-time,contractor,intern',
        //         'hiring_data' => 'required|date',
        //         'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx|max:10240'
        //     ], [
        //         'full_name.required' => 'Employee name is required',
        //         'job_title.required' => 'Job title is required',
        //         'gender.required' => 'Gender is required',
        //         'employment_type.required' => 'Employment type is required',
        //         'hiring_data.required' => 'Hiring date is required',
        //         'documents.*.mimes' => 'Documents must be PDF, JPG, PNG, or DOCX files',
        //         'documents.*.max' => 'Each document must be less than 10MB'
        //     ]);

        //     if ($validator->fails()) {
        //         return response()->json([
        //             'errors' => $validator->errors()
        //         ], 422);
        //     }

        //     $employee = new Team_member();
        //     $employee->Company_id = $id;
        //     $employee->full_name = $request->full_name;
        //     $employee->job_title = $request->job_title;
        //     $employee->gender = $request->gender;
        //     $employee->employment_type = $request->employment_type;
        //     $employee->hiring_data = $request->hiring_data;
        //     $employee->TeamMember = 'Employee';

        //     // Handle document uploads
        //     if ($request->hasFile('documents')) {
        //         $documents = [];
        //         foreach ($request->file('documents') as $index => $file) {
        //             $fileName = time() . '_' . $index . '_' . $file->getClientOriginalName();
        //             $path = $file->storeAs('employees/' . $id . '/documents', $fileName, 'public');
                    
        //             $documents[] = [
        //                 'name' => $file->getClientOriginalName(),
        //                 'url' => Storage::disk('public')->url($path),
        //                 'path' => $path
        //             ];
        //         }
        //         $employee->documents = $documents;
        //     }

        //     $employee->save();

        //     return response()->json([
        //         'success' => true,
        //         'employee' => $employee,
        //         'state' => 'Employee added successfully'
        //     ]);
            
        // } catch (Exception $e) {
        //     Log::error('Error adding employee: ' . $e->getMessage());
        //     return response()->json([
        //         'errors' => ['general' => ['An error occurred while adding the employee.']]
        //     ], 500);
        // }
    }

    public function editEmployee(Request $request, $id)
    {
        // try {
        //     // Validate company access
        //     $user = Auth::user();
        //     $company = Company::findOrFail($id);
            
        //     // Check permissions
        //     if ($user->company_id !== $company->id && !$user->hasAnyRole('Admin|organizationManager')) {
        //         return response()->json([
        //             'errors' => ['permission' => ['You do not have permission to edit employees in this company.']]
        //         ], 403);
        //     }

        //     $validator = Validator::make($request->all(), [
        //         'id' => 'required|exists:team_members,id',
        //         'full_name' => 'required|string|max:255',
        //         'job_title' => 'required|string|max:255',
        //         'gender' => 'required|in:male,female',
        //         'employment_type' => 'required|in:full-time,part-time,contractor,intern',
        //         'hiring_data' => 'required|date',
        //         'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx|max:10240',
        //         'remove_documents' => 'nullable|string'
        //     ], [
        //         'id.required' => 'Employee ID is required',
        //         'id.exists' => 'Employee not found',
        //         'full_name.required' => 'Employee name is required',
        //         'job_title.required' => 'Job title is required',
        //         'gender.required' => 'Gender is required',
        //         'employment_type.required' => 'Employment type is required',
        //         'hiring_data.required' => 'Hiring date is required',
        //         'documents.*.mimes' => 'Documents must be PDF, JPG, PNG, or DOCX files',
        //         'documents.*.max' => 'Each document must be less than 10MB'
        //     ]);

        //     if ($validator->fails()) {
        //         return response()->json([
        //             'errors' => $validator->errors()
        //         ], 422);
        //     }

        //     $employee = Team_member::where('id', $request->id)
        //                          ->where('Company_id', $id)
        //                          ->where('TeamMember', 'Employee')
        //                          ->firstOrFail();

        //     $employee->full_name = $request->full_name;
        //     $employee->job_title = $request->job_title;
        //     $employee->gender = $request->gender;
        //     $employee->employment_type = $request->employment_type;
        //     $employee->hiring_data = $request->hiring_data;

        //     // Handle existing documents
        //     $existingDocs = $employee->documents ?: [];
            
        //     // Handle document removals
        //     if ($request->has('remove_documents')) {
        //         $removeDocuments = json_decode($request->input('remove_documents'), true);
        //         if (is_array($removeDocuments)) {
        //             foreach ($removeDocuments as $index) {
        //                 if (isset($existingDocs[$index])) {
        //                     $document = $existingDocs[$index];
        //                     // Delete physical file
        //                     if (isset($document['path']) && Storage::disk('public')->exists($document['path'])) {
        //                         Storage::disk('public')->delete($document['path']);
        //                     }
        //                 }
        //             }
        //             // Remove documents from array
        //             $existingDocs = array_values(array_filter($existingDocs, function($doc, $index) use ($removeDocuments) {
        //                 return !in_array($index, $removeDocuments);
        //             }, ARRAY_FILTER_USE_BOTH));
        //         }
        //     }

        //     // Handle new document uploads
        //     if ($request->hasFile('documents')) {
        //         $newDocuments = [];
        //         foreach ($request->file('documents') as $index => $file) {
        //             $fileName = time() . '_' . $index . '_' . $file->getClientOriginalName();
        //             $path = $file->storeAs('employees/' . $id . '/documents', $fileName, 'public');
                    
        //             $newDocuments[] = [
        //                 'name' => $file->getClientOriginalName(),
        //                 'url' => Storage::disk('public')->url($path),
        //                 'path' => $path
        //             ];
        //         }
        //         $existingDocs = array_merge($existingDocs, $newDocuments);
        //     }
            
        //     $employee->documents = $existingDocs;
        //     $employee->save();

        //     return response()->json([
        //         'success' => true,
        //         'employee' => $employee,
        //         'state' => 'Employee updated successfully'
        //     ]);
            
        // } catch (Exception $e) {
        //     Log::error('Error editing employee: ' . $e->getMessage());
        //     return response()->json([
        //         'errors' => ['general' => ['An error occurred while updating the employee.']]
        //     ], 500);
        // }
    }

    public function deleteEmployee(Request $request, $id)
    {
        // try {
        //     // Validate company access
        //     $user = Auth::user();
        //     $company = Company::findOrFail($id);
            
        //     // Check permissions
        //     if ($user->company_id !== $company->id && !$user->hasAnyRole('Admin|organizationManager')) {
        //         return response()->json([
        //             'errors' => ['permission' => ['You do not have permission to delete employees from this company.']]
        //         ], 403);
        //     }

        //     $validator = Validator::make($request->all(), [
        //         'id' => 'required|exists:team_members,id'
        //     ], [
        //         'id.required' => 'Employee ID is required',
        //         'id.exists' => 'Employee not found'
        //     ]);

        //     if ($validator->fails()) {
        //         return response()->json([
        //             'errors' => $validator->errors()
        //         ], 422);
        //     }

        //     $employee = Team_member::where('id', $request->id)
        //                          ->where('Company_id', $id)
        //                          ->where('TeamMember', 'Employee')
        //                          ->firstOrFail();

        //     // Delete associated documents
        //     if ($employee->documents && is_array($employee->documents)) {
        //         foreach ($employee->documents as $document) {
        //             if (isset($document['path']) && Storage::disk('public')->exists($document['path'])) {
        //                 Storage::disk('public')->delete($document['path']);
        //             }
        //         }
        //     }

        //     $employee->delete();

        //     return response()->json([
        //         'success' => true,
        //         'state' => 'Employee deleted successfully'
        //     ]);
            
        // } catch (Exception $e) {
        //     Log::error('Error deleting employee: ' . $e->getMessage());
        //     return response()->json([
        //         'errors' => ['general' => ['An error occurred while deleting the employee.']]
        //     ], 500);
        // }
    }
}