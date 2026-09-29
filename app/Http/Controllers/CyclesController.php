<?php

namespace App\Http\Controllers;

use App\Enums\CycleModifyAction;
use App\Enums\CycleTabAction;
use App\Enums\StepType;
use App\Country;
use App\Sector;
use App\Stage;
use App\Almuni;
use App\Form;
use App\Step;
use App\User;
use URL, Str;
use App\Cycle;
use App\Company;
use App\Message;
use App\Formdata;
use App\Settings;
use App\FinalData;
use App\Formfield;
use App\Messenger;
use App\Screening;
use App\TeamFormation;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use App\BuilderForm;
use App\BuilderField;
use http\Env\Response;
use App\Finalscreening;
use App\TrainingSession;
use App\CycleCustomPages;
use App\MentorshipSchedule;
use Illuminate\Http\Request;
use App\Events\StartupJoining;
use Illuminate\Validation\Rule;
use App\Jobs\ExportCycleCompData;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use App\Mail\ProgramCreatedNotification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Notifications\StartupJoiningNotification;

class CyclesController extends Controller
{
    use \App\Traits\ValidatesUrlDomains;
    use \App\Traits\ManagesStepForm;

    protected $whatsAppService;
    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    public function exportcyclesComp(Request $request)
    {
        $user = auth()->user();
        $cycle = $request->cycle;

        if (!$user->hasRole('Admin')) {
            $orgIds = $user->organizations_ids;
            $cycleModel = \App\Cycle::find($cycle);
            if (!$cycleModel || !in_array($cycleModel->organization_id, $orgIds)) {
                abort(403, 'Unauthorized access to this cycle.');
            }
        }

        $companies = Company::where('cycle', $cycle)->get();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        // كتابة رؤوس الأعمدة
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Company');
        $sheet->setCellValue('C1', 'Title');
        $sheet->setCellValue('D1', 'User');
        $sheet->setCellValue('E1', 'Mobile');
        $sheet->setCellValue('F1', 'E-Mail');
        $sheet->setCellValue('G1', 'Current Step');
        $sheet->setCellValue('H1', 'Created_At');
        $row = 2;
        $stepsByCycle = \App\Step::whereIn('cycle_id', [$cycle])->orderBy('arr')->get()->groupBy('cycle_id');
        if(auth()->user()->hasRole('Admin')) {
            Company::with('user')->where('cycle', $cycle)->chunk(1000, function($companies) use (&$sheet, &$row, $stepsByCycle) {
                foreach ($companies as $companie) {
                    $sheet->setCellValue('A' . $row, $companie?->id);
                    $sheet->setCellValue('B' . $row, $companie?->name);
                    $sheet->setCellValue('C' . $row, $companie?->user?->title);
                    $sheet->setCellValue('D' . $row, $companie?->user?->name);
                    $sheet->setCellValue('E' . $row, $companie?->user?->phone);
                    $sheet->setCellValue('F' . $row, $companie?->user?->email);
                    foreach($stepsByCycle->get($companie->cycle, collect()) as $step){
                        $sheet->setCellValue('G' . $row, $step->title);
                    }
                    $sheet->setCellValue('H' . $row, $companie->created_at);
                    $row++;
                }
            });
        }else{
        Company::with('user')->where('cycle', $cycle)->chunk(1000, function($companies) use (&$sheet, &$row, $stepsByCycle) {
        foreach ($companies as $companie) {

            $sheet->setCellValue('A' . $row, $companie?->id);
            $sheet->setCellValue('B' . $row, $companie?->name);
            $sheet->setCellValue('C' . $row, $companie?->user?->title);
            $sheet->setCellValue('D' . $row, $companie?->user?->name);
            $sheet->setCellValue('E' . $row, $companie?->user?->phone);
            $sheet->setCellValue('F' . $row, $companie?->user?->email);
            foreach($stepsByCycle->get($companie->cycle, collect()) as $step){
                $sheet->setCellValue('G' . $row, $step->title);
            }
            $sheet->setCellValue('H' . $row, $companie->created_at);
            $row++;
        }
        });

        }
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Company_' . date('Y-m-d') . '.xlsx';
        $tempFilePath = storage_path('app/' . $fileName);
        $writer->save($tempFilePath);

        return response()->download($tempFilePath)->deleteFileAfterSend(true);
        // إرسال وظيفة (Job) لتحميل البيانات وإرسال البريد
        // ExportCycleCompData::dispatch($data2);
        // return response()->json(['message' => 'Export process started. You will receive an email with the download attached.']);
    }

    public function changAll(Request $request)
    {



        $this->validate($request, [

            'checkget' => 'required|integer',
            'compid' => 'required|array',
            'compid.*' => 'required|integer',

        ]);
        $checkget = $request->input('checkget');
        $compid = $request->input('compid');
        foreach ($compid as $index => $id) {
            $company = Company::where('id', $id)->first();
            if ($company) {
                $company->step = $checkget;
                $company->save();
            }
        }
        return response()->json(['state' => 'success'], 200);
    }
    public function index($id = 0)
    {
        $user = auth()->user();
        $orgIds = $user->organizations_ids;

        $cycles = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
            return $q->whereIn('organization_id', $orgIds);
        })->with(['organization'])->orderBy('start', 'DESC')->get();

        $steps = null;
        if( count( $cycles ) > 0 )
        {
            if ($id == 0) { $id = $cycles->pluck('id')[0]; }
            $steps = Step::where('cycle_id', $id)->orderBy('arr')->get();
        }
        $svgs = ['notebook', 'search', 'idea', 'comments', 'trophy'];
        if($steps == null || count($steps) == 0)
        {
            $steps = collect([]);
        }
        $steps = $steps->map(function ( $step ) use ( $cycles ) {
            $step->join_url = null;
            if( $step->stype != 1 ) { return $step; }
            else
            {
                if( $step->is_public == 0 ) { return $step; }
                else
                {
                    $slug = $step->public_slug;
                    if( !empty( $slug ) && trim( $slug ) )
                    {
                        $cycle_id = $step->cycle_id;
                        $getCycle = $cycles->where('id', $cycle_id)->first();
                        if( !empty( $getCycle ) )
                        {
                            $organization = $getCycle->organization;
                            if( !empty( $organization ) )
                            {
                                $short_code = $organization->short_code;
                                $step->join_url = URL::to("/$short_code/join/$slug");
                            }
                        }
                    } else { return $step; }
                }
            }
            return $step;
        });

        return view('admin.settings.cycles.index', compact('cycles', 'steps', 'id', 'svgs'));
    }

    public function users(Request $request, $id)
    {
        $user = auth()->user();
        $orgIds = $user->organizations_ids;

        if ($id == 0) {
            $cycles = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                return $q->whereIn('organization_id', $orgIds);
            })->with(['organization'])->orderBy('id', 'DESC')->get();
            if (!empty($cycles->toArray())) {
                $id = $cycles->pluck('id')[0];
            }
        } else {
            $cycles = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                return $q->whereIn('organization_id', $orgIds);
            })->with(['organization'])->get();
        }
        $alumniCompanyIds = Almuni::where('cycle_id', $id)->pluck('company_id');
        $companies = Company::where('cycle', $id);
        $alumniCompanies = Company::whereIn('id', $alumniCompanyIds)->get();
        $uniqueCycleIds = $companies->pluck('cycle')->unique();
        $steps = Step::whereIn('cycle_id', $uniqueCycleIds)->orderBy('arr')->get();
        if ($request->step_id != 0) {
            $companies = $companies->where('step', $request->step_id);
        }
        if ($request->country_id != 0) {
            $companies = $companies->where('country_id', $request->country_id);
        }
        if ($request->sector_id != 0) {
            $companies = $companies->where('sector', $request->sector_id);
        }
        if ($request->Dstage_id != 0) {
            $companies = $companies->where('stage', $request->Dstage_id);
        }
        if ($request->funding_stage != 0) {
            $companies = $companies->where('development_stage', $request->funding_stage);
        }
        if ($request->targeted_market != 0) {
            $companies = $companies->where(function ($query) use ($request) {
                foreach ($request->targeted_market as $market) {
                    $query->orWhereJsonContains('targeted_market', $market);
                }
            });
        }
        if ($request->BType != 0) {
            $companies = $companies->where('business_type', $request->BType);
        }

        $l = app()->getLocale();
        $companies = $companies->with(['user', 'cycleData'])->get();
        $companies = $companies->merge($alumniCompanies);
        $getSteps = Step::where('stype', '!=', '7')->get();
        $companies = $companies->map(function ($company) use ($alumniCompanyIds, $l, $getSteps) {
            $company->isAlumni = $alumniCompanyIds->contains($company->id);
            $finalScreeningSteps = $getSteps->where('cycle_id', $company->cycle)
                            ->where('stype', '=', '5');
            $goAlumni = $finalScreeningSteps->sortByDesc('arr')
                            ->first();

            $currentStepTitle = $getSteps->where('cycle_id', $company->cycle)
                            ->sortBy('arr')
                            ->where('id', $company->step)
                            ->pluck('title')
                            ->implode(', ');

            $company->goAlumni         = $goAlumni;
            $company->atAlumniStep     = $finalScreeningSteps->pluck('id')->contains($company->step);
            $company->currentStepTitle = $currentStepTitle;
            $company->userName         = trim(($company->user->{'first_name_'.$l} ?? '').' '.($company->user->{'last_name_'.$l} ?? ''));
            $company->companyName      = $company->{'name_'.$l};

            return $company;
        });
        $countries = Country::all();
        $sectors = Sector::all();
        $stages = Stage::all();

        $com          = $companies;
        $cycleId      = request()->route('id');
        $currentCycle = $cycles->firstWhere('id', $id);
        $cycleTitle   = $cycles->where('id', request('id'))->pluck('title')->implode(', ');
        $fundingStages = [
            'Pre_seed_funding_stage' => __('admin.Pre_seed_funding_stage'),
            'Seed_funding_stage'     => __('admin.Seed_funding_stage'),
            'Series_A_funding'       => __('admin.Series_A_funding'),
            'Series_B_funding'       => __('admin.Series_B_funding'),
            'Series_C_funding'       => __('admin.Series_C_funding'),
        ];
        $bTypes = ['B2C', 'B2B', 'B2B2C', 'C2C', 'B2G', 'Platform'];
        return view('admin.settings.cycles.users', compact(
            'cycles', 'id', 'companies', 'steps',
            'countries', 'sectors', 'stages',
            'com', 'cycleId', 'currentCycle', 'cycleTitle',
            'fundingStages', 'bTypes'
        ));
    }

    public function modify(Request $request)
    {
        if ($request->has('start_color')) {
            $request->merge(['start_color' => preg_replace('/^#+/', '#', trim($request->start_color))]);
        }
        if ($request->has('end_color')) {
            $request->merge(['end_color' => preg_replace('/^#+/', '#', trim($request->end_color))]);
        }
        if ($request->has('main_color')) {
            $request->merge(['main_color' => preg_replace('/^#+/', '#', trim($request->main_color))]);
        }

        $isEdit = false;
        $isCreate = false;
        if( $request->has('typeedit') && $request->has('id') && intval( $request->id ) > 0 ) { $isEdit = true; }
        if( $request->has('typecreate') && !$request->has('id') ) { $isCreate = true; }

        if ($request->cycleid == null && isset($request->typeedit)) {
            $id = $request->id;
            $type = $request->typeedit;
        } else {
            $id = $request->cycleid;
            $type = $request->type;
        }

        if( $isEdit == true ) { $id = intval( $request->id ); }
        if ($request->has('date_range')) {

            $dates = explode(' - ', $request->date_range);
            if( count( $dates ) < 2 ) { return redirect()->back()->with(['message2' => 'error_date_range', 'class' => 'danger']); }

            try {
                $startDate = Carbon::createFromFormat('m/d/Y', trim($dates[0]))->format('Y-m-d'); // التاريخ الأول
                $endDate   = Carbon::createFromFormat('m/d/Y', trim($dates[1]))->format('Y-m-d'); // التاريخ الثاني
            } catch (InvalidFormatException $e) {
                return redirect()->back()->with(['message2' => 'error_date_range', 'class' => 'danger']);
            }

            $request->merge([
                'start' => $startDate,
                'end' => $endDate,
                'fromDate' => $startDate,
                'toDate' => $endDate,
            ]);
        }

        $title_max_length = Cycle::TITLE_MAX_LENGTH;
        if ($type == CycleModifyAction::EDIT_CYCLE || $isEdit == true)
        {
            /// Edit Cycle (to be moved to standalone controller)
            $isAr = app()->getLocale() == 'ar';
            $request->validate([
                'organization_id2' => 'required|exists:organizations,id',
                'title' => ['required', 'string', 'max:' . $title_max_length, Rule::unique('cycles', 'title')->where('organization_id', $request->organization_id2)->ignore($id)],
                'title_ar' => ['required', 'string', 'max:' . $title_max_length],
                'description' => 'required|string|max:250',
                'description_ar' => 'required|string|max:250',
                'is_public' => 'required|boolean',
                // 'public_slug' => 'required_if:is_public,1|unique:cycles,public_slug,' . $id . ',id|regex:/^[a-zA-Z0-9-]+$/',
                'public_slug' => [
                    Rule::requiredIf($request->is_public == 1),
                    'nullable',
                    'regex:/^[a-zA-Z0-9_-]+$/',
                    Rule::unique('cycles', 'public_slug')->ignore($request->id),
                ],
                'start' => 'required|date',
                'end' => 'required|date',
                'program-guideline-pdf' => 'nullable|file|mimes:pdf|max:2048',
                'financial_policy_pdf' => 'nullable|file|mimes:pdf|max:2048',
                'legal_policy_pdf' => 'nullable|file|mimes:pdf|max:2048',
                'delegation_pdf' => 'nullable|file|mimes:pdf|max:2048',
                'program_step_type' => ['required', 'in:cycle,time'],
                'cohort' => 'required|string|max:250',
                'offline_location_address' => 'nullable|string|max:250',
                'offline_location_map' => 'nullable|string|max:500',
                'start_color' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'],
                'end_color' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'],
                'main_color' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'],
            ], [
                'public_slug.required_if' => __('cycles.Public URL required'),
                'public_slug.unique' => __('cycles.Public URL already exist'),
                'public_slug.regex' => __('cycles.Public URL must be alphanumeric'),
                'program_step_type.required' => __('cycles.please_select_program_step_type'),
                'title_ar.required' => $isAr ? 'عنوان البرنامج باللغة العربية مطلوب.' : 'Program Title in Arabic is required.',
                'description_ar.required' => $isAr ? 'وصف البرنامج باللغة العربية مطلوب.' : 'Description in Arabic is required.',
                'start_color.required' => $isAr ? 'يرجى إدخال لون صالح.' : 'Please enter a valid start color.',
                'start_color.regex' => $isAr ? 'يرجى إدخال لون صالح.' : 'Please enter a valid start color.',
                'end_color.required' => $isAr ? 'يرجى إدخال لون صالح.' : 'Please enter a valid end color.',
                'end_color.regex' => $isAr ? 'يرجى إدخال لون صالح.' : 'Please enter a valid end color.',
                'main_color.required' => $isAr ? 'يرجى إدخال لون صالح.' : 'Please enter a valid button color.',
                'main_color.regex' => $isAr ? 'يرجى إدخال لون صالح.' : 'Please enter a valid button color.',
            ]);
                
            $cycle = Cycle::find($id);

            $organization_id = $cycle->organization_id;
            $cycles = Cycle::where('organization_id', $organization_id)->get();
            $slugs = $cycles->pluck('public_slug')->unique()->filter(function( $value ) { return !empty( $value ); })->values()->toArray();

            $private = null;
            if ($request->private) {
                $private = sha1(now());
            }

            // Check if file is uploaded
            $cycle->is_public = $request->has('is_public') ? ( intval( $request->is_public ) == 1 ? 1 : 0 ) : 0;
            if( $cycle->is_public == 1 && ( empty( $request->public_slug ) || !trim( $request->public_slug ) ) )
            {
                return redirect()->back()->with(['message2' => __('cycles.Public URL required'), 'class' => 'danger']);
            }
            if( $request->public_slug != $cycle->public_slug )
            {
                $counter = 0;
                do {
                    if( $counter == 0 )
                    {
                        $public_slug = url_slugify( $request->public_slug );
                        $counter++;
                    }
                    else
                    {
                        $public_slug = url_slugify( $request->public_slug ) . '-' . $counter++;
                    }
                    $check_slug = in_array( $public_slug, $slugs );
                } while ( $check_slug );
            } else { $public_slug = $cycle->public_slug; }

            $cycle->public_slug = $public_slug;
            $cycle->organization_id = $request->organization_id2;
            $cycle->title = $request->title;
            $cycle->title_ar = $request->title_ar;
            $cycle->description = $request->description;
            $cycle->description_ar = $request->description_ar;
            $cycle->start = Carbon::parse($request->start);
            $cycle->end = Carbon::parse($request->end);
            $cycle->private = $private;
            $cycle->cohort = $request->cohort;
            $cycle->allow_non_company = $request->has('allow_non_company') ? 1 : 0;
            $cycle->step_type = $request->program_step_type;
            $cycle->offline_location_address = $request->offline_location_address;
            $cycle->offline_location_map = $request->offline_location_map;
            $cycle->start_color = $request->start_color;
            $cycle->end_color = $request->end_color;
            $cycle->main_color = $request->main_color;


            $fileManager = app(\App\Services\FileManagerService::class);
            $cycle->program_guideline = $request->program_guideline;
            if ($request->hasFile('program-guideline-pdf')) {
                $cycle->program_guideline_file = $fileManager->update($request->file('program-guideline-pdf'), $cycle->program_guideline_file, 'programs', 'public_uploads');
            }
            $cycle->budget_allocation = $request->budget_allocation;
            $cycle->currency_type = $request->currency_type;
            $cycle->financial_guideline = $request->financial_policy;
            if ($request->hasFile('financial_policy_pdf')) {
                $cycle->financial_guideline_file = $fileManager->update($request->file('financial_policy_pdf'), $cycle->financial_guideline_file, 'programs', 'public_uploads');
            }
            $cycle->legal_guideline = $request->legal_policy;
            if ($request->hasFile('legal_policy_pdf')) {
                $cycle->legal_guideline_file = $fileManager->update($request->file('legal_policy_pdf'), $cycle->legal_guideline_file, 'programs', 'public_uploads');
            }
            if ($request->delegation_option == 'yes' && $request->hasFile('delegation_pdf')) {
                $cycle->delegation_file = $fileManager->update($request->file('delegation_pdf'), $cycle->delegation_file, 'programs', 'public_uploads');
            }
            $cycle->auto_approve_mentorship_requests = $request->has('auto_approve_mentorship_requests') ? 1 : 0;

            if ($request->hasFile('img_url')) {
                $image = $request->file('img_url');
                $cycle->img_url = app(\App\Services\FileManagerService::class)->update($image, $cycle->img_url, 'programs', 'public_uploads');
            }
                    $cycle->is_active_Rminder = $request->reminder_enabled == 'on' ? 1 : 0;
                    // $cycle->hours_Rminder = $request->reminder_enabled == 'on' ? $request->hours_reminder : 0;

            $cycle->save();

            if($request->reminder_enabled == 'on'){
                $schedule = DB::table('mentorship_schedules')->where('cycle_id', $cycle->id)->delete();
                    foreach ($request->reminder_times as $reminder) {
                        [$value, $unit, $before] = explode('_', $reminder);
                        DB::table('mentorship_schedules')->insert([
                            'cycle_id' => $cycle->id,
                            'value' => (int) $value,
                            'unit' => $unit,
                            'is_active' => 1,
                            'organization_id' => $cycle->organization_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
            }else{
                DB::table('mentorship_schedules')->where('cycle_id', $cycle->id)->delete();
            }


           
            return redirect()->back()->with('message', 'Saved');
        }
        elseif ($type == CycleModifyAction::SAVE_CYCLE_NOOP) {

            /// Save Cycle



            return redirect()->back()->with('message', 'Saved');
        } elseif ($type == CycleModifyAction::DELETE_CYCLE) {
            /// Delete Cycle steps contents
            $delsteps = Step::where('cycle_id', $id)->get();
            foreach ($delsteps as $onestep) {
                Formfield::where('step_id', $onestep->id)->delete();
                Screening::where('step_id', $onestep->id)->delete();
                Formdata::where('form_id', $onestep->id)->delete();
                Finalscreening::where('step_id', $onestep->id)->delete();
                FinalData::where('step_id', $onestep->id)->delete();
                TrainingSession::where('step_id', $onestep->id)->delete();
            }
            /// Delete Step
            Step::where('cycle_id', $id)->delete();
            /// Delete Cycle
            Cycle::destroy($id);
            return Response()->json('Cycle deleted');
        } elseif ($type == CycleModifyAction::ARRANGE_STEPS) {
            /// arrange steps
            $i = 1;
            foreach ($request->steps as $id) {
                Step::where('id', $id)->update(['arr' => $i++]);
            }
            return Response()->json('Arranged ');
        } elseif ($type == CycleModifyAction::ADD_STEP) {
            if (is_null($request->fromDate) || is_null($request->toDate)) {
                return redirect()->back()->with(['message2' => 'add start and end date first', 'class' => 'danger']);
            }
            /// add new step
            $stype = $request->stype;
            
            $data = null;
           
            if ($stype == StepType::CUSTOM_FORM) {
                $data = $request->FormSelect;
                $id = Step::insertGetId([
                    'cycle_id' => $request->cid,
                    'title' => $request->title,
                    'description' => $request->description,
                    'stype' => $stype,
                    'data' => $data,
                    'form_id' => $data,
                    'arr' => $request->arr,
                    'from' => $request->fromDate,
                    'to' => $request->toDate
                ]);
                // make a copy of form fields
               
                $new = BuilderField::where('builder_form_id', $data)->orderBy('id')->get();
                
                if (count($new) > 0) {
                    foreach ($new as $n) {
                      
                        Formfield::insert([
                            'step_id' => $id,
                            'field_id' => $n->field_id,
                            'options' => json_encode($n->options),
                            'builder_forms_id' => $n->builder_form_id
                        ]);
                    }
                }
            }elseif ($stype == StepType::SCREENING) {
                if (is_null($request->fromDate) || is_null($request->toDate)) {
                    return redirect()->back()->with(['message2' => 'add start and end date first', 'class' => 'danger']);
                }
                // add Screening
                Step::insert([
                    'cycle_id' => $request->cid,
                    'title' => $request->title,
                    'description' => $request->description,
                    'stype' => $stype,
                    'data' => $request->ScreeningSelect,
                    'arr' => $request->arr,
                    'from' => $request->fromDate,
                    'to' => $request->toDate
                ]);
            }elseif ($stype == StepType::MENTORSHIP) {
                if (is_null($request->fromDate) || is_null($request->toDate)) {
                    return redirect()->back()->with(['message2' => 'add start and end date first', 'class' => 'danger']);
                }
                // add step
                Step::insert([
                    'cycle_id' => $request->cid,
                    'title' => $request->title,
                    'description' => $request->description,
                    'stype' => $stype,
                    'data' => null,
                    'arr' => $request->arr,
                    'from' => $request->fromDate,
                    'to' => $request->toDate
                ]);
            }elseif ($stype == StepType::TRAINING) {
                if (is_null($request->fromDate) || is_null($request->toDate)) {
                    return redirect()->back()->with(['message2' => 'add start and end date first', 'class' => 'danger']);
                }
                // add training
                Step::insert([
                    'cycle_id' => $request->cid,
                    'title' => $request->title,
                    'description' => $request->description,
                    'stype' => $stype,
                    'list' => $request->RecordedSelect,
                    'data' => null,
                    'arr' => $request->arr,
                    'from' => $request->fromDate,
                    'to' => $request->toDate
                ]);
            }elseif ($stype == StepType::FINAL_EVALUATION) {
                if (is_null($request->fromDate) || is_null($request->toDate)) {
                    return redirect()->back()->with(['message2' => 'add start and end date first', 'class' => 'danger']);
                }
                // add service
                if($request->FormSelect != null){
                $data = $request->FormSelect;
                }else{
                $data = $request->FinalScreeningSelect;
                }
                $fform = $request->FormSelect;
                if (!$fform) {
                    return redirect()->back()->with('message2', 'You must add form first');
                }
                $id = Step::insertGetId([
                    'cycle_id' => $request->cid,
                    'title' => $request->title,
                    'description' => $request->description,
                    'stype' => $stype,
                    'data' => $data,
                    'form_id' => $fform,
                    'arr' => $request->arr,
                    'from' => $request->fromDate,
                    'to' => $request->toDate
                ]);
                // make a copy of form fields
                $new = BuilderField::where('builder_form_id', $fform)->orderBy('id')->get();
                if (count($new) > 0) {
                    foreach ($new as $n) {
                        Formfield::insert([
                            'step_id' => $id,
                            'field_id' => $n->field_id,
                            'options' => json_encode($n->options),
                            'builder_forms_id' => $n->builder_form_id
                        ]);
                    }
                }
            }elseif ($stype == StepType::TEAM_FORMATION) {
               $validator = Validator::make($request->all(), [
                    'title' => 'required|string|max:100',
                    'description' => 'required|string|max:100',
                    'count_team_number' => 'required|numeric|gt:0',
                    ]);
                if ($validator->fails()) {
                    return redirect()->back()
                    ->withInput()
                    ->with([
                    'message2' => $validator->errors()->first(),
                    'class' => 'danger'
                    ]);
                }
              if (is_null($request->fromDate) || is_null($request->toDate)) {
                   return redirect()->back()->with(['message2' => __('admin.AddDateFirst'), 'class' => 'danger']);
               }
                $cycle = \App\Cycle::find($request->cid);
                if (!$cycle || $cycle->type !== 'Hackathon') {
                return redirect()->back()->with(['message2' => __('admin.HackathonAllowed'), 'class' => 'danger']);
                }
                Step::insert([
                'cycle_id' => $request->cid,
                'title' => $request->title,
                'description' => $request->description,
                'stype' => $stype,
                'data' => null,
                'arr' => $request->arr,
                'from' => $request->fromDate,
                'to' => $request->toDate,
                'count_team_number' => $request->count_team_number ??0,
                ]);
            }elseif ($stype == StepType::GENERIC) {
                
                $validator = Validator::make($request->all(), [
                    'title' => 'required|string|max:100',
                    'description' => 'required|string|max:100',
                    ]);
                if ($validator->fails()) {
                    return redirect()->back()
                    ->withInput()
                    ->with([
                    'message2' => $validator->errors()->first(),
                    'class' => 'danger'
                    ]);
                }
              if (is_null($request->fromDate) || is_null($request->toDate)) {
                   return redirect()->back()->with(['message2' => __('admin.AddDateFirst'), 'class' => 'danger']);
               }
                
                Step::insert([
                'cycle_id' => $request->cid,
                'title' => $request->title,
                'description' => $request->description,
                'stype' => $stype,
                'data' => null,
                'arr' => $request->arr,
                'from' => $request->fromDate,
                'to' => $request->toDate,
                ]);
            }
            return redirect()->back()->with('message', 'Saved');
        } elseif ($type == CycleModifyAction::DELETE_STEP) {
            /// Delete step
            $cid = $request->stepid;
            if ($request->steps) {
                $steps = $request->steps;
            } else {
                $steps = Step::where('cycle_id', Step::where('id', $cid)->pluck('cycle_id')->first())->pluck('id')->toArray();
            }

            //// Delete step contents
            Formfield::where('step_id', $cid)->delete();
            Screening::where('step_id', $cid)->delete();
            Formdata::where('form_id', $cid)->delete();
            Finalscreening::where('step_id', $cid)->delete();
            FinalData::where('step_id', $cid)->delete();
            TrainingSession::where('step_id', $cid)->delete();
            Step::destroy($cid);
            // Arrange steps
            $i = 1;
            $steps = array_diff($steps, [$cid]);
            foreach ($steps as $id) {
                Step::where('id', $id)->update(['arr' => $i++]);
            }
            return Response()->json('Step deleted');
        } elseif ($type == CycleModifyAction::FETCH_STEP) {
            // Modify step
            $step = Step::where('id', $request->sid)->first();
            $minimumMembers = 1; // Default minimum members
            if( !empty( $step ) )
            {
                if( $step->stype == StepType::TEAM_FORMATION )
                {
                    $minimumMembers = TeamFormation::maxAcceptedMembers($step->id);
                    $step->minimumMembers = $minimumMembers;
                }
            }
            $step = $step->toArray();
            return response()->json($step);
        } elseif ($type == CycleModifyAction::UPDATE_STEP) {
            /// deleted saved data if type changed
            if( $request->stype == StepType::TEAM_FORMATION )
            {
                $numberOfMembers = TeamFormation::maxAcceptedMembers($request->sid);
                if( $request->count_team_number < $numberOfMembers )
                {
                    return redirect()->back()->with(['failure' => __('cycles.TeamFormationMinMembers', ['min' => $numberOfMembers]), 'class' => 'danger']);
                }
            }
            if ($request->ScreeningSelect !== null && Step::where('id', $request->sid)->pluck('data')->first() != $request->ScreeningSelect) {
                Formdata::where('step_id', $request->sid)->update(['screening' => null, 'comments' => null]);
            }
            if($request->count_team_number !== null){
                Step::where('id', $request->sid)->update(['count_team_number' => $request->count_team_number]);
            }
            /// Save Modified step
            if ($request->ScreeningSelect) {
                Step::where('id', $request->sid)->update(['title' => $request->title, 'description' => $request->description, 'data' => $request->ScreeningSelect, 'list' => null, 'from' => Carbon::parse($request->fromDate), 'to' => Carbon::parse($request->toDate)]);
            } elseif ($request->ServiceSelect) {
                Step::where('id', $request->sid)->update(['title' => $request->title, 'description' => $request->description, 'data' => $request->ServiceSelect, 'list' => null, 'from' => Carbon::parse($request->fromDate), 'to' => Carbon::parse($request->toDate)]);
            } elseif ($request->TrainingSelect) {
                Step::where('id', $request->sid)->update(['title' => $request->title, 'description' => $request->description, 'data' => $request->TrainingSelect, 'list' => $request->RecordedSelect, 'from' => Carbon::parse($request->fromDate), 'to' => Carbon::parse($request->toDate)]);
            } elseif ($request->FinalScreeningSelect) {
                Step::where('id', $request->sid)->update(['title' => $request->title, 'description' => $request->description, 'data' => $request->FinalScreeningSelect, 'list' => null, 'from' => Carbon::parse($request->fromDate), 'to' => Carbon::parse($request->toDate)]);
            } elseif ($request->has("mScreeningSelect") && $request->mScreeningSelect) {
                Step::where('id', $request->sid)->update(['title' => $request->title, 'data' => $request->mScreeningSelect, 'description' => $request->description, 'from' => Carbon::parse($request->fromDate), 'to' => Carbon::parse($request->toDate)]);
            }
            elseif ($request->has("mFormSelect") || !$request->has("mFormSelect"))
            {
                $step8 = Step::find($request->sid);
                $updatedArray = [
                    'title' => $request->title,
                    'data' => $request->input('mFormSelect', null),
                    'description' => $request->description,
                    'from' => Carbon::parse($request->fromDate),
                    'to' => Carbon::parse($request->toDate)
                ];
                if( $step8->stype == StepType::CUSTOM_FORM )
                {
                    $counter = 0;
                    $organization_id = $step8->cycle->organization_id;
                    $cycles_ids = Cycle::where('organization_id', $organization_id)->pluck('id')->toArray();
                    $slugs = Step::whereIn('cycle_id', $cycles_ids)->where('stype', StepType::CUSTOM_FORM)->pluck('public_slug')->unique()->filter(function($value) { return !empty($value); })->values()->toArray();

                    $updatedArray['is_public'] = $request->is_public;
                    if( $updatedArray['is_public'] == 1 && ( empty( $request->public_slug ) || !trim( $request->public_slug ) ) )
                    {
                        return redirect()->back()->with(['message2' => __('cycles.Public URL required'), 'class' => 'danger']);
                    }
                    if( $request->public_slug != $step8->public_slug )
                    {
                        do {
                            if( $counter == 0 )
                            {
                                $public_slug = url_slugify( $request->public_slug );
                                $counter++;
                            }
                            else
                            {
                                $public_slug = url_slugify( $request->public_slug ) . '-' . $counter++;
                            }
                            $check_file = in_array( $public_slug, $slugs );
                        } while ( $check_file );
                    }
					$updatedArray['public_slug'] = url_slugify( $request->public_slug );
                }
                // Update non-form metadata (title/description/dates and, for
                // stype 1, the public_slug computed above), but NOT data/form_id —
                // those plus the field snapshot are handled by applyFormToStep so
                // every entry point behaves identically (sync + warn + re-snapshot).
                unset($updatedArray['data']);
                Step::where('id', $request->sid)->update($updatedArray);

                $result = $this->applyFormToStep($step8->fresh(), $request->input('mFormSelect', null), (bool) $request->boolean('confirm_form_change'));
                if ($result['status'] === 'needs_confirmation') {
                    // The flash partials render __('admin.' . $message2), so pass the
                    // bare key (not the already-translated string) — otherwise the
                    // banner shows the literal "admin.This step already has...".
                    // This is only a fallback; the modal JS warns before submit.
                    return redirect()->back()
                        ->with('message2', 'step_form_change_has_submissions');
                }
            } else {
                
                Step::where('id', $request->sid)->update(['title' => $request->title, 'count_team_number' => $request->count_team_number, 'description' => $request->description, 'from' => Carbon::parse($request->fromDate), 'to' => Carbon::parse($request->toDate)]);
            }
            return redirect()->back()->with('message', 'Saved');
        }
        elseif ($request->tab_id == CycleTabAction::CREATE_CYCLE)
        {
            // create new cycle (to be moved to standalone controller)
            $isAr = app()->getLocale() == 'ar';
            $request->validate([
                'organization_id2' => 'required|exists:organizations,id',
                'title' => 'required|string|max:' . $title_max_length,
                'title_ar' => 'required|string|max:' . $title_max_length,
                'description' => 'required|string|max:100',
                'description_ar' => 'required|string|max:250',
                'is_public' => 'required|boolean',
                'public_slug' => 'nullable|required_if:is_public,1|unique:cycles,public_slug,' . $request->id . ',id|regex:/^[a-zA-Z0-9_-]+$/',
                'start' => 'required|date|before:end',
                'end' => 'required|date|after:start',
                'program-guideline-pdf' => 'nullable|file|mimes:pdf|max:2048',
                'financial_policy_pdf' => 'nullable|file|mimes:pdf|max:2048',
                'legal_policy_pdf' => 'nullable|file|mimes:pdf|max:2048',
                'delegation_pdf' => 'nullable|file|mimes:pdf|max:2048',
                'program_type' => ['required', 'in:General_Program,Hackathon'],
                'program_step_type' => ['required', 'in:cycle,time'],
                'offline_location_address' => 'nullable|string|max:250',
                'offline_location_map' => 'nullable|string|max:500',
                'start_color' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'],
                'end_color' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'],
                'main_color' => ['required', 'string', 'regex:/^#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/'],
            ], [
                'public_slug.required_if' => __('cycles.Public URL required'),
                'public_slug.unique' => __('cycles.Public URL already exist'),
                'public_slug.regex' => __('cycles.Public URL must be alphanumeric'),
                'program_step_type.required' => __('cycles.please_select_program_step_type'),
                'offline_location_address.max' => __('cycles.offline_location_address_max'),
                'offline_location_map.max' => __('cycles.offline_location_map_max'),
                'title_ar.required' => $isAr ? 'عنوان البرنامج باللغة العربية مطلوب.' : 'Program Title in Arabic is required.',
                'description_ar.required' => $isAr ? 'وصف البرنامج باللغة العربية مطلوب.' : 'Description in Arabic is required.',
                'start_color.required' => $isAr ? 'يرجى إدخال لون صالح.' : 'Please enter a valid start color.',
                'start_color.regex' => $isAr ? 'يرجى إدخال لون صالح.' : 'Please enter a valid start color.',
                'end_color.required' => $isAr ? 'يرجى إدخال لون صالح.' : 'Please enter a valid end color.',
                'end_color.regex' => $isAr ? 'يرجى إدخال لون صالح.' : 'Please enter a valid end color.',
                'main_color.required' => $isAr ? 'يرجى إدخال لون صالح.' : 'Please enter a valid button color.',
                'main_color.regex' => $isAr ? 'يرجى إدخال لون صالح.' : 'Please enter a valid button color.',
            ]);
            
            $cycles = Cycle::where('organization_id', $request->organization_id2)->get();
            $slugs = $cycles->pluck('public_slug')->unique()->filter(function( $value ) { return !empty( $value ); })->values()->toArray();

            $existTitle = Cycle::where('organization_id', $request->organization_id2)->where('title', $request->title)->first();
                if ($existTitle) {
                    return redirect()->back()->with('message2', 'Cycle_title_already_exist');
                }

            $cycle = null;
            DB::transaction(function () use ($request, $slugs, &$cycle) {
                $private = null;
                if ($request->private) {
                    $private = sha1(now());
                }
                $cycle = new Cycle();

                $cycle->is_public = $request->has('is_public') ? ( intval( $request->is_public ) == 1 ? 1 : 0 ) : 0;
                if( $cycle->is_public == 1 && ( empty( $request->public_slug ) || !trim( $request->public_slug ) ) )
                {
                    return redirect()->back()->with(['message2' => __('cycles.Public URL required'), 'class' => 'danger']);
                }
                if( $request->public_slug != $cycle->public_slug )
                {
                    $counter = 0;
                    do {
                        if( $counter == 0 )
                        {
                            $public_slug = url_slugify( $request->public_slug );
                            $counter++;
                        }
                        else
                        {
                            $public_slug = url_slugify( $request->public_slug ) . '-' . $counter++;
                        }
                        $check_slug = in_array( $public_slug, $slugs );
                    } while ( $check_slug );
                } else { $public_slug = $cycle->public_slug; }

                $cycle->public_slug = $public_slug;
                $cycle->step_type = $request->program_step_type;
                $cycle->organization_id = $request->organization_id2;
                $cycle->type = $request->program_type;
                $cycle->title = $request->title;
                $cycle->title_ar = $request->title_ar;
                $cycle->description = $request->description;
                $cycle->description_ar = $request->description_ar;
                $cycle->start = Carbon::parse($request->start);
                $cycle->end = Carbon::parse($request->end);
                $cycle->private = $private;
                $cycle->cohort = $request->cohort;
                $cycle->allow_non_company = $request->has('allow_non_company') ? 1 : 0;
                $cycle->auto_approve_mentorship_requests = $request->has('auto_approve_mentorship_requests') ? 1 : 0;
                $cycle->offline_location_address = $request->offline_location_address;
                $cycle->offline_location_map = $request->offline_location_map;
                $cycle->start_color = $request->start_color;
                $cycle->end_color = $request->end_color;
                $cycle->main_color = $request->main_color;
                // Check if file is uploaded

                $fileManager = app(\App\Services\FileManagerService::class);
                $cycle->program_guideline = $request->program_guideline;
                if ($request->hasFile('program-guideline-pdf')) {
                    $cycle->program_guideline_file = $fileManager->upload($request->file('program-guideline-pdf'), 'programs', 'public_uploads');
                }


                $cycle->budget_allocation = $request->budget_allocation;
                $cycle->currency_type = $request->currency_type;
                $cycle->financial_guideline = $request->financial_policy;
                if ($request->hasFile('financial_policy_pdf')) {
                    $cycle->financial_guideline_file = $fileManager->upload($request->file('financial_policy_pdf'), 'programs', 'public_uploads');
                }

                $cycle->legal_guideline = $request->legal_policy;
                if ($request->hasFile('legal_policy_pdf')) {
                    $cycle->legal_guideline_file = $fileManager->upload($request->file('legal_policy_pdf'), 'programs', 'public_uploads');
                }

                if ($request->delegation_option == 'yes' && $request->hasFile('delegation_pdf')) {
                    $cycle->delegation_file = $fileManager->upload($request->file('delegation_pdf'), 'programs', 'public_uploads');
                }
                if ($request->hasFile('img_url')) {
                    $image = $request->file('img_url');
                    $cycle->img_url = $fileManager->upload($image, 'programs', 'public_uploads');
                }
                $cycle->is_active_Rminder = $request->reminder_enabled == 'on' ? 1 : 0;
                // $cycle->hours_Rminder = $request->reminder_enabled == 'on' ? $request->hours_reminder : 0;
                $cycle->save();
                if($request->reminder_enabled == 'on'){
                    foreach ($request->reminder_times as $reminder) {
                        [$value, $unit, $before] = explode('_', $reminder);
                        DB::table('mentorship_schedules')->insert([
                            'cycle_id' => $cycle->id,
                            'value' => (int) $value,
                            'unit' => $unit,
                            'is_active' => 1,
                            'organization_id' => $cycle->organization_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if (auth()->user()->hasRole('OrganizationManager')) {
                    $requiredFields = ['host', 'mail', 'mailpassword', 'port', 'encryption'];
                    $missing = [];

                    $setting = Settings::first();

                    foreach ($requiredFields as $field) {
                        if (!$setting || is_null($setting->$field) || $setting->$field === '') {
                            $missing[] = $field;
                        }
                    }

                    if (!empty($missing)) {
                        return redirect()->back()->with([
                            'message2' => __('missing_mail_settings', ['fields' => implode(', ', $missing)]),
                            'class' => 'danger'
                        ]);
                    }  

                    $admin = User::role('Admin')->orderBy('id')->first();

                    if ($admin) {

                        \Illuminate\Support\Facades\Notification::route('mail', $admin->email)
                            ->notify(new \App\Notifications\ProgramCreatedNotification($cycle, $admin));

                        $messenger = new Messenger();
                        $messenger->sender = auth()->id();
                        $messenger->receiver = $admin->id;
                        $messenger->subject = 'New Program Created';
                        $messenger->message = "A new Program Titled \"{$cycle->title}\" has been created.";
                        $messenger->read = 0;
                        $messenger->save();
                    }
                }
            });
            // return redirect()->route('programs.edit', ['id' => $cycle->id])->with('message', 'Saved');
            return redirect()->route('programlist.index')->with('message', 'Saved');
        }
        else
        {
            // Show Cycle
            $cycles = Cycle::where('id', $id)->first()->toArray();

            $cycles['basename_guideline'] = basename($cycles['program_guideline_file']);

            return response()->json($cycles);
        }
    }

    public function validateURL(Request $request)
    {
        $title = trim( $request->title );
        $slug = Str::slug( $request->slug );

        $slugsList = DB::table('cycles')
            ->select([
                'id',
                'public_slug'
            ])
            ->whereNotNull('public_slug')
            ->when($request->has('id') && $request->id != 0, function ($q) use ($request) {
                return $q->where('id', '!=', $request->id);
            })
            ->get();

        $slugs = $slugsList->pluck('public_slug')->toArray();

        if( !in_array( $slug, $slugs ) ) { return response()->json(['status' => true, 'slug' => $slug, 'message' => __('cycles.Public URL available')]); }
        else
        {
            $availableSlugs = [];
            $baseSlug = Str::slug( $title );
            if( preg_match('/^(.*?)-(\d+)$/', $slug, $matches ) )
            {
                $base = $matches[1];
                $num  = (int) $matches[2];
            }
            else
            {
                $base = $slug;
                $num  = 0;
            }
            $i = $num + 1;
            while( count( $availableSlugs ) < 5 )
            {
                $newSlug = $base . '-' . $i;
                if( !in_array( $newSlug, $slugs ) ) { $availableSlugs[] = $newSlug; }
                $i++;
            }

            if( preg_match('/^(.*?)-(\d+)$/', $baseSlug, $matchings ) )
            {
                $base = $matchings[1];
                $num  = (int) $matchings[2];
            }
            else
            {
                $base = $baseSlug;
                $num  = 0;
            }
            $i = $num + 1;
            $availableSlugs2 = [];
            while( count( $availableSlugs2 ) < 5 )
            {
                $newSlug = $base . '-' . $i;
                if( !in_array( $newSlug, $slugs ) ) { $availableSlugs2[] = $newSlug; }
                $i++;
            }
            $availableSlugs = array_merge( $availableSlugs, $availableSlugs2 );
            return response()->json(['status' => false, 'message' => __('cycles.Public URL not available', ['slug' => $request->slug]), 'slugs' => $availableSlugs]);
        }
    }

    function view($c, Request $request)
    {
        $user = auth()->user();
        $private = Cycle::where('private', $c)->first();

        if( $private == null ) { $private = Cycle::where('id', $c)->first(); }
        $c = $private->id;
        if( $private->allow_non_company == 1 )
        {
            if( !$user->company )
            {
                $result = new Company();
                $result->name_en = $user->first_name_en . " " . $user->last_name_en;
                $result->name_ar = $user->first_name_ar . " " . $user->last_name_ar;
                $result->address_en = $user->address ?? 'Not Set';
                $result->address_ar = $user->address ?? 'Not Set';
                $result->save();

                $update_user = User::find( $user->id );
                $update_user->company_id = $result->id;
                $update_user->HaveCompany = 1;
                $update_user->save();
            }
        }

        $user = User::where('id', $user->id)->first();

        // Check Company ---> forward to profile
        if( !$user->HaveCompany ) {
            return redirect()->route('user.profile_Edit')->with(['msg' => 'You need to enable Have a company first', 'class' => 'danger']);
        }
        if( !$user->company ) {
            return redirect()->route('company.profile_view_mine')->with(['msg' => 'You need to add your company details first', 'class' => 'danger']);
        }

        // Check enrolled in Cycle
        if( $user->company->cycle && $user->company->cycle != $c ) {
            return redirect()->route('core.dashboard_view')->with(['msg' => 'You already enrolled in a cycle.', 'class' => 'danger']);
        }

        // Check private
        $private = Cycle::findOrFail($c);
        /* if( $private->private == null )
        {
            return redirect(route('index.cycle',$c))->with(['message2'=>'هذا البرنامج خاص']);
            abort(404);
        } */

        // Check STEPS
        if( !$user->company->cycle || $user->company->cycle == $c )
        {
            $step = Step::where('cycle_id', $c)->orderBy('arr')->first();
            if( $step?->stype != 1 ) {
                return redirect()->route('core.dashboard_view')->with(['msg' => 'The selected cycle doesn\'t contain a data form.', 'class' => 'danger']);
            }
            if( $step->to < now()->format('Y-m-d') ) {
                return redirect()->route('core.dashboard_view')->with(['msg' => 'Error can\'t open form, Registration timed out.', 'class' => 'danger']);
            }

            $id = $step->id;

            $the_form = BuilderForm::findOrFail( $step->data );
            // Step-scoped pivot (`form_fields`), NOT the BuilderForm template
            // pivot (`builder_fields`) — see the comment in my(). `form_data.
            // field_id` holds a `form_fields.id`, so reading the template pivot
            // yields ids from a different id space and the saved answers never
            // match when one BuilderForm is shared across several steps.
            $fields = $step->fields()
                ->where('builder_forms_id', $step->data)
                ->orderBy('form_fields.id', 'asc')
                ->get();

            $field_map_ids = $fields->pluck('pivot.id')->toArray();
            // تجهيز البيانات بشكل منسق
            $structured = $fields->map(function ($field) {
                $options = json_decode($field->pivot->options ?? '{}', true) ?? [];

                return [
                    'id'          => $field->pivot->id,
                    'label'       => $options['label'] ?? '',
                    'description' => $options['description'] ?? '',
                    'section'     => $options['section'] ?? 'N/A',
                    'type'        => $options['type'] ?? null,
                    'is_required' => (bool)($options['validation']['required'] ?? 0),
                    'options'     => form_field_options($options['values'] ?? null),
                    'logic'       => $options['logic'] ?? 'continue',
                    'min'         => $options['min'] ?? null,
                    'max'         => $options['max'] ?? null,
                    'step'        => $options['step'] ?? null,
                    'label_low'   => $options['label_low'] ?? '',
                    'label_high'  => $options['label_high'] ?? '',
                ];
            })
            ->groupBy('section')
            ->map(function ($questions, $section_title) {
                $first = $questions->first();

                return [
                    'title'       => $section_title,
                    'description' => $first['description'] ?? '',
                    'logic'       => is_array($first['logic']) ? ($first['logic']['section'] ?? 'continue') : ($first['logic'] ?? 'continue'),
                    'questions'   => $questions->values()->map(function ($q) {
                        return [
                            'id'          => $q['id'],
                            'label'       => $q['label'],
                            'is_required' => $q['is_required'],
                            'type'        => $q['type'],
                            'options'     => $q['options'],
                            'logic'       => $q['logic'],
                            'min'         => $q['min'] ?? null,
                            'max'         => $q['max'] ?? null,
                            'step'        => $q['step'] ?? null,
                            'label_low'   => $q['label_low'] ?? '',
                            'label_high'  => $q['label_high'] ?? '',
                        ];
                    })->toArray(),
                ];
            })
            ->values()
            ->toArray();
            $answers = [];
            // البنية النهائية
            $data = [
                'id'          => $step->id,
                'title'       => $step->title,
                'description' => $step->description,
                'build_data'  => $structured,
                'field_ids' => implode(",", $field_map_ids)
            ];

            // $field_map_ids = Formfield::where('step_id', $id)->select('id')->pluck('id')->toArray();
            // $answers = [];
            // $data = [
            //     'title' => $step->title,
            //     'fields' => $step->fields()->orderBy('form_fields.id', 'asc')->get(),
            //     'form_id' => $step->id,
            //     'field_ids' => implode(",", $field_map_ids)
            // ];
            // GV-3827: populate saved answers so a submitted form renders
            // read-only WITH the founder's answers on the /view route too
            // (previously only /my did this, and only when the founder's
            // current step happened to equal this one). Scope by user +
            // this step (form_id, per saveForm's own scoping — see the
            // comment in saveForm()) + this form's fields, so a founder who
            // has moved to a later step still sees this step's answers when
            // navigating back to it. Detection of "already submitted" (=>
            // read-only) is driven by these rows via $answers->count() in
            // the blade.
            $inputs = [];
            $answerRows = Formfield::rightJoin('form_data', 'form_fields.id', 'form_data.field_id')
                ->where('form_data.user_id', Auth::id())
                ->where('form_data.form_id', $id)
                ->whereIn('form_data.field_id', $field_map_ids)
                ->orderBy('form_data.id', 'ASC')
                ->get(['form_data.field_id', 'form_data.answer']);
            foreach ($answerRows as $row) {
                $inputs['field_' . $row->field_id] = $row->answer;
            }
            if (! session()->hasOldInput()) {
                session()->flashInput($inputs);
            }
            $answers = collect($answerRows->pluck('answer'));
            return view('admin.settings.cycles.form', $data)->with('data', $data)->with('step', $step)->with('c', $c)->with('answers', $answers);
        }
    }

    function my($id)
    {
        $step = Step::where(function($query) {
            $query->where('stype', 1)->orWhere('stype', 5);
        })->where('id', $id)->first();
        if (!$step) {
            return redirect()->back()->with(['msg' => 'Error opening the selected form.', 'class' => 'danger']);
        }
        $user = auth()->user();
        // if ($step->to < now()->format('Y-m-d')){return redirect()->back()->with(['msg' => 'Error can\'t open form, step timed out.', 'class' => 'danger']);}
        $id = $step->id;

        // Read the field list through the STEP pivot (`form_fields`), not the
        // BuilderForm template pivot (`builder_fields`).
        //
        // `form_data.field_id` stores a `form_fields.id` — the step-scoped copy
        // of the question. `$the_form->fields()` returns `builder_fields` rows,
        // which belong to the template and are shared by every step using that
        // form, so its pivot ids are a completely different id space. When a
        // BuilderForm is assigned to more than one step, `whereIn('field_id',
        // $field_map_ids)` below matched nothing: the saved answers never
        // loaded, so the form rendered blank AND `$isReadOnly = $hasSubmission`
        // stayed false, letting the founder submit the same form again.
        $the_form = BuilderForm::findOrFail( $step->data );
        $fields = $step->fields()
                ->where('builder_forms_id', $step->data)
                ->orderBy('form_fields.id', 'asc')
                ->get();

        $fields = $fields->map(function($field) {
            $options = json_decode($field->pivot->options ?? '{}', true) ?? [];
            $field->options = $options;
            return $field;
        });
        $field_map_ids = $fields->pluck('pivot.id')->toArray();
        // تجهيز البيانات بشكل منسق
        $structured = $fields->map(function ($field) {
            $options = (array) $field->options;
            return [
                'id'          => $field->pivot->id,
                'label'       => $options['label'] ?? '',
                'description' => $options['description'] ?? '',
                'section'     => $options['section'] ?? 'N/A',
                'type'        => $options['type'] ?? null,
                'is_required' => (bool)($options['validation']->required ?? 0),
                'options'     => form_field_options($options['values'] ?? null),
                'logic'       => $options['logic'] ?? 'continue',
                'allowed_domains' => $options['allowed_domains'] ?? '',
                'min'         => $options['min'] ?? null,
                'max'         => $options['max'] ?? null,
                'step'        => $options['step'] ?? null,
                'label_low'   => $options['label_low'] ?? '',
                'label_high'  => $options['label_high'] ?? '',
            ];
        })
        ->groupBy('section')
        ->map(function ($questions, $section_title) {
            $first = $questions->first();
            return [
                'title'       => $section_title,
                'description' => $first['description'] ?? '',
                'logic'       => is_array($first['logic']) ? ($first['logic']['section'] ?? 'continue') : ($first['logic'] ?? 'continue'),
                'questions'   => $questions->values()->map(function ($q) {
                    return [
                        'id'          => $q['id'],
                        'label'       => $q['label'],
                        'is_required' => $q['is_required'],
                        'type'        => $q['type'],
                        'options'     => $q['options'],
                        'logic'       => (array) $q['logic'],
                        'allowed_domains' => $q['allowed_domains'] ?? '',
                        'min'         => $q['min'] ?? null,
                        'max'         => $q['max'] ?? null,
                        'step'        => $q['step'] ?? null,
                        'label_low'   => $q['label_low'] ?? '',
                        'label_high'  => $q['label_high'] ?? '',
                    ];
                })->toArray(),
            ];
        })
        ->values()
        ->toArray();

        $answers = [];

        // البنية النهائية
        $data = [
            'id'          => $step->id,
            'title'       => $step->title,
            'description' => $step->description,
            'build_data'  => $structured,
            'field_ids' => implode(",", $field_map_ids)
        ];

        // $field_map_ids = Formfield::where('step_id', $id)->where('builder_forms_id', $step->data)->select('id')->pluck('id')->toArray();
        // $answers = [];
        // $data = [
        //     'title' => $step->title,
        //     'fields' => $step->fields()->where('builder_forms_id', $step->data)->orderBy('form_fields.id', 'asc')->get(),
        //     'form_id' => $step->id,
        //     'field_ids' => implode(",", $field_map_ids)
        // ];


        $inputs = [];
        // Scope previously-submitted answers to THIS step. The same BuilderForm
        // (and therefore the same field_map_ids) can be assigned to multiple
        // steps — e.g. a Custom Form step and a Final Screening step. Without a
        // step_id filter, answers submitted in one step bleed into the other's
        // form, since field_id alone is shared across every step using the form
        // (GV-3226).
        $answers = Formfield::rightJoin('form_data', 'form_fields.id', 'form_data.field_id')->where('user_id', $user->id)->where('form_data.step_id', $id)->whereIn('form_data.field_id', $field_map_ids)->orderBy('form_data.id', 'ASC')->get();
        foreach ($answers as $answer) {
            $inputs[ 'field_' . $answer->field_id] = $answer->answer;
        };
        // Seed old() with the saved DB answers ONLY on a normal page load. When
        // saveForm() fails validation it redirects back here with ->withInput();
        // flashing the DB values would overwrite that just-submitted input and
        // blank out the form (especially on a first submission with no saved
        // rows). So defer to any input that is already flashed.
        if (! session()->hasOldInput()) {
            session()->flashInput($inputs);
        }

        return view('admin.settings.cycles.form', $data)->with('data', $data)->with('step', $step)->with('c', $step->cycle_id)->with('answers', $answers);
    }

    public function saveForm(Request $request)
    {
        $id = $request->id;
        $user = auth()->user();
        $step = Step::where('id', $id)->first();

        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:steps,id',
            'field_ids' => 'required|string',
        ]);
        abort_if($validator->fails(), 422, "Data error");

        $field_map_ids = array_filter(array_map('intval', explode(",", $request->input('field_ids'))));

        // Step-scoped pivot (`form_fields`), NOT the BuilderForm template pivot
        // (`builder_fields`) — must match the id space the form posts and that
        // `form_data.field_id` stores. See the comment in my(). Reading the
        // template pivot here made every posted id look "unknown" to the
        // hardening check below, so a shared BuilderForm aborted with a 422.
        $the_form = BuilderForm::findOrFail( $step->data );
        $fields = $step->fields()
            ->where('builder_forms_id', $step->data)
            ->orderBy('form_fields.id', 'asc')
            ->get();

        $field_mapper = $fields->map(function ($field) {
            $pivot = $field->pivot;
            $options = json_decode($pivot->options ?? '{}', true) ?? [];
            $output = ['id' => $pivot->id];
            $output = array_merge($output, $options);
            return $output;
        });

        // Harden the client-supplied `field_ids`. The stepper computes this list
        // from the active navigation path, and the controller trusts it to decide
        // which answers to keep vs. delete. A malformed payload (empty after
        // filtering, or IDs that don't belong to this form) must be rejected
        // BEFORE the destructive delete below — otherwise a truncated list would
        // silently wipe previously-saved answers.
        $valid_field_ids = $field_mapper->pluck('id')->map('intval')->all();
        $unknown_ids = array_diff($field_map_ids, $valid_field_ids);
        abort_if(empty($field_map_ids) || !empty($unknown_ids), 422, "Data error");

        // Pre-hydrate the request with previously-submitted file paths so the
        // `required` rule on file fields passes when the founder re-opens an
        // already-submitted form and does not re-upload anything. Without
        // this, the validator rejects the form as "file is required" even
        // though a valid file is already attached in the database — the bug
        // GV-2134 is fixing.
        $my_submissions = Formdata::where('user_id', Auth::id())
            ->where('step_id', $id)
            ->whereIn('field_id', $field_map_ids)
            ->get();

        foreach ($field_map_ids as $fid) {
            $field_config = $field_mapper->where('id', $fid)->first();
            if ($field_config && ($field_config['type'] ?? null) === 'file' && !$request->hasFile('field_' . $fid)) {
                $existing = $my_submissions->where('field_id', $fid)->first()?->answer;
                if (!empty($existing)) {
                    $request->merge(['field_' . $fid => $existing]);
                }
            }
        }

        // Localized validation messages shared with FormBuilderController.
        $locale = app()->getLocale() === 'ar' ? 'ar' : 'en';
        $messages_map = [
            'en' => [
                'required'        => 'This field is required.',
                'email'           => 'Please enter a valid email address.',
                'url'             => 'Please enter a valid URL starting with http:// or https://.',
                'allowed_domains' => 'The URL must belong to one of these domains: :domains.',
                'numeric'   => 'Please enter numbers only.',
                'number_min' => 'Please enter a valid positive number.',
                'number_max' => 'Please enter a number no greater than :max.',
                'time'      => 'Please enter a valid time.',
                'file_size' => 'File size must not exceed 10 MB.',
                'file_type' => 'Supported file types are Word, Excel, PowerPoint, PDF, JPG, JPEG, PNG, and WEBP.',
                'other_required' => 'This field is required when "Other" is selected.',
                'email_regex' => 'Please enter a valid email address (e.g. name@example.com).',
            ],
            'ar' => [
                'required'        => 'هذا الحقل مطلوب.',
                'email'           => 'يرجى إدخال بريد إلكتروني صحيح.',
                'url'             => 'يرجى إدخال رابط صالح يبدأ بـ http:// أو https://.',
                'allowed_domains' => 'يجب أن ينتمي الرابط إلى أحد هذه النطاقات: :domains.',
                'numeric'   => 'يرجى إدخال أرقام فقط.',
                'number_min' => 'يرجى إدخال رقم موجب صحيح.',
                'number_max' => 'يرجى إدخال رقم لا يتجاوز :max.',
                'time'      => 'يرجى إدخال وقت صحيح.',
                'file_size' => 'يجب ألا يتجاوز حجم الملف 10 ميجابايت.',
                'file_type' => 'أنواع الملفات المدعومة هي Word وExcel وPowerPoint وPDF وJPG وJPEG وPNG وWEBP.',
                'other_required' => 'هذا الحقل مطلوب عند اختيار "أخرى".',
                'email_regex' => 'يرجى إدخال بريد إلكتروني صحيح (مثال: name@example.com).',
            ],
        ];
        $L = $messages_map[$locale];

        $allowed_file_extensions = ['doc','docx','xls','xlsx','ppt','pptx','pdf','jpg','jpeg','png','webp'];
        $max_file_size_kb = 10 * 1024;

        $rules_array = [];
        $custom_messages = [];

        $uid = $user->id;
        $company = $user->company;
        $cid = $company->id;

        // GV-3827: a founder may submit this form only once. Detect a prior
        // completed submission by the existence of form_data rows for this
        // (user, company, step). The stepper never persists partial progress
        // server-side (the whole form posts in one request), so row existence
        // always means "already fully submitted", never "in progress".
        // Scoped on `form_id`, not `step_id`: the destructive delete/insert
        // below (and the GV-3226 fix) both key the "this step" scope off
        // `form_id` (see the comment above the delete at ~line 1650 — "the
        // same BuilderForm can be assigned to multiple steps ... this mirrors
        // the form_id scoping"), so the guard must agree with that column.
        //
        // The flash message follows this controller's existing convention
        // (see e.g. 'You already enrolled in a cycle.' above): an English
        // key string translated via __('admin.'.$msg) using the en/ar
        // resources/lang/*/admin.php files, NOT the blade's inline
        // `$l == 'ar' ? … : …` pattern (that convention isn't in scope here;
        // $l itself isn't defined in this method).
        $alreadySubmitted = Formdata::where('user_id', Auth::id())
            ->where('company_id', $cid)
            ->where('form_id', $id)
            ->exists();
        if ($alreadySubmitted) {
            return redirect()->back()->with([
                'msg'   => 'You have already submitted this form.',
                'class' => 'danger',
            ]);
        }

        $get_fields_data = Formfield::whereIn('id', $field_map_ids)->get();
        /* foreach ($field_map_ids as $fid)
        {
            $checkExistance = $get_fields_data->where('id', $fid)->first();
            if( !$checkExistance ) { continue; }
            $field_options = $checkExistance->options;
            $field_name = 'field_' . $fid;
            $type = $field_options->type ?? 'text';
            $required = intval($field_options->validation->required ?? 0) === 1;

            $rules = [];
            $rules[] = $required ? 'required' : 'nullable';

            switch ($type) {
                case 'email':
                    $rules[] = 'email';
                    $custom_messages[$field_name . '.email'] = $L['email'];
                    break;
                case 'number':
                case 'linear_scale':
                    $rules[] = 'numeric';
                    $custom_messages[$field_name . '.numeric'] = $L['numeric'];
                    break;
                case 'time':
                    $rules[] = 'date_format:H:i';
                    $custom_messages[$field_name . '.date_format'] = $L['time'];
                    break;
                case 'date':
                    $rules[] = 'date';
                    $custom_messages[$field_name . '.date'] = $L['time'];
                    break;
                case 'file':
                    // Only enforce file/max/mimes on a fresh upload. The
                    // hydrated previously-submitted path is a string, so
                    // Laravel's `file` rule would reject it — we skip the
                    // type/size checks when no new file was attached.
                    if ($request->hasFile($field_name)) {
                        $rules[] = 'file';
                        $rules[] = 'max:' . $max_file_size_kb;
                        $rules[] = 'mimes:' . implode(',', $allowed_file_extensions);
                        $custom_messages[$field_name . '.file']  = $L['file_type'];
                        $custom_messages[$field_name . '.max']   = $L['file_size'];
                        $custom_messages[$field_name . '.mimes'] = $L['file_type'];
                    }
                    break;
            }

            $rules_array[$field_name] = implode('|', $rules);
            $custom_messages[$field_name . '.required'] = $L['required'];
        } */

        $active_fields = $field_mapper->whereIn('id', $field_map_ids);
        $inactive_fields = $field_mapper->whereNotIn('id', $field_map_ids);

        foreach( $active_fields as $field )
        {
            $field_name = 'field_' . $field['id'];
            $type = $field['type'] ?? 'text';
            $required = intval($field['validation']['required'] ?? 0) === 1;

            $rules = [];
            $rules[] = $required ? 'required' : 'nullable';
            switch ($type) {
                case 'email':
                    // email:rfc catches malformed addresses; the regex additionally
                    // requires a dotted domain (e.g. rejects "user@example") without
                    // doing a live DNS lookup (email:dns), per GV-3832.
                    $rules[] = 'email:rfc';
                    $rules[] = 'regex:/.+@.+\..+/';
                    $custom_messages[$field_name . '.email'] = $L['email'];
                    $custom_messages[$field_name . '.regex'] = $L['email_regex'];
                    break;
                case 'url':
                    $rules[] = 'url';
                    $custom_messages[$field_name . '.url'] = $L['url'];
                    $allowedDomains = $this->parseAllowedDomains($field['allowed_domains'] ?? '');
                    if (!empty($allowedDomains)) {
                        $domainsLabel = implode(', ', $allowedDomains);
                        $rules[] = function ($attribute, $value, $fail) use ($allowedDomains, $domainsLabel, $L) {
                            if ($value !== null && $value !== '' && !$this->urlHostAllowed($value, $allowedDomains)) {
                                $fail(str_replace(':domains', $domainsLabel, $L['allowed_domains']));
                            }
                        };
                    }
                    break;
                case 'number':
                    // Non-negative by default (GV-3831/GV-3453): when the field
                    // author set no min, floor at 0. Respect an authored min/max
                    // when present. linear_scale is handled separately below and
                    // intentionally keeps only the plain `numeric` rule, since its
                    // scale may legitimately start below 0.
                    $rules[] = 'numeric';
                    $custom_messages[$field_name . '.numeric'] = $L['numeric'];
                    $numMin = (isset($field['min']) && $field['min'] !== '') ? $field['min'] : 0;
                    $rules[] = 'min:' . $numMin;
                    $custom_messages[$field_name . '.min'] = $L['number_min'];
                    if (isset($field['max']) && $field['max'] !== '') {
                        $rules[] = 'max:' . $field['max'];
                        $custom_messages[$field_name . '.max'] = str_replace(':max', $field['max'], $L['number_max']);
                    }
                    break;
                case 'linear_scale':
                    $rules[] = 'numeric';
                    $custom_messages[$field_name . '.numeric'] = $L['numeric'];
                    break;
                case 'time':
                    $rules[] = 'date_format:H:i';
                    $custom_messages[$field_name . '.date_format'] = $L['time'];
                    break;
                case 'date':
                    $rules[] = 'date';
                    $custom_messages[$field_name . '.date'] = $L['time'];
                    break;
                case 'file':
                    // Only enforce file/max/mimes on a fresh upload. The
                    // hydrated previously-submitted path is a string, so
                    // Laravel's `file` rule would reject it — we skip the
                    // type/size checks when no new file was attached.
                    if ($request->hasFile($field_name)) {
                        $rules[] = 'file';
                        $rules[] = 'max:' . $max_file_size_kb;
                        $rules[] = 'mimes:' . implode(',', $allowed_file_extensions);
                        $custom_messages[$field_name . '.file']  = $L['file_type'];
                        $custom_messages[$field_name . '.max']   = $L['file_size'];
                        $custom_messages[$field_name . '.mimes'] = $L['file_type'];
                    }
                    break;
            }

            // Closure rules (e.g. the allowed-domains check) can't be joined into
            // the pipe-delimited string, so pass the array form when any rule is
            // not a plain string; otherwise keep the original string format.
            $hasClosureRule = collect($rules)->contains(fn ($r) => !is_string($r));
            $rules_array[$field_name] = $hasClosureRule ? $rules : implode('|', $rules);
            $custom_messages[$field_name . '.required'] = $L['required'];

            // "Other" free-text is optional by default; it's only required
            // when the question was explicitly configured with
            // validation.other_required (see FormBuilderController). The
            // `values` option list is stored as a comma-joined string, so
            // normalize before checking for the __OTHER__ sentinel.
            //
            // Detection keys off the SUBMITTED ANSWER still carrying the
            // "__OTHER__" marker — NOT the mere presence of the `{field}_other`
            // key. The client only rewrites "__OTHER__" into the typed value
            // when that value is non-empty (see normalizeOtherFields in the
            // view); so when the user picks Other and leaves the box empty, the
            // raw "__OTHER__" survives into the payload. Keying off the marker
            // is reliable even when the answer's section is disabled at submit
            // and the `{field}_other` companion input never posts.
            $optionValues = $field['values'] ?? ($field['options'] ?? []);
            if (!is_array($optionValues)) {
                $optionValues = array_map('trim', explode(',', (string) $optionValues));
            }
            if (in_array('__OTHER__', $optionValues, true)) {
                $answer = $request->input($field_name);
                $answerHasOther = is_array($answer)
                    ? in_array('__OTHER__', $answer, true)
                    : $answer === '__OTHER__';
                if ($answerHasOther) {
                    $otherKey = $field_name . '_other';
                    $otherRequired = intval($field['validation']['other_required'] ?? 0) === 1;
                    $rules_array[$otherKey] = $otherRequired ? 'required|string' : 'nullable|string';
                    if ($otherRequired) {
                        $custom_messages[$otherKey . '.required'] = $L['other_required'];
                    }
                }
            }
        }

        $dynamic_validator = Validator::make($request->all(), $rules_array, $custom_messages);
        if( $dynamic_validator->fails() ) { return redirect()->back()->withErrors($dynamic_validator)->withInput(); }

        // update cycle & step
        $company->update(['cycle' => $request->cycle, 'step' => $request->id]);
        $company = Company::with(['stepdata', 'cycledata'])->find($cid);

        // gather the form data as field_name => [ label, data ]
        /* foreach ($field_map_ids as $fid)
        {
            $checkExistance = $get_fields_data->where('id', $fid)->first();
            if( !$checkExistance ) { continue; }
            $field_options = $checkExistance->options;
            $field_name = 'field_' . $fid;

            $verifyInput = $field_mapper->where('id', $fid)->first();

            if( $verifyInput && $verifyInput['type'] == 'file' )
            {
                if ($request->hasFile($field_name))
                {
                    $file = $request->file($field_name);
                    // $originalName = $file->getClientOriginalName();
                    // $file->storeAs('formsubmissions', $originalName, 'public_uploads');
                    // $request->{$field_name} = 'formsubmissions/' . $originalName;
                    $imageName = $file->getClientOriginalName() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('formsubmissions/'), $imageName);
                    $request->{$field_name} = 'formsubmissions/' . $imageName;
                }
                else
                {
                    $request->{$field_name} = $my_submissions->where('field_id', $fid)->first()?->answer ?? null;
                }
            }

            if( $verifyInput && $verifyInput['type'] == 'checkboxes' && is_array($request->{$field_name}) )
            {
                $request->{$field_name} = json_encode($request->{$field_name});
            }

            Formdata::where('field_id', $fid)->where('company_id', $cid)->delete();
            Formdata::updateOrInsert([
                'field_id' => $fid,
                'company_id' => $cid,
                'user_id' => $uid,
                'form_id' => $id,
                'step_id' => Auth::user()->company->step,
                'question' => $field_options->label,
                'answer' => $request->{$field_name},
                'screening' => '{}',
                'comments' => '{}',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } */

        foreach( $active_fields as $field )
        {
            $field_name = 'field_' . $field['id'];
            if( $field['type'] == 'file' )
            {
                if ($request->hasFile($field_name))
                {
                    $file = $request->file($field_name);
                    $imageName = $file->getClientOriginalName() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('formsubmissions/'), $imageName);
                    $request->{$field_name} = 'formsubmissions/' . $imageName;
                }
                else
                {
                    $request->{$field_name} = $my_submissions->where('field_id', $field['id'])->first()?->answer ?? null;
                }
            }
            if( $field['type'] == 'checkboxes' && is_array($request->{$field_name}) )
            {
                $request->{$field_name} = json_encode($request->{$field_name});
            }

            // Scope the delete to THIS step (form_data.form_id holds the step id).
            // The same BuilderForm — and therefore the same field_id values — can
            // be assigned to multiple steps (e.g. a Custom Form step and a Final
            // Screening step). Deleting by field_id + company_id alone would wipe
            // the other step's answers for the shared fields, so re-submitting one
            // step destroyed the other's saved data (GV-3226). This mirrors the
            // form_id scoping already used by the inactive-fields cleanup below.
            Formdata::where('field_id', $field['id'])->where('company_id', $cid)->where('form_id', $id)->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Formdata::updateOrInsert([
                'field_id' => $field['id'],
                'company_id' => $cid,
                'user_id' => $uid,
                'form_id' => $id,
                'step_id' => $company->step,
                'question' => $field['label'],
                'answer' => is_array( $request->{$field_name} ) ? json_encode( $request->{$field_name} ) : $request->{$field_name},
                'screening' => '{}',
                'comments' => '{}',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // Clear answers for fields that are NOT on the active navigation path
        // (e.g. a logic branch the founder did not take). This is intentional
        // pruning, but it is destructive — it is only reached because the
        // `field_ids` list was validated above to be non-empty and to contain
        // only IDs that belong to this form. The delete is further scoped to
        // this company AND this form so it can never touch unrelated answers.
        $inactive_ids = $inactive_fields->pluck('id')->toArray();
        if( !empty($inactive_ids) )
        {
            Formdata::whereIn('field_id', $inactive_ids)
                ->where('company_id', $cid)
                ->where('form_id', $id)
                ->delete();
        }

        $data = [];
        $name = $user->first_name_en . ' ' . $user->last_name_en;

        $data = [
            'name' => $name,
            'email' => $user->email,
            'step' => $company->stepdata->title,
            'cycle' => $company->cycledata->title,
            'cycle_id' => $company->cycledata->id,
        ];

        // إرسال الإشعار
        if (!in_array(env('APP_ENV', 'local'), ['local'])) {
            try {
                broadcast(new StartupJoining($data));
                // Once per program: re-submitting steps must not re-send it.
                if ($user->claimProgramJoinSlot((int) $company->cycledata->id)) {
                    try {
                        $user->notify(new StartupJoiningNotification($data));
                    } catch (\Throwable $th) {
                        \App\User::where('id', $user->id)->update(['program_join_notified_cycle' => null]);
                        throw $th;
                    }
                }
                log::info('Sent notification to ' . $user->email);
            } catch (\Throwable $th) {
                throw $th;
            }

            $phoneNumber = $user->country_code_w_a . $user->whatsapp;
            $Massage = "Dear " . $user->name . ",
            Congratulations! You have successfully joined the" . $data['cycle'] . ", 
            We are excited to have you on board and look forward to supporting your journey.
            Thanks from " . env('APP_NAME');
            $result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
            Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
        }


        return redirect(url('/dashboard'))->with(['msg' => 'formSubmittedSuccessfully', 'class' => 'success']);
    }

    function normalizeFieldName($fieldName)
{
    if (preg_match('/^field_\d+_(\d+)$/', $fieldName, $matches)) {
        return 'field_' . intval($matches[1]);
    }

    if (preg_match('/^field_(\d+)$/', $fieldName, $matches)) {
        return 'field_' . intval($matches[1]);
    }

    return $fieldName;
}

    public function testSend(Request $request)
    {

        $phone = $request->query('phone');
        $message = $request->query('message', 'رسالة تجريبية من API');

        if (!$phone) {
            return response()->json(['error' => 'يرجى إدخال رقم الهاتف'], 400);
        }

        $result = $this->sendMessage($phone, $message);

        return response()->json($result);
    }

    public function sendMessage($phoneNumber, $message)
    {
        try {
            $sessionId = 'ehabwhats'; // اسم الجلسة في GrowthLabs
            $apiKey = env('WAPI_KEY'); // مفتاح API من .env

            // Step 1: Get chat ID
            $response = Http::withHeaders([
                'x-api-key' => $apiKey
            ])->post("https://wapi.growthlabs.app/client/getNumberId/$sessionId", [
                'number' => $phoneNumber
            ]);

            $chatId = $response->json('result._serialized');

            if (!$chatId) {
                Log::error("Failed to get chatId for number: {$phoneNumber}");
                return false;
            }

            // Step 2: Send message
            $sendResponse = Http::withHeaders([
                'x-api-key' => $apiKey
            ])->post("https://wapi.growthlabs.app/client/sendMessage/$sessionId", [
                'chatId' => $chatId,
                'contentType' => 'string',
                'content' => $message
            ]);

            return $sendResponse->json();
        } catch (\Exception $e) {
            Log::error('Failed to send message via GrowthLabs WhatsApp API: ' . $e->getMessage());
            return false;
        }
    }

    // private function storeUploadedFile(Request $request, string $inputName, string $subfolder = '')
    // {
    //     if( !$request->hasFile( $inputName ) ) { return null; }
    //     $file = $request->file( $inputName );
    //     abort_unless($file->isValid(), 400);

    //     $raw_name = str_replace('.' . $file->getClientOriginalExtension(), '', $file->getClientOriginalName()) . '-' . $file->getClientOriginalExtension();
    //     $filename = time() . '_' . $raw_name . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

    //     $path = $subfolder ? "$subfolder/$filename" : $filename;
    //     Storage::disk('public_uploads')->put($path, file_get_contents($file->getRealPath()));

    //     return $path;
    // }
}
