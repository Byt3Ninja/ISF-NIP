<?php

namespace App\Http\Controllers;

use App\City;
use App\Like;
use App\Step;
use App\User;
use App\Cycle;
use App\Stage;
use App\State;
use App\Almuni;
use App\Sector;
use App\Company;
use App\Country;
use App\Quizzes;
use App\Service;
use App\Surveys;
use App\Currency;
use App\Employee;
use App\Formdata;
use App\Settings;
use App\Training;
use App\Milestone;
use App\Screening;
use App\Technology;
use App\Team_member;
use App\FundingRound;
use App\Organization;
use App\Finalscreening;
use App\MentorshipTask;
use App\StaticCategory;
use App\CompanyRelation;
use App\ServiceCategory;
use App\SustainableIcon;
use App\TrainingSession;
use App\ServicesRequests;
use App\Events\WebMessage;
use App\MentorshipRequest;
use App\ScreeningEvaluation;
use App\StartupCustomerGrowth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\CompanyService;
use App\ScreeningEvaluationResults;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\ScreeningEvaluationCustomFields;
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
        if (empty($company)) {
            return abort(404);
        }

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
        $Currencies = Currency::where('enabled', 1)->get(); // جلب جميع الأعضاء

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
        $evaluationForm = ScreeningEvaluation::getEvaluationData($company->step);
        $isResponsesPublished = false;
        $final_comment = null;
        $formData = collect([]);
        $formTitle = '';

        if (!empty($evaluationForm)) {
            $current_step = $company->stepdata;
            $form = Company::form($company);
            if ($current_step->stype != 2 && empty($form)) {
                return redirect()->back()->with('message2', 'No Form Found');
            }

            $formData = $form?->formData;
            $formTitle = $form?->form?->form_name ?? '';
            /* if( !empty( $formData ) ) { $formData = $formData->where('company_id', $company->id)->values(); }
            $formData = $formData->map(function ($item) {
                $field = $item->field()->first();
                $field_type = '';
                if( isset( $field?->options?->type ) ) { $field_type = $field?->options?->type; }
                $item->field_type = $field_type;
                $item->comment = '';
                return $item;
            }); */

            $myResponses = ScreeningEvaluationResults::where('company_id', $company->id)->where('evaluation_id', $evaluationForm->id)->where('user_id', $user_id)->get();
            $isResponsesPublished = $myResponses->where('status', true)->count() > 0 ? true : false;
            $myComment = ScreeningEvaluation::getMyFinalComment($evaluationForm->id, $user_id, $company->id);
            $final_comment = $myComment->value;
            $evaluationForm->final_comment = $final_comment;
            $evaluationForm->is_approved = $myComment->is_approved;
            $evaluationForm->categories->map(function ($category) use ($myResponses) {
                $category->subCriteria->map(function ($subCategory) use ($myResponses) {
                    $checkResponse = $myResponses->where('criteria_id', $subCategory->id)->first();
                    $subCategory->result = new stdClass;
                    $subCategory->result->score = $checkResponse?->score;
                    $subCategory->result->comment = $checkResponse?->comment;
                    return $subCategory;
                });
                return $category;
            });
            $evaluationForm->isPublished = $isResponsesPublished;
            $form_data_ids = $formData ? $formData->pluck('id')->toArray() : [];
            if (count($form_data_ids) > 0) {
                $comments = DB::table('form_data_judges_comments')->where('user_id', $user_id)->whereIn('form_data_id', $form_data_ids)->get();
                $formData = $formData->map(function ($fd) use ($comments) {
                    $fd->question = trim($fd->question);
                    $fd->comment = $comments->where('form_data_id', $fd->id)->first() ?? '';
                    return $fd;
                });
            } else {
                $comments = collect([]);
            }
        }
        $current_year = date('Y');
        $years = array_merge(range($current_year - 10, $current_year), range($current_year + 1, $current_year + 10));
        $growth_records = StartupCustomerGrowth::records( $company->id );
        $avgMonthlyGrowth = 0;
        $growthSinceFirstRecord = 0;
        $compoundAnnualGrowthRate = 0;
        $years_count = $growth_records->groupBy('year')->count();
        if( $growth_records->count() > 0 )
        {
            $count = $growth_records->count();
            $intervals = $count - 1;
            $last = $growth_records->last();
            $first = $growth_records->first();

            $growthSinceFirstRecord = (($last->total - $first->total) / $first->total) * 100;
            $growthSinceFirstRecord = round($growthSinceFirstRecord) . '%';

            $avgMonthlyGrowth = pow(($last->total / $first->total), (1 / $intervals)) - 1;
            $avgMonthlyGrowth = round($avgMonthlyGrowth * 100, 1) . '%';

            $compoundAnnualGrowthRate = pow(($last->total / $first->total), (1 / $years_count)) - 1;
            $compoundAnnualGrowthRate = round($compoundAnnualGrowthRate * 100, 1) . '%';
        }
        return view('admin/clients/startup/company_profile', compact('company', 'formTitle', 'formData', 'evaluationForm', 'Currencies', 'milestones', 'Achieved_Milestones', 'Planned_Milestones', 'icons', 'fonders', 'Shareholders', 'Investors', 'fileExists', 'pathToFile', 'technologies', 'sectors', 'industry', 'years', 'growth_records', 'growthSinceFirstRecord', 'avgMonthlyGrowth', 'compoundAnnualGrowthRate'));
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

                $Currencies = Currency::where('enabled', 1)->get(); // جلب جميع الأعضاء
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

                $Currencies = Currency::where('enabled', 1)->get(); // جلب جميع الأعضاء
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
                $selectedIndustryId = $companyRelations->pluck('industry_id')->filter()->first();
                $selectedSectorIds = $companyRelations->pluck('sector_id')->filter()->toArray();
                $selectedTechnologyIds = $companyRelations->pluck('technology_id')->filter()->toArray();

                // Get all technologies for the select2 dropdown
                $allTechnologies = DB::table('technologies')->get()->map(function ($tech) {
                    return [
                        'id' => $tech->id,
                        'name' => $tech->name,
                    ];
                })->values()->toArray();

                // Get selected technologies as names (for display in tags)
                $selectedTechnologies = DB::table('technologies')
                    ->whereIn('id', $selectedTechnologyIds)
                    ->pluck('name')
                    ->toArray();

                $technologies = $allTechnologies; // All technologies for select2
                $technologiesSelected = $selectedTechnologies; // Selected tech names for tags

                $allSectors = DB::table('sectors')->get();
                $industries = StaticCategory::with('sectors')->get();
                $data = $industries->map(function ($industry) use ($l, $allSectors, $selectedIndustryId, $selectedSectorIds) {
                    if ($industry->sectors->isNotEmpty()) {
                        $sectorsList = $industry->sectors->map(function ($sector) use ($l, $selectedSectorIds) {
                            return [
                                'id' => $sector->id,
                                'name' => $sector->{"name_$l"} ?? $sector->name_en ?? 'N/A',
                                'selected' => in_array($sector->id, $selectedSectorIds),
                            ];
                        })->values()->toArray();
                    } else {
                        // When industry has no sectors, return empty array (not all sectors)
                        $sectorsList = [];
                    }
                    return [
                        'id' => $industry->id,
                        'name' => $industry->name ?? $industry->name_en,
                        'selected' => $industry->id == $selectedIndustryId,
                        'sectors' => $sectorsList,
                    ];
                });
            } else {
                $industries = StaticCategory::with('sectors')->get();
                $technologies = DB::table('technologies')->get()->map(function ($tech) {
                    return [
                        'id' => $tech->id,
                        'name' => $tech->name,
                    ];
                })->values()->toArray();
                $technologiesSelected = [];
                $data = [];
            }
            $industries = $industries->map(function ($industry) {
                if (empty($industry->name_ar) || is_null($industry->name_ar) || !trim($industry->name_ar)) {
                    $industry->name_ar = $industry->name_en;
                }
                return $industry;
            });
            $employees = Employee::where('company_id', $id)->get();

            // Load all milestones for this company (new unified structure)
            $milestones = Milestone::where('company_id', $id)
                ->orderBy('created_at')->get();

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
                'technologiesSelected',
                'industries',
                'employees',
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
            if (!is_null($id)) {
                if (!$user->hasanyRole('Admin|organizationManager')) {
                    return redirect()->back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']);
                }
                if ($user->hasRole('organizationManager')) {
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

                    $check = collect($check)->first();
                    if (empty($check)) {
                        return redirect()->back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']);
                    } else {
                        $id = $check->id;
                    }
                }
                if (is_null($id)) {
                    return redirect()->back()->with(['msg' => 'NoHavePermission', 'class' => 'danger']);
                }
                $companyId = intval($id);
                $company = Company::find($companyId);
            } else {
                $companyId = intval($user->company_id);
                $company = Company::find($companyId);
            }
            $data = $request->all();

            // Update $data with decoded values if they were decoded in validation
            if ($request->has('tech_tags') && is_array($request->tech_tags)) {
                $data['tech_tags'] = $request->tech_tags;
            }

            if ($companyId) {
                $result = Company::find($companyId);
                if (empty($result)) {
                    $result = new Company();
                    $msgtype = 'AddComp';
                } else {
                    $msgtype = 'UpdateComp';
                }
            } else {
                $result = new Company();
                $msgtype = 'AddComp';
            }

            if ($data['CompanyN'] == 1) {

                $l = $request->input('locale', app()->getLocale());
                if ($l == 'en') {
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
                } else {
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
                    'Commercial_Register.file' => __('admin.invalid_file_format'),
                    'Commercial_Register.mimes' => __('admin.invalid_file_format'),
                    'Commercial_Register.max' => __('admin.file_size_exceeds'),
                    'Tax_Card.file' => __('admin.invalid_file_format'),
                    'Tax_Card.mimes' => __('admin.invalid_file_format'),
                    'Tax_Card.max' => __('admin.file_size_exceeds'),
                ]);

                // Custom Validation for Mandatory Files if "Have Registered Startup" is Yes
                if ($data['CreatedTax'] == 1) {
                    $hasCommercialRegister = $request->hasFile('Commercial_Register') || ($company->Commercial_Register && $company->Commercial_Register != 'null');
                    $hasTaxCard = $request->hasFile('Tax_Card') || ($company->Tax_Card && $company->Tax_Card != 'null');

                    if (!$hasCommercialRegister) {
                        throw ValidationException::withMessages(['Commercial_Register' => __('admin.commercial_register_required')]);
                    }
                    if (!$hasTaxCard) {
                        throw ValidationException::withMessages(['Tax_Card' => __('admin.tax_card_required')]);
                    }
                }

                // Handle "Other" category logic
                if (in_array('Other', $validatedData['business_categories'] ?? [])) {
                    $validatedData['business_categories'] = array_diff($validatedData['business_categories'], ['Other']);
                    if (!empty($validatedData['other_category'] ?? '')) {
                        $validatedData['business_categories'][] = 'Other';
                        $validatedData['business_categories'][] = $validatedData['other_category'];
                    }
                }

                $validatedData['business_categories'] = json_encode($validatedData['business_categories']);

                if ($data['CreatedTax'] == 0) {
                    $CreatedTax = 0;
                    $cuntryReg = 0;
                    $Commercial_Register = '';
                    $Tax_Card = '';
                } else {
                    if ($request->hasFile('Commercial_Register')) {
                        $file = $request->file('Commercial_Register');
                        $originalName = $file->getClientOriginalName();
                        $path = $companyId . '/documents/'; // بدون documents

                        $file->storeAs($path, $originalName, [
                            'disk' => 'document'
                        ]);
                        $Commercial_Register = '/documents/' . $path . $originalName;
                    } else {
                        $Commercial_Register = $company->Commercial_Register ?? 'null';
                    }
                    if ($request->hasFile('Tax_Card')) {
                        $file = $request->file('Tax_Card');
                        $originalName = $file->getClientOriginalName();
                        $path = $companyId . '/documents/';
                        // تخزين الملف بنفس اسمه الأصلي في مجلد public مباشرة
                        $file->storeAs($path, $originalName, [
                            'disk' => 'document'
                        ]);
                        $Tax_Card = '/documents/' . $path . $originalName;
                    } else {
                        $Tax_Card = $company->Tax_Card ?? 'null';
                    }
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
            if ($data['CompanyN'] == 2) {
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
                if ($request->hasFile('img_url')) {
                    // الحصول على الصورة
                    $image = $request->file('img_url');
                    // حفظ الصورة داخل القرص 'public_uploads'
                    $img_path = $image->store('images/logo', 'public_uploads');

                    // إنشاء رابط الصورة
                    $img_url = Storage::disk('public_uploads')->url($img_path);
                } else {
                    $img_url = $company->logo ?? 'null';
                }

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
            if ($data['CompanyN'] == 3) {
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
                } else {
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
            if ($data['CompanyN'] == 4) {
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
                if (!isset($data['targeted_market']) || $data['targeted_market'] == null) {
                    $data['targeted_market'] = ['null'];
                }

                if ($request->hasFile('financial_plan')) {
                    $file = $request->file('financial_plan'); // التعامل مع ملف واحد فقط
                    if ($file->isValid()) {
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
                } else {
                    $uploadedFile = $company->financial_plan;
                }

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
            if ($data['CompanyN'] == 5) {
                $request->validate([
                    'team_num' => 'required|string',
                ], [
                    'team_num.numeric' => __('admin.Raised fund must be a number'),
                ]);

                $result->team_num = $data['team_num'];
                $result->working_team_summary = $data['working_team_summary'];
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

                $result->pitch_deck_description = $data['pitch_deck_info'];
                $result->pitch_deck_summary = $data['pitch_deck_summary'];
                $result->problem = $data['problem_facing'];
                $result->solution = $data['solution_problem'];
            }
            if ($data['CompanyN'] == 8) {
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
                if ($request->hasFile('img_url')) {
                    // الحصول على الصورة
                    $image = $request->file('img_url');
                    // حفظ الصورة داخل القرص 'public_uploads'
                    $img_path = $image->store('images/logo', 'public_uploads');
                    // إنشاء رابط الصورة
                    $img_url = Storage::disk('public_uploads')->url($img_path);
                } else {
                    $img_url = $company->logo;
                }

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
            if ($data['CompanyN'] == 9) {
                $validator = Validator::make($request->all(), [
                    'pitch_deck' => 'nullable|mimes:pdf,ppt,pptx|max:10240', // يجب أن يكون ملف PDF أو PPT وحجمه أقل من 10MB
                ], [
                    'pitch_deck.mimes' => __('admin.Pitch_Deck must be a pdf or ppt file'),
                    'pitch_deck.max' => __('admin.Pitch_Deck max size is 10MB'),
                ]);

                if ($request->hasFile('pitch_deck')) {
                    $file = $request->file('pitch_deck');
                    $originalName = $file->getClientOriginalName();
                    $path = $companyId . '/pitch_decks/';
                    // تخزين الملف بنفس اسمه الأصلي في مجلد public مباشرة
                    $file->storeAs($path, $originalName, [
                        'disk' => 'pitch_deck'
                    ]);
                    $pitch_deck = 'pitch_decks/' . $path . $originalName;
                } else {
                    return redirect()->back()->with(['msg' => 'No_Document_Found', 'class' => 'danger'])->withInput();
                }

                $result->pitch_deck = $pitch_deck;
            }
            if ($data['CompanyN'] == 10) {
                $validator = Validator::make($request->all(), [
                    'pitch_deck_video' => 'nullable', // يجب أن يكون ملف PDF او PPT وحجمه لا يتجاوز 20MB
                ]);

                if ($request->pitch_deck_video == null) {
                    return redirect()->back()->with(['msg' => 'No_Video_Found', 'class' => 'danger'])->withInput();
                }
                $result->pitch_deck_video = $request->pitch_deck_video;
            }
            if ($data['CompanyN'] == 11) {
                if ($request->hasFile('documents')) {
                    $files = $request->file('documents');
                    $uploadedFiles = [];
                    foreach ($files as $file) {
                        if ($file->isValid()) {
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
                } else {
                    return redirect()->back()->with(['msg' => 'No_Document_Found', 'class' => 'danger'])->withInput();
                }

                $result->documents = json_encode($uploadedFiles);
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

                $result->about_problem = $data['about_problem'];
                $result->about_market_opportunity = $data['about_market_opportunity'];
                $result->about_competitors = $data['about_competitors'];
                $result->about_monetize = $data['about_monetize'];
                $result->about_challenges = $data['about_challenges'];
            }
            if ($data['CompanyN'] == 13) {
                $request->validate([
                    'business_model' => 'required',
                    'business_model_description' => 'required|string|max:255',
                    'business_plan' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:20480', // max limit to be defined later from admin panel settings
                ], [
                    'business_model.required' => __('admin.business_model is required'),
                    'business_model_description.required' => __('admin.business_model_description is required'),
                ]);

                if ($request->hasFile('business_plan')) {
                    $file = $request->file('business_plan'); // التعامل مع ملف واحد فقط
                    if ($file->isValid()) {
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
                } else {
                    return redirect()->back()->with(['msg' => 'Business Plan file not selected', 'class' => 'danger'])->withInput();
                }

                $result->business_model = $data['business_model'];
                $result->other_business_model = $data['other_business_model'];
                $result->business_model_description = $data['business_model_description'];
                $result->business_plan = $uploadedFile;
            }
            $result->save();
            if ($data['CompanyN'] == 14) {
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

                // Decode tech_tags if it's a JSON string
                $requestData = $request->all();
                if (!empty($requestData['tech_tags']) && is_string($requestData['tech_tags'])) {
                    try {
                        $decoded = json_decode($requestData['tech_tags'], true);
                        if (is_array($decoded)) {
                            $requestData['tech_tags'] = $decoded;
                        } else {
                            $requestData['tech_tags'] = [];
                        }
                    } catch (\Exception $e) {
                        $requestData['tech_tags'] = [];
                    }
                } elseif (empty($requestData['tech_tags'])) {
                    $requestData['tech_tags'] = [];
                }

                // Merge decoded data back into request
                $request->merge($requestData);

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

                if ($validator->fails()) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => $validator->errors()->first(),
                            'errors' => $validator->errors()
                        ], 422);
                    }
                    return redirect()->back()->withErrors($validator)->withInput();
                }

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
                        if ($request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => __('admin.error_processing_sectors')
                            ], 400);
                        }
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

                // Process tech_tags if provided (from the form)
                if (isset($data['tech_tags'])) {
                    $techTags = [];
                    if (is_string($data['tech_tags'])) {
                        try {
                            $techTags = json_decode($data['tech_tags'], true);
                            if (!is_array($techTags)) {
                                $techTags = [$techTags];
                            }
                        } catch (\Exception $e) {
                            Log::error('Error decoding tech_tags', ['error' => $e->getMessage()]);
                            $techTags = [];
                        }
                    } elseif (is_array($data['tech_tags'])) {
                        $techTags = $data['tech_tags'];
                    }

                    // Process tech tags to get or create technologies
                    foreach ($techTags as $tag) {
                        if (empty(trim($tag)))
                            continue;

                        $tech = Technology::firstOrCreate(
                            ['name' => trim($tag)],
                            ['name' => trim($tag)]
                        );

                        if ($tech && !in_array($tech->id, $techIds)) {
                            $techIds[] = $tech->id;
                        }
                    }
                }

                // Require at least one technology selection
                if (empty($techIds) && empty($techTags) && empty($request->other_tech)) {
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => __('admin.please_select_technology')
                        ], 422);
                    }
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
                        if ($request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => __('admin.error_creating_sector')
                            ], 400);
                        }
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
                        if ($request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => __('admin.error_creating_technology')
                            ], 400);
                        }
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

                // Ensure company is saved and has an ID
                if (empty($result->id)) {
                    $result->save();
                }

                if (empty($result->id)) {
                    Log::error('Company ID is missing', [
                        'company_data' => $result->toArray()
                    ]);
                    if ($request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => __('admin.error_company_not_saved')
                        ], 400);
                    }
                    return redirect()->back()
                        ->with('msg', __('admin.error_company_not_saved'))
                        ->with('class', 'danger')
                        ->withInput();
                }

                // Validate industry_id exists
                if (!empty($data['industry_id'])) {
                    $industryExists = \DB::table('static_categories')->where('id', $data['industry_id'])->exists();
                    if (!$industryExists) {
                        Log::error('Invalid industry_id', ['industry_id' => $data['industry_id']]);
                        if ($request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => __('admin.invalid_industry_selected')
                            ], 400);
                        }
                        return redirect()->back()
                            ->with('msg', __('admin.invalid_industry_selected'))
                            ->with('class', 'danger')
                            ->withInput();
                    }
                }

                DB::beginTransaction();
                try {
                    $timestamp = now();
                    $relations = [];

                    // Get existing relations to determine what needs to be updated
                    $existingRelations = CompanyRelation::where('company_id', $result->id)
                        ->where('user_id', $user->id)
                        ->get();

                    // Process sector relations
                    $processedSectorIds = [];
                    foreach ($sectorIds as $sectorId) {
                        if (empty($sectorId))
                            continue;

                        // Validate sector exists
                        $sectorExists = \DB::table('sectors')->where('id', $sectorId)->exists();
                        if (!$sectorExists) {
                            Log::warning('Invalid sector_id skipped', ['sector_id' => $sectorId]);
                            continue;
                        }

                        // Check if this relation already exists
                        $existingRelation = $existingRelations->where('sector_id', $sectorId)->first();

                        if ($existingRelation) {
                            // Update existing relation
                            $existingRelation->update([
                                'industry_id' => $data['industry_id'],
                                'updated_at' => $timestamp
                            ]);
                        } else {
                            // Add new relation
                            $relations[] = [
                                'company_id' => $result->id,
                                'user_id' => $user->id,
                                'industry_id' => $data['industry_id'],
                                'sector_id' => $sectorId,
                                'created_at' => $timestamp,
                                'updated_at' => $timestamp
                            ];
                        }
                        $processedSectorIds[] = $sectorId;
                    }

                    // Process technology relations
                    $processedTechIds = [];
                    foreach ($techIds as $techId) {
                        if (empty($techId))
                            continue;

                        // Validate technology exists
                        $techExists = \DB::table('technologies')->where('id', $techId)->exists();
                        if (!$techExists) {
                            Log::warning('Invalid technology_id skipped', ['technology_id' => $techId]);
                            continue;
                        }

                        // Check if this relation already exists
                        $existingRelation = $existingRelations->where('technology_id', $techId)->first();

                        if ($existingRelation) {
                            // Update existing relation
                            $existingRelation->update([
                                'industry_id' => $data['industry_id'],
                                'updated_at' => $timestamp
                            ]);
                        } else {
                            // Add new relation
                            $relations[] = [
                                'company_id' => $result->id,
                                'user_id' => $user->id,
                                'industry_id' => $data['industry_id'],
                                'technology_id' => $techId,
                                'created_at' => $timestamp,
                                'updated_at' => $timestamp
                            ];
                        }
                        $processedTechIds[] = $techId;
                    }

                    // Remove relations that are no longer selected
                    $existingRelations->each(function ($relation) use ($processedSectorIds, $processedTechIds) {
                        if (
                            ($relation->sector_id && !in_array($relation->sector_id, $processedSectorIds)) ||
                            ($relation->technology_id && !in_array($relation->technology_id, $processedTechIds))
                        ) {
                            $relation->delete();
                        }
                    });

                    // Bulk insert all relations
                    if (!empty($relations)) {
                        CompanyRelation::insert($relations);
                    }

                    DB::commit();
                    Log::info('Company relations updated', [
                        'company_id' => $result->id,
                        'sector_count' => count($sectorIds),
                        'tech_count' => count($techIds),
                        'relations_count' => count($relations)
                    ]);
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Failed to update company relations', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'company_id' => $result->id ?? null,
                        'sector_ids' => $sectorIds,
                        'tech_ids' => $techIds,
                        'industry_id' => $data['industry_id'] ?? null
                    ]);

                    if ($data['CompanyN'] == 14 && $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => __('admin.error_updating_company_relations') . ': ' . $e->getMessage()
                        ], 400);
                    }

                    return redirect()->back()
                        ->with('msg', __('admin.error_updating_company_relations'))
                        ->with('class', 'danger')
                        ->withInput();
                }
            }
            if (is_null($id)) {
                User::where('id', $user->id)->update(['company_id' => $result->id]);
                $msgtype = 'NewComp';
            }

            if ($result) {
                if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                    event(new WebMessage($msgtype, $user->first_name_en));
                }

                // Use the result's ID if $id is null (new company creation)
                $redirectId = $id ?: $result->id;

                // Return JSON response for AJAX requests (CompanyN == 14 is classification form)
                if ($data['CompanyN'] == 14 && $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => __('admin.startup_info_updated')
                    ]);
                }

                return redirect()->route('company.profile_view_mine', $redirectId)->with(['msg' => __('admin.startup_info_updated'), 'class' => 'success']);
            } else {
                if ($data['CompanyN'] == 14 && $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('admin.Startup_Info_Updated_Failed')
                    ], 400);
                }
                return back()->withInput()->with(['msg' => 'Startup_Info_Updated_Failed', 'class' => 'danger']);
            }
        } catch (ValidationException $e) {
            // عند وجود خطأ في التحقق، إرجاع رسائل الأخطاء للمستخدم
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (Exception $e) {
            // dd($e);
            // تسجيل الأخطاء الأخرى
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('admin.please_complete_startup_info')
                ], 500);
            }
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
        if (isset($id) && !is_null($id)) {
            $user = User::where('company_id', $id)->first();
        } else {
            $user = auth()->user();
        }
        // $user = Auth::user();
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
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => $validator->errors(),
                    ], 422); // حالة الخطأ 422
                } else {
                    return redirect()->back()->with('error', 'inputs error')->withErrors($validator)->withInput();
                }
            }

            $email = $request->input('founder_email');
            $mail = Team_member::where('Email', $email)->where('Company_id', $companyId)->where('TeamMember', 'Founder')->first();

            if ($mail != null) {
                if ($request->ajax()) {
                    return response()->json(['state' => __('admin.founder_email_exist'),]);
                } else {
                    return redirect()->back()->with('error', __('admin.founder_email_exist'));
                }
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
            if ($request->ajax()) {
                return response()->json(['state' => __('admin.founder_added'),]);
            } else {
                return redirect()->back()->with('success', __('admin.founder_added'));
            }
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
            $totalPercentage = Team_member::where('Company_id', $companyId)->sum('PresentagShereholder');

            $newPercentage = $request->shareholder_ownership_percentage;

            // تحقق من أن النسبة المئوية الجديدة لا تتجاوز 100%
            if ($totalPercentage + $newPercentage > 100) {
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => [
                            'shareholder_ownership_percentage' => [__('admin.Total ownership percentage cannot exceed 100%')],
                        ],
                    ], 422); // حالة الخطأ 422
                } else {
                    return redirect()->back()->with('error', 'inputs error')->withErrors(__('admin.Total ownership percentage cannot exceed 100%'))->withInput();
                }
            }
            // إذا كانت البيانات غير صالحة، إرجاع الأخطاء إلى الجافا سكريبت
            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => $validator->errors(),
                    ], 422); // حالة الخطأ 422
                } else {
                    return redirect()->back()->with('error', 'inputs error')->withErrors($validator)->withInput();
                }
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
            if ($request->ajax()) {
                return response()->json(['state' => __('admin.Investor_added'),]);
            } else {
                return redirect()->back()->with('success', __('admin.founder_added'));
            }
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

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'errors' => $validator->errors(),
                    ], 422); // حالة الخطأ 422
                } else {
                    return redirect()->back()->with('error', 'inputs error')->withErrors($validator)->withInput();
                }
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
            if ($request->ajax()) {
                return response()->json(['state' => __('admin.Investor_added'),]);
            } else {
                return response()->json(['state' => __('admin.Investor_added'),]);
            }
        }
    }

    public function EditTeams(Request $request, $id = null)
    {
        if (isset($id) && !is_null($id)) {
            $user = User::where('company_id', $id)->first();
        } else {
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
            if (!$id) {
                return response()->json([
                    'errors' => ['company' => [__('startup.startup_not_found')]],
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
            $milestone->Company_id = $id;
            $milestone->save();

            // Handle file uploads if any
            $documents = [];
            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $index => $file) {
                    $fileName = time() . '_' . $index . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('milestones/' . $id, $fileName, 'public');

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
            if (!$id) {
                return response()->json([
                    'errors' => ['company' => [__('startup.startup_not_found')]],
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
                ->where('Company_id', $id)
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
                    $existingDocuments = array_values(array_filter($existingDocuments, function ($doc, $index) use ($removeDocuments) {
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
                    $filePath = $file->storeAs('milestones/' . $id, $fileName, 'public');

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
            if (!$id) {
                return response()->json([
                    'errors' => ['company' => [__('startup.startup_not_found')]],
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
                ->where('Company_id', $id)
                ->first();

            if (!$milestone) {
                return response()->json([
                    'errors' => ['milestone' => [__('startup.milestone_not_found')]],
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
                'state' => __('startup.milestone_delete_success'),
                'success' => true,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting milestone: ' . $e->getMessage(), [
                'milestone_id' => $request->id ?? 'unknown',
                'company_id' => $id ?? 'unknown',
                'user_id' => Auth::id(),
                'stack_trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'errors' => ['general' => [__('startup.milestone_delete_failed')]],
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

        // Handle Alumni filter
        if ($request->filled('alumni')) {
            $alumni = $request->get('alumni');
            if ($alumni !== 'all') {
                $filters['is_alumni'] = $alumni;
            }
        }

        $companies = $this->companyService->getCompanies($filters, isApi: false);

        // Pass the language variable
        $l = app()->getLocale();

        return view('admin/users/companies', compact('companies', 'l'));
    }

    public function companyAlumni(Request $request)
    {
        return view('admin.clients.startup.company_alumni');
    }

    public function addEmployee(Request $request, $id)
    {
        // dd($request->all());
        try {
            if ($id) {
                $companyId = $id;

                // Verify that current user has access to this company (allow admins)
                if (Auth::user()->company_id !== $companyId && !Auth::user()->hasanyRole('Admin|organizationManager|registered')) {
                    return response()->json([
                        'errors' => ['permission' => [__('You do not have permission to access this company.')]],
                    ], 403);
                }
            } else {
                $companyId = Auth::user()->company_id;
            }

            $validator = Validator::make($request->all(), [
                'full_name' => 'required|string|max:255',
                'job_title' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'employment_type' => 'required|in:full-time,part-time,contractor,intern',
                'hiring_date' => 'required|date',
                'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx|max:10240'
            ], [
                'full_name.required' => 'Employee name is required',
                'job_title.required' => 'Job title is required',
                'gender.required' => 'Gender is required',
                'employment_type.required' => 'Employment type is required',
                'hiring_date.required' => 'Hiring date is required',
                'documents.*.mimes' => 'Documents must be PDF, JPG, PNG, or DOCX files',
                'documents.*.max' => 'Each document must be less than 10MB'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee = new Employee();
            $employee->company_id = $companyId;
            $employee->full_name = $request->full_name;
            $employee->job_title = $request->job_title;
            $employee->gender = $request->gender;
            $employee->employment_type = $request->employment_type;
            $employee->hiring_date = $request->hiring_date;

            // Handle document uploads
            if ($request->hasFile('documents')) {
                $documents = [];
                foreach ($request->file('documents') as $index => $file) {
                    $fileName = time() . '_' . $index . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('employees/' . $id . '/documents', $fileName, 'public');

                    $documents[] = [
                        'name' => $file->getClientOriginalName(),
                        'url' => asset('storage/' . $path),
                        'path' => $path
                    ];
                }
                $employee->documents = $documents;
            }

            $employee->save();
            // dd($employee);
            return response()->json([
                'success' => true,
                'employee' => $employee,
                'state' => 'Employee added successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error adding employee: ' . $e->getMessage());
            return response()->json([
                'errors' => ['general' => ['An error occurred while adding the employee.']]
            ], 500);
        }
    }

    public function editEmployee(Request $request, $id)
    {
        try {
            if ($id) {
                $companyId = $id;

                // Verify that current user has access to this company (allow admins)
                if (Auth::user()->company_id !== $companyId && !Auth::user()->hasanyRole('Admin|organizationManager|registered')) {
                    return response()->json([
                        'errors' => ['permission' => [__('You do not have permission to access this company.')]],
                    ], 403);
                }
            } else {
                $companyId = Auth::user()->company_id;
            }


            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:employees,id',
                'full_name' => 'required|string|max:255',
                'job_title' => 'required|string|max:255',
                'gender' => 'required|in:male,female',
                'employment_type' => 'required|in:full-time,part-time,contractor,intern',
                'hiring_date' => 'required|date',
                'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx|max:10240',
                'remove_documents' => 'nullable|string'
            ], [
                'id.required' => 'Employee ID is required',
                'id.exists' => 'Employee not found',
                'full_name.required' => 'Employee name is required',
                'job_title.required' => 'Job title is required',
                'gender.required' => 'Gender is required',
                'employment_type.required' => 'Employment type is required',
                'hiring_date.required' => 'Hiring date is required',
                'documents.*.mimes' => 'Documents must be PDF, JPG, PNG, or DOCX files',
                'documents.*.max' => 'Each document must be less than 10MB'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $employee = Employee::where('id', $request->id)
                ->where('company_id', $companyId)
                ->firstOrFail();

            $employee->full_name = $request->full_name;
            $employee->job_title = $request->job_title;
            $employee->gender = $request->gender;
            $employee->employment_type = $request->employment_type;
            $employee->hiring_date = $request->hiring_date;

            // Handle existing documents
            $existingDocs = $employee->documents ?: [];

            // Handle document removals
            if ($request->has('remove_documents')) {
                $removeDocuments = json_decode($request->input('remove_documents'), true);
                if (is_array($removeDocuments)) {
                    foreach ($removeDocuments as $index) {
                        if (isset($existingDocs[$index])) {
                            $document = $existingDocs[$index];
                            // Delete physical file
                            if (isset($document['path']) && Storage::disk('public')->exists($document['path'])) {
                                Storage::disk('public')->delete($document['path']);
                            }
                        }
                    }
                    // Remove documents from array
                    $existingDocs = array_values(array_filter($existingDocs, function ($doc, $index) use ($removeDocuments) {
                        return !in_array($index, $removeDocuments);
                    }, ARRAY_FILTER_USE_BOTH));
                }
            }

            // Handle new document uploads
            if ($request->hasFile('documents')) {
                $newDocuments = [];
                foreach ($request->file('documents') as $index => $file) {
                    $fileName = time() . '_' . $index . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('employees/' . $id . '/documents', $fileName, 'public');

                    $newDocuments[] = [
                        'name' => $file->getClientOriginalName(),
                        'url' => asset('storage/' . $path),
                        'path' => $path
                    ];
                }
                $existingDocs = array_merge($existingDocs, $newDocuments);
            }

            $employee->documents = $existingDocs;
            $employee->save();

            return response()->json([
                'success' => true,
                'employee' => $employee,
                'state' => 'Employee updated successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error editing employee: ' . $e->getMessage());
            return response()->json([
                'errors' => ['general' => ['An error occurred while updating the employee.']]
            ], 500);
        }
    }

    public function deleteEmployee(Request $request, $id)
    {
        try {
            if ($id) {
                $companyId = $id;

                // Verify that current user has access to this company (allow admins)
                if (Auth::user()->company_id !== $companyId && !Auth::user()->hasanyRole('Admin|organizationManager|registered')) {
                    return response()->json([
                        'errors' => ['permission' => [__('You do not have permission to access this company.')]],
                    ], 403);
                }
            } else {
                $companyId = Auth::user()->company_id;
            }

            // Get employee ID from request - handle both JSON and form data
            $employeeId = null;

            // Try to get from JSON body first (for DELETE requests with JSON)
            $contentType = $request->header('Content-Type');
            if ($contentType && strpos($contentType, 'application/json') !== false) {
                $jsonData = json_decode($request->getContent(), true);
                if ($jsonData && isset($jsonData['id'])) {
                    $employeeId = $jsonData['id'];
                }
            }

            // Fallback to input/get methods (for form data)
            if (!$employeeId) {
                $employeeId = $request->input('id') ?? $request->get('id');
            }

            if (!$employeeId) {
                return response()->json([
                    'errors' => ['id' => ['Employee ID is required']]
                ], 422);
            }

            $validator = Validator::make(['id' => $employeeId], [
                'id' => 'required|exists:employees,id'
            ], [
                'id.required' => 'Employee ID is required',
                'id.exists' => 'Employee not found'
            ]);

        //     if ($validator->fails()) {
        //         return response()->json([
        //             'errors' => $validator->errors()
        //         ], 422);
        //     }

        //     $employee = Team_member::where('id', $request->id)
        //                          ->where('Company_id', $id)
        //                          ->where('TeamMember', 'Employee')
        //                          ->firstOrFail();

            // Delete associated documents
            if ($employee->documents && is_array($employee->documents)) {
                foreach ($employee->documents as $document) {
                    if (isset($document['path']) && Storage::disk('public')->exists($document['path'])) {
                        Storage::disk('public')->delete($document['path']);
                    }
                }
            }

            $employee->delete();

            return response()->json([
                'success' => true,
                'state' => 'Employee deleted successfully'
            ]);
            
        } catch (Exception $e) {
            Log::error('Error deleting employee: ' . $e->getMessage());
            return response()->json([
                'errors' => ['general' => ['An error occurred while deleting the employee.']]
            ], 500);
        }
    }

    public function addCompanyAlumni(Request $request, $id)
    {
        
        try {
            $user = Auth::user();
            $company = Company::findOrFail($id);
            $company_relations = CompanyRelation::where('company_id', $id)->where('user_id', $company->user->id)->get();
            $almuni = new Almuni();
            $almuni->user_id = $company->user->id;
            $almuni->cycle_id = $company->cycle;
            $almuni->company_id = $id;
            $almuni->alumni = '1';
            $almuni->established_date = date('Y-m-d H:i:s');
           
            //Relations Data
            $relations_data = $company_relations->map(function ($relation) {
                                    return [
                                        'Industry'    => $relation->industry?->name_en,
                                        'Sector'      => $relation->sector?->name_en,
                                        'Technology'  => $relation->technology?->name_en,
                                    ];
                                })->values();
            $basic_startup_information = [
                    'ID' => $company->id,
                    'Startup_Name' => $company->name_en,
                    'Alumni' => 'Yes',
                    'Established_Date' => date('Y-m-d H:i:s'),
                    'Location' => implode(', ', array_filter([
                        $company->city->name_en ?? '',
                        $company->country->name_en ?? '',
                    ])),
                    'Country' => $company->country->name_en,
                    'Business_Type' => $company->business_type,
                    'Relations'     => $relations_data,
                    'Website_Link' => $company->website,
                    'Social Media Links' => [
                        'Facebook' => $company->facebook,
                        'Twitter' => $company->twitter,
                        'Linkedin' => $company->linkedin,
                        'Instagram' => $company->instagram,
                        'Youtube' => $company->youtube,
                        'Google_Map' => $company->google_map,
                        'tiktok' => $company->tiktok,
                    ],
                ];
            $almuni->basic_startup_information = json_encode($basic_startup_information);

            
                $team_leaders = Team_member::where('company_id', $id)
                    ->where('TeamMember', 'hackathon')
                    ->get();

                $startup_leader_information = [];

                foreach ($team_leaders as $leader) {
                    $startup_leader_information[] = [
                        'ID' => $leader->id,
                        'Full_Name' => $leader->Name,
                        'Country' => $company->country->name_en ?? '',
                        'Email' => $leader->Email,
                        'Phone_Number' => $leader->Phone,
                    ];
                }

                $almuni->startup_leader_information = json_encode($startup_leader_information);

                $startup_founders = Team_member::where('company_id', $id)
                    ->where('TeamMember', 'Founder')
                    ->get();

                $founders = [];

                foreach ($startup_founders as $founder) {
                    $founders[] = [
                        'ID' => $founder->id,
                        'Full_Name' => $founder->Name,
                        'Country' => $company->country->name_en ?? '',
                        'Email' => $founder->Email,
                        'Phone_Number' => $founder->Phone,
                        'LinkedIn Profile' => $founder->LinkedIn
                    ];
                }

                $almuni->founders = json_encode($founders);

                $startup_employees = Team_member::where('company_id', $id)
                    ->where('TeamMember', 'Employee')
                    ->get();

                $employees = [];

                foreach ($startup_employees as $employee) {
                    $employees[] = [
                        'ID' => $employee->id,
                        'Full_Name' => $employee->Name,
                        'Country' => $company->country->name_en ?? '',
                        'Email' => $employee->Email,
                        'Phone_Number' => $employee->Phone,
                        'Job Title' => $employee->RoleShereholder,
                        'Gender' => $employee->gender,
                        'Employment Type' => $employee->employment_type,
                        'Start Date' => $employee->hiring_date
                        
                    ];
                }

                $almuni->employees = json_encode($employees);

                $startup_shareholders = Team_member::where('company_id', $id)
                    ->where('TeamMember', 'Shareholder')
                    ->get();

                $shareholders = [];

                foreach ($startup_shareholders as $shareholder) {
                    $shareholders[] = [
                        'ID' => $shareholder->id,
                        'Full_Name' => $shareholder->Name,
                        'Country' => $company->country->name_en ?? '',
                        'Email' => $shareholder->Email,
                        'Phone_Number' => $shareholder->Phone,
                        'Role' => $shareholder->RoleShereholder,
                        'Ownership Percentage' => $shareholder->PresentagShereholder
                    ];
                }

                $almuni->shareholders = json_encode($shareholders);


                $startup_investors = Team_member::where('company_id', $id)
                    ->where('TeamMember', 'Investor')
                    ->get();

                $investors = [];

                foreach ($startup_investors as $investor) {
                    $investors[] = [
                        'ID' => $investor->id,
                        'Full_Name' => $investor->Name,
                        'Country' => $company->country->name_en ?? '',
                        'Email' => $investor->Email,
                        'Phone_Number' => $investor->Phone,
                        'Type' => $investor->investorType
                    ];
                }

                $almuni->investors = json_encode($investors);

                $almuni->sustainability_goals = $company->Sustainable_development;


                    $pitch_deck[] = [
                        'pitch deck' => $company->pitch_deck ?? '',
                        'pitch deck video' => $company->pitch_deck_video ?? '',
                        'pitch deck description' => $company->pitch_deck_description ?? '',
                        'pitch deck summary' => $company->pitch_deck_summary ?? '',
                    ];
                $almuni->pitch_deck = json_encode($pitch_deck);


                $legal_information[] = [
                        'Registered' => $company->Commercial_Register  ? 'Registered' : 'Not Registered',
                        'Commercial Register' => $company->Commercial_Register ?? '',
                        'Tax Card' => $company->Tax_Card ?? '',
                        'Country Register id' => $company->Country_Register_id ?? '',
                    ];
                $almuni->legal_information = json_encode($legal_information);

                    $cycle[] = [
                        'Program Name' => $company->cycledata->title ?? '',
                        'ID' =>  $company->cycledata->id ?? '',
                        'Organization' =>  $company->cycledata->organization ?? '',
                        'Cohort' =>  $company->cycledata->cohort ?? '',
                        'Start Date' => $company->cycledata->start->format('Y-m-d H:i') ?? '',
                        'Completion Date' => $company->cycledata->end->format('Y-m-d H:i') ?? '',
                        'Type' => $company->cycledata->type ?? '',
                    ];
                $almuni->program = json_encode($cycle);


            $company_id = $company->id;
            $cycle_id = $company->cycle;

            $_steps = Step::where('cycle_id', $cycle_id)
                ->where('stype', 2)
                ->orderBy('arr')
                ->get();
            $companies = Company::where('cycle', $cycle_id)->get();
            $stepsData = collect();
            $l = app()->getLocale();
            foreach ($_steps as $step) {
                $evaluation = ScreeningEvaluation::where('step_id', $step->id)->first();
                $results = ScreeningEvaluationResults::where('evaluation_id', $evaluation?->id)->get();
                $get_comments = ScreeningEvaluationCustomFields::where('name', 'final_comment')
                    ->where('evaluation_id', $evaluation?->id)
                    ->with('values')
                    ->first();

                $comments = collect([]);
                if($get_comments && $get_comments->values) {
                    $comments = $get_comments->values->map(function($c) {
                        $c->evaluation_id = $c->evaluation_id;
                        return $c;
                    });
                }
                $getUsers = Screening::where('step_id', $step->id)->with('user')->get();
                $judges = $getUsers->map(function($item) use ($l) {
                    $user = $item->user;
                    $j = new stdClass;
                    $j->id = $user->id ?? 0;
                    $j->name = ($user->{'first_name_' . $l} ?? '') . ' ' . ($user->{'last_name_' . $l} ?? '');
                    $j->phone = $user->phone ?? '';
                    $j->email = $user->email ?? '';
                    $j->image_url = $user->image_url ?? '';
                    return $j;
                })->unique('id')->values();
                $judges = $judges->map(function($judge) use ($getUsers) {
                    $startups = $getUsers->where('user_id', $judge->id)
                        ->map(fn($i) => $i->users)
                        ->flatten()
                        ->unique()
                        ->filter()
                        ->values();
                    $judge->startups = $startups->pluck('id')->values();
                    return $judge;
                });
                foreach ($judges as $jdx => $judge) {
                    $judges[$jdx]->companies = collect([]);
                    foreach ($judge->startups as $cid) {

                        $_comment = '';
                        $_approval = 'pending';

                        $judge_comment = $comments->where('user_id', $judge->id)
                            ->where('company_id', $cid)
                            ->first();

                        if($judge_comment) {
                            $_comment = $judge_comment->value ?? '';
                            $_approval = intval($judge_comment->is_approved) === 1
                                ? 'approved'
                                : (intval($judge_comment->is_approved) === 2 ? 'disapproved' : 'pending');
                        }

                        $_results = $results->where('company_id', $cid)
                            ->where('user_id', $judge->id)
                            ->values();

                        $_company = new stdClass;
                        $_company->id = $cid;
                        $_company->status = $_results->count() > 0
                            ? ($_results->where('status', 0)->count() > 0 ? 'Draft' : 'Published')
                            : 'Not Evaluated';
                        $_company->comment = $_comment;
                        $_company->approval = $_approval;
                        $_company->results = $_results;

                        $judges[$jdx]->companies->push($_company);
                    }
                }
                foreach ($companies as $cidx => $comp) {
                    $avg = $judges
                        ->filter(fn($j) => $j->companies->where('status', 'Published')->where('id', $comp->id)->isNotEmpty())
                        ->avg(function($j) use ($comp) {
                            $item = $j->companies->where('status', 'Published')->where('id', $comp->id)->first();
                            return $item ? $item->results->sum(fn($r) => $r->score ?? 0) : null;
                        });
                    $companies[$cidx]->average_score = $avg ?? 0;
                }
                $stepsData->push([
                    'step_id' => $step->id,
                    'step_title' => $step->title,
                    'companies' => $companies->map(function($c) { 
                        return [
                            'company_id' => $c->id,
                            'average_score' => $c->average_score
                        ];
                    })->values(),
                    'judges' => $judges->map(function($j) {
                        return [
                            'judge_id' => $j->id,
                            'judge_name' => $j->name,
                            'phone' => $j->phone,
                            'email' => $j->email,
                            'image_url' => $j->image_url,
                            'evaluations' => $j->companies->map(function($c) {
                                return [
                                    'company_id' => $c->id,
                                    'score' => $c->results->sum(fn($r) => $r->score ?? 0),
                                    'status' => $c->status,
                                    'comment' => $c->comment,
                                    'approval' => $c->approval
                                ];
                            })->values()
                        ];
                    })->values()
                ]);
            }
            $screeningData = $stepsData;



        // return or view
        //  dd($screeningData);


                $almuni->screening = json_encode($screeningData);

                    //Final Screening
                $company_id = $company->id;
                $cycle_id = $company->cycle;
                $_steps = Step::where('cycle_id', $cycle_id)
                    ->where('stype', 5)
                    ->orderBy('arr')
                    ->get();
                $companies = Company::where('cycle', $cycle_id)->get();
                $finalstepsData = collect();

                $l = app()->getLocale();

                foreach ($_steps as $step) {

                    $finals = Finalscreening::where('step_id', $step->id)
                        ->with(['company', 'company.users'])
                        ->get()
                        ->map(function($f) {
                            $f->judgers_ids = array_map(fn($i) => intval($i), $f->judges);
                            return $f;
                        });

                    $users_ids = $finals
                        ->map(fn($f) => array_map(fn($i) => intval($i), $f->judges))
                        ->flatten()
                        ->unique()
                        ->values();

                    $judges = User::whereIn('id', $users_ids)
                        ->get()
                        ->map(function($user) use ($l) {
                            $j = new stdClass;
                            $j->id = $user->id ?? 0;
                            $j->name = ($user->{'first_name_' . $l} ?? '') . ' ' . ($user->{'last_name_' . $l} ?? '');
                            $j->phone = $user->phone ?? '';
                            $j->email = $user->email ?? '';
                            $j->image_url = $user->image_url ?? '';
                            return $j;
                        })->unique('id')->values();
                    $judges = $judges->map(function($item) use ($finals) {
                        $startups = $finals
                            ->filter(fn($f) => in_array($item->id, $f->judgers_ids))
                            ->values()
                            ->map(fn($f) => $f?->company?->users)
                            ->flatten()
                            ->unique()
                            ->filter(fn($i) => !is_null($i))
                            ->values();

                        $item->startups = $startups;
                        return $item;
                    });
                    foreach ($judges as $idx => $judge) {
                        $startup_ids = $judge->startups->pluck('id')->toArray();
                        $get_companies = $companies->filter(fn($c) => $c->users->whereIn('id', $startup_ids)->count() > 0);
                        $judges[$idx]->startups = $get_companies->pluck('id');
                    }
                    $judges = $judges->map(function($judge) use ($companies) {
                        $judge->companies = collect([]);
                        foreach ($judge->startups as $company_id) {
                            $_company = new stdClass;
                            $_company->id = $company_id;
                            $_company->status = 'Not Evaluated';
                            $_company->results = collect([]);
                            $_company->comment = '';
                            $_company->approval = 'pending';
                            $judge->companies->push($_company);
                        }
                        return $judge;
                    });
                    $_companies = $companies->map(function($comp) use ($judges, $step) {
                        $average_score = $judges
                            ->filter(fn($j) => in_array($comp->id, $j->startups->toArray()))
                            ->avg(fn($j) => $j->companies->where('id', $comp->id)->first()->results->sum(fn($r) => $r->score ?? 0));
                        $comp->average_score = $average_score ?? 0;
                        $comp->judges = $judges;
                        $comp->company_step_id = $step->id;
                        $comp->company_cycle_id = $step->cycle_id;
                        return $comp;
                    });
                    $stepsData->push([
                        'step_id' => $step->id,
                        'step_title' => $step->title,
                        'companies' => $_companies->map(fn($c) => [
                            'company_id' => $c->id,
                            'average_score' => $c->average_score
                        ])->values(),
                        'judges' => $judges->map(function($j) {
                            return [
                                'judge_id' => $j->id,
                                'judge_name' => $j->name,
                                'phone' => $j->phone,
                                'email' => $j->email,
                                'image_url' => $j->image_url,
                                'evaluations' => $j->companies->map(fn($c) => [
                                    'company_id' => $c->id,
                                    'score' => $c->results->sum(fn($r) => $r->score ?? 0),
                                    'status' => $c->status,
                                    'comment' => $c->comment,
                                    'approval' => $c->approval
                                ])->values()
                            ];
                        })->values()
                    ]);
                }
                $FinalscreeningData = $finalstepsData;

                // dd($FinalscreeningData);
                $almuni->final_screening = json_encode($FinalscreeningData);


                 // Training
                 $steps = Step::where('cycle_id', $company->cycledata->id)->get();
                 
                $startup_training = TrainingSession::whereIn('step_id', $steps->pluck('id'))
                    ->get();
                  
                $trainings = [];
                // dd($startup_training[3]->attendance);
                // dd($startup_training[3]->course->title);
                foreach ($startup_training as $training) {
                    $trainings[] = [
                        'ID' => $training->id,
                        'title' => $training->title,
                        'Trainer_course_ID' => $training->course->id ?? '',
                        'trainer_course_name' => $training->course->title ?? '',
                        'date' => $training->datetime->format('Y-m-d H:i') ?? '',
                        'duration' => $training->duration,
                        'step' => $training->step->title,
                        'step_ID' => $training->step->id,
                        'attendance' => $training->attendance->where('user_id', $company->user->id)->first() ? 'Attended' : 'Not Attended',
                    ];
                }

                $almuni->training = json_encode($trainings);
                    
                $steps = Step::where('cycle_id', $company->cycledata->id)->get();
                $startup_mentorship = MentorshipRequest::where('cycle_id', $company->cycledata->id)
                ->where('approved', 'Yes')
                ->where('user_id', $company->user->id)
                ->get();
                  
                $mentorships = [];
                // dd($startup_mentorship[3]);
                // dd($startup_training[3]->course->title);
                foreach ($startup_mentorship as $mentorship) {
                    $mentorships[] = [
                        'mentor_name' => $mentorship->mentor->first_name_en ." ". $mentorship->mentor->last_name_en,
                        'mentor_ID' => $mentorship->mentor->id ?? '',
                        // 'step_name' => $training->course->id ?? '',
                        // 'step_ID' => $training->course->title ?? '',
                        'date' => $mentorship->zoom_date->format('Y-m-d H:i') ?? '',
                        'duration' => $mentorship->duration,
                        'attended' => $mentorship->done == '1' ? 'Attended' : 'Not Attended',
                        'rating' => $mentorship->rating,
                        'comments' => $mentorship->rate_comment,
                    ];
                }

                $almuni->mentorship = json_encode($mentorships);
        

                
                $startup_milestones = Milestone::where('company_id', $company->id)
                    ->get();
                  
                $milestones = [];
                // dd($startup_mentorship[3]);
                // dd($startup_training[3]->course->title);
                foreach ($startup_milestones as $milestone) {
                    $milestones[] = [
                        'Milestone_title.' => $milestone->name,
                        'Description' => $milestone->description?? '',
                        'Due_date' => $milestone->created_at->format('Y-m-d H:i') ?? '',
                        'Completion_percentage' => $milestone->completion_percentage,
                        // 'Linked_deliverables' => $milestone->done == '1' ? 'Attended' : 'Not Attended',
                        'download' => $milestone->documents,
                    ];
                }
                // dd($milestones);
                $almuni->milestones = json_encode($milestones);


                // Mentor Tasks
                $startup_MentorTasks = MentorshipTask::where('company_id', $company->id)->get();
                $MentorTasks = [];
                // dd($startup_mentorship[3]);
                // dd($startup_training[3]->course->title);
                foreach ($startup_MentorTasks as $MentorTask) {
                    $MentorTasks[] = [
                        'Task_ID' => $MentorTask->id ?? '',
                        'Task_title' => $MentorTask->title ?? '',
                        'Session_ID' => $MentorTask->mentorshipRequest->id ?? '',
                        'Description' => $MentorTask->description ?? '',
                        'Due_date' => $MentorTask->due_date ?? '',
                        'Status' => $MentorTask->status ?? '',
                        'Completion_date' => $MentorTask->status == 'completed' ? $MentorTask->date : '',
                        'Assigned_mentor' => $MentorTask->mentor->first_name_en ." ". $MentorTask->mentor->last_name_en ?? '',
                        'Who_created_task' => $MentorTask->mentor->first_name_en ." ". $MentorTask->mentor->last_name_en ?? '',
                    ];
                }
                $almuni->tasks = json_encode($MentorTasks);

                $startup_quizzes= Quizzes::where('organization_id', $company->cycledata->organization_id)->get();
                $quizzes = [];
                // dd($startup_quizzes);
                // dd($startup_training[3]->course->title);
                foreach ($startup_quizzes as $quizze) {

                    $result = $quizze->results->first(); // ممكن يرجع null
                    $user   = $result?->user;            // برضه ممكن يكون null

                    $quizzes[] = [
                        'Quiz_ID' => $quizze->id ?? '',
                        'Quiz_title' => $quizze->title ?? '',
                        'Date_completed' => $result?->ended_at
                            ? $result->ended_at->format('Y-m-d H:i')
                            : '',
                        'founder_completed_quiz' => $user
                            ? ($user->first_name_en . " " . $user->last_name_en)
                            : '',
                        'Score' => $result?->score ?? '',
                        'Status' => $quizze->status ?? '',
                    ];
                }

                
                $almuni->quizzes = json_encode($quizzes);

                $startup_surveys= Surveys::where('organization_id', $company->cycledata->organization_id)->get();
                $surveys = [];
                foreach ($startup_surveys as $survey) {
                    $answers = $survey->responses
                        ->flatMap(function ($res) {

                            $decoded = json_decode($res->responses, true);

                            return collect($decoded)->map(function ($item) {
                                return [
                                    'question' => $item['question'] ?? '',
                                    'answer'   => $item['answer'] ?? '',
                                ];
                            });
                        })
                        ->filter()  
                        ->values();
                    $surveys[] = [
                        'Survey_ID' => $survey->id ?? '',
                        'Survey_title' => $survey->title ?? '',
                        'Survey_answers' => $answers ?? '',
                    ];
                }
                
                $almuni->surveys = json_encode($surveys);

                $startup_services= ServicesRequests::where('user_id', $company->user->id)->get();
                $services = [];
                
                foreach ($startup_services as $service) {
                    $services[] = [
                        'Service_name' => $service->service->name_en ?? '',
                        'Service_ID' => $service->service->id ?? '',
                        'Description' => $service->service->description_en ?? '',
                        'Price' => $service->service->price ?? '',
                    ];
                }
               
                $almuni->services = json_encode($services);

                // $startup_formSubmissions= Formdata::where('user_id', $company->user->id)->get();
                $formSubmissions = [];
                $user = $company->user;
                $l =  app()->getLocale();
                $forms = DB::table('form_data as FD')
                ->select([
                    'FD.company_id as FD_company_id',
                    'FD.user_id as FD_user_id',
                    'FD.step_id as FD_step_id',
                    'FD.question as FD_question',
                    'FD.answer as FD_answer',
                    'FD.created_at as FD_created_at',
                    'FD.updated_at as FD_updated_at',
                    'FD.deleted_at as FD_deleted_at',
                    'C.name_en as C_name_en',
                    'C.name_ar as C_name_ar',
                    'C.id as C_id',
                    DB::raw("CONCAT(U.first_name_en, ' ', U.last_name_en) as user_name_en"),
                    DB::raw("CONCAT(U.first_name_ar, ' ', U.last_name_ar) as user_name_ar"),
                    'U.id as U_id',
                    'S.title as S_title',
                    'S.id as S_id',
                    'S.cycle_id as S_cycle_id',
                    'CYC.title as CYC_title',
                    'CYC.id as CYC_id',
                    'CYC.organization_id as CYC_organization_id',
                    'O.name_en as O_name_en',
                    'O.name_ar as O_name_ar',
                    'O.id as O_id'
                ])
                ->leftJoin('companies as C', 'C.id', '=', 'FD.company_id')
                ->leftJoin('users as U', 'U.id', '=', 'FD.user_id')
                ->leftJoin('steps as S', 'S.id', '=', 'FD.step_id')
                ->leftJoin('cycles as CYC', 'CYC.id', '=', 'S.cycle_id')
                ->leftJoin('organizations as O', 'O.id', '=', 'CYC.organization_id')
                ->whereNull('FD.deleted_at')
                ->where('FD.user_id', $company->user->id)
                ->when($company->cycledata->organization_id != null, function ($query) use ($company) {
                    return $query->where('CYC.organization_id', $company->cycledata->organization_id);
                })
                ->get();

                $C = 1;
                $grouped = $forms->groupBy('FD_step_id');
                $data = $grouped->map(function ($group, $stepId) use ($l, &$C) {
                    $first = $group->first();

                    $item = new stdClass;
                    $item->submission_id = $C++;
                    $item->step_id = $stepId;
                    $item->startup_name = $first->{'C_name_' . $l};
                    $item->submitted_by = $group->pluck('user_name_' . $l)->unique()->implode(', ') ?? '';
                    $item->step_name = $first->S_title;
                    $item->program_name = $first->CYC_title;
                    $item->organization_name = $first->O_name_en;
                    $item->created_at = $first->FD_created_at;
                    $item->answers = $group->map(function ($item) use ($l) {
                        $value = $item->FD_answer;

                        if( is_string( $value ) && (strpos($value, '/') !== false || strpos($value, '\\') !== false) )
                        {
                            $filename = basename($value);
                            $value = [
                                'type' => 'file',
                                'path' => $value,
                                'file_name' => $filename,
                                'file_url' => asset($value),
                            ];
                        }

                        $submission = new stdClass;
                        $submission->user_id = $item->FD_user_id;
                        $submission->name = $item->{'user_name_' . $l};
                        $submission->label = trim( $item->FD_question );
                        $submission->value = $value;
                        $submission->created_at = $item->FD_created_at;
                        return $submission;
                    })->groupBy('user_id')->values();
                    return $item;
                })->values();
                        // dd($data);
                        
                        foreach ($data as $item) {

                            // كل answers عبارة عن Collection جوا Collection
                            $answers = $item->answers->collapse(); // ده بيفك الـ nesting

                            foreach ($answers as $answer) {

                                // تجهيز الفيلد
                                $fieldType = is_array($answer->value) ? 'file' : 'text';

                                $formSubmissions[] = [
                                    'Form_Title'      => $item->step_name,
                                    'ID'              => $item->submission_id,
                                    'step_title'      => $item->step_name,
                                    'Step_ID'         => $item->step_id,
                                    'Submission_Date' => $item->created_at,

                                    'Field_Name'      => $answer->label,
                                    'Field_Type'      => $fieldType,
                                    'Submitted_Value' => is_array($answer->value) ? $answer->value['file_name'] : $answer->value,
                                    'Attachment_Link' => is_array($answer->value) ? $answer->value['file_url'] : null,

                                    'Form_Inputs' => [
                                        'Field_Name'      => $answer->label,
                                        'Field_Type'      => $fieldType,
                                        'Submitted_Value' => is_array($answer->value) ? $answer->value['file_name'] : $answer->value,
                                        'Attachment_Link' => is_array($answer->value) ? $answer->value['file_url'] : null,
                                    ],
                                ];
                            }
                        }
             
                $almuni->submitted_forms = json_encode($formSubmissions);

            $almuni->save();
           
               $dattt = json_decode($almuni->screening);
               dd($dattt);
        } catch (Exception $e) {
            dd($e->getMessage());
            Log::error('Error deleting employee: ' . $e->getMessage());
            return response()->json([
                'errors' => ['general' => ['An error occurred while deleting the employee.']]
            ], 500);
        }

    }

}