<?php

namespace App\Http\Controllers;

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
use Carbon\Carbon;

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
    protected $whatsAppService;
    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    public function exportcyclesComp(Request $request)
    {
        // تحقق من أن البريد الإلكتروني موجود

        // $data2 = [
        //     'step' => $request->input('step'),
        //     'userEmail' => auth()->user()->email
        // ];

        $cycle = $request->cycle;
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
        if(auth()->user()->hasRole('Admin')) {
            Company::where('cycle', $cycle)->chunk(1000, function($companies) use (&$sheet, &$row) {
                foreach ($companies as $companie) {
                    $sheet->setCellValue('A' . $row, $companie?->id);
                    $sheet->setCellValue('B' . $row, $companie?->name);
                    $sheet->setCellValue('C' . $row, $companie?->user?->title);
                    $sheet->setCellValue('D' . $row, $companie?->user?->name);
                    $sheet->setCellValue('E' . $row, $companie?->user?->phone);
                    $sheet->setCellValue('F' . $row, $companie?->user?->email);
                    foreach(\App\Step::where('cycle_id',$companie->cycle)->orderBy('arr')->get() as $step){
                        $sheet->setCellValue('G' . $row, $step->title);
                    }
                    $sheet->setCellValue('H' . $row, $companie->created_at);
                    $row++;
                }
            });
        }else{
        Company::where('cycle', $cycle)->chunk(1000, function($companies) use (&$sheet, &$row) {
        foreach ($companies as $companie) {
            
            $sheet->setCellValue('A' . $row, $companie?->id);
            $sheet->setCellValue('B' . $row, $companie?->name);
            $sheet->setCellValue('C' . $row, $companie?->user?->title);
            $sheet->setCellValue('D' . $row, $companie?->user?->name);
            $sheet->setCellValue('E' . $row, $companie?->user?->phone);
            $sheet->setCellValue('F' . $row, $companie?->user?->email);
            foreach(\App\Step::where('cycle_id',$companie->cycle)->orderBy('arr')->get() as $step){
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
            })->orderBy('id', 'DESC')->get();
            if (!empty($cycles->toArray())) {
                $id = $cycles->pluck('id')[0];
            }
        } else {
            $cycles = Cycle::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                return $q->whereIn('organization_id', $orgIds);
            })->get();
        }
        $companies = Company::where('cycle', $id);
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


        $companies = $companies->get();
        return view('admin.settings.cycles.users', compact('cycles', 'id', 'companies', 'steps'));
    }

    public function modify(Request $request)
    {
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
            $request->merge([
                'start' => Carbon::createFromFormat('m/d/Y', trim($dates[0]))->format('Y-m-d'), // التاريخ الأول
                'end' => Carbon::createFromFormat('m/d/Y', trim($dates[1]))->format('Y-m-d'),   // التاريخ الثاني
                'fromDate' => Carbon::createFromFormat('m/d/Y', trim($dates[0]))->format('Y-m-d'), // التاريخ الأول
                'toDate' => Carbon::createFromFormat('m/d/Y', trim($dates[1]))->format('Y-m-d'),   // التاريخ الثاني

            ]);
        }

        if ($type == 1 || $isEdit == true)
        {
            /// Edit Cycle (to be moved to standalone controller)
            $request->validate([
                'organization_id2' => 'required|exists:organizations,id',
                'title' => 'required|string|max:250|unique:cycles,title,' . $id,
                'description' => 'required|string|max:100',
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
            ], [
                'public_slug.required_if' => __('cycles.Public URL required'),
                'public_slug.unique' => __('cycles.Public URL already exist'),
                'public_slug.regex' => __('cycles.Public URL must be alphanumeric'),
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
            $cycle->description = $request->description;
            $cycle->start = Carbon::parse($request->start);
            $cycle->end = Carbon::parse($request->end);
            $cycle->private = $private;
            $cycle->cohort = $request->cohort;
            $cycle->allow_non_company = $request->has('allow_non_company') ? 1 : 0;
            $cycle->step_type = $request->program_step_type;

            $cycle->program_guideline = $request->program_guideline;
            if ($request->hasFile('program-guideline-pdf')) {
                $originalName = $request->file('program-guideline-pdf')->getClientOriginalName();
                $cycle->program_guideline_file = $request->file('program-guideline-pdf')->storeAs('programs', $originalName, 'public_uploads');
            }
            $cycle->budget_allocation = $request->budget_allocation;
            $cycle->currency_type = $request->currency_type;
            $cycle->financial_guideline = $request->financial_policy;
            if ($request->hasFile('financial_policy_pdf')) {
                $originalName = $request->file('financial_policy_pdf')->getClientOriginalName();
                $cycle->financial_guideline_file = $request->file('financial_policy_pdf')->storeAs('programs', $originalName, 'public_uploads');
            }
            $cycle->legal_guideline = $request->legal_policy;
            if ($request->hasFile('legal_policy_pdf')) {
                $originalName = $request->file('legal_policy_pdf')->getClientOriginalName();
                $cycle->legal_guideline_file =  $request->file('legal_policy_pdf')->storeAs('programs', $originalName, 'public_uploads');
            }
            if ($request->delegation_option == 'yes' && $request->hasFile('delegation_pdf')) {
                $originalName = $request->file('delegation_pdf')->getClientOriginalName();
                $cycle->delegation_file =  $request->file('delegation_pdf')->storeAs('programs', $originalName, 'public_uploads');
               
            }
            $cycle->auto_approve_mentorship_requests = $request->has('auto_approve_mentorship_requests') ? 1 : 0;

            if ($request->hasFile('img_url')) {
                    $image = $request->file('img_url');
                    $imageName = time() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('programs/'), $imageName);
                    $cycle->img_url = 'programs/' . $imageName;
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
        elseif ($type == 2) {

            /// Save Cycle



            return redirect()->back()->with('message', 'Saved');
        } elseif ($type == 3) {
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
        } elseif ($type == 4) {
            /// arrange steps
            $i = 1;
            foreach ($request->steps as $id) {
                Step::where('id', $id)->update(['arr' => $i++]);
            }
            return Response()->json('Arranged ');
        } elseif ($type == 5) {
            if (is_null($request->fromDate) || is_null($request->toDate)) {
                return redirect()->back()->with(['message2' => 'add start and end date first', 'class' => 'danger']);
            }
            /// add new step
            $stype = $request->stype;
            
            $data = null;
           
            if ($stype == 1) {
                $data = $request->FormSelect;
                $id = Step::insertGetId([
                    'cycle_id' => $request->cid,
                    'title' => $request->title,
                    'description' => $request->description,
                    'stype' => $stype,
                    'data' => $data,
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
            }elseif ($stype == 2) {
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
            }elseif ($stype == 3) {
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
            }elseif ($stype == 4) {
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
            }elseif ($stype == 5) {
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
            }elseif ($stype == 6) {
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
            }elseif ($stype == 7) {
                
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
        } elseif ($type == 6) {
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
        } elseif ($type == 7) {
            // Modify step
            $step = Step::where('id', $request->sid)->first()->toArray();

            return response()->json($step);
        } elseif ($type == 8) {
            /// deleted saved data if type changed
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
            elseif ($request->has("mFormSelect") && $request->mFormSelect)
            {
                $step8 = Step::find($request->sid);
                $updatedArray = [
                    'title' => $request->title,
                    'data' => $request->mFormSelect,
                    'description' => $request->description,
                    'from' => Carbon::parse($request->fromDate),
                    'to' => Carbon::parse($request->toDate)
                ];
                if( $step8->stype == 1 )
                {
                    $counter = 0;
                    $organization_id = $step8->cycle->organization_id;
                    $cycles_ids = Cycle::where('organization_id', $organization_id)->pluck('id')->toArray();
                    $slugs = Step::whereIn('cycle_id', $cycles_ids)->where('stype', 1)->pluck('public_slug')->unique()->filter(function($value) { return !empty($value); })->values()->toArray();

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
                Step::where('id', $request->sid)->update($updatedArray);
                $form_fields = FormField::where('step_id', $step8->id)->where('builder_forms_id', $step8->data)->get();
                if ($form_fields->isEmpty()) {
                    $new = BuilderField::where('builder_form_id', $step8->data)->orderBy('id')->get();
                    if (count($new) > 0) {
                        foreach ($new as $n) {
                            Formfield::insert([
                                'step_id' => $request->sid,
                                'field_id' => $n->field_id,
                                'options' => json_encode($n->options),
                                'builder_forms_id' => $n->builder_form_id
                            ]);
                        }
                    }
                }else{
                    $form_fields = FormField::where('step_id', $step8->id)->whereNull('builder_forms_id')->update(['builder_forms_id' => $request->mFormSelect]);
                }
            } else {
                
                Step::where('id', $request->sid)->update(['title' => $request->title, 'count_team_number' => $request->count_team_number, 'description' => $request->description, 'from' => Carbon::parse($request->fromDate), 'to' => Carbon::parse($request->toDate)]);
            }
            return redirect()->back()->with('message', 'Saved');
        }
        elseif ($request->tab_id == 1)
        {
            // create new cycle (to be moved to standalone controller)
            $request->validate([
                'organization_id2' => 'required|exists:organizations,id',
                'title' => 'required|string|max:250',
                'description' => 'required|string|max:100',
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
            ], [
                'public_slug.required_if' => __('cycles.Public URL required'),
                'public_slug.unique' => __('cycles.Public URL already exist'),
                'public_slug.regex' => __('cycles.Public URL must be alphanumeric'),
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
                $cycle->description = $request->description;
                $cycle->start = Carbon::parse($request->start);
                $cycle->end = Carbon::parse($request->end);
                $cycle->private = $private;
                $cycle->cohort = $request->cohort;
                $cycle->allow_non_company = $request->has('allow_non_company') ? 1 : 0;
                $cycle->auto_approve_mentorship_requests = $request->has('auto_approve_mentorship_requests') ? 1 : 0;
                // Check if file is uploaded

                $cycle->program_guideline = $request->program_guideline;
                if ($request->hasFile('program-guideline-pdf')) {
                    $originalName = $request->file('program-guideline-pdf')->getClientOriginalName();
                    $cycle->program_guideline_file = $request->file('program-guideline-pdf')->storeAs('programs', $originalName, 'public_uploads');
                }


                $cycle->budget_allocation = $request->budget_allocation;
                $cycle->currency_type = $request->currency_type;
                $cycle->financial_guideline = $request->financial_policy;
                if ($request->hasFile('financial_policy_pdf')) {
                    $originalName = $request->file('financial_policy_pdf')->getClientOriginalName();
                    $cycle->financial_guideline_file =  $request->file('financial_policy_pdf')->storeAs('programs', $originalName, 'public_uploads');
                }

                $cycle->legal_guideline = $request->legal_policy;
                if ($request->hasFile('legal_policy_pdf')) {
                    $originalName = $request->file('legal_policy_pdf')->getClientOriginalName();
                    $cycle->legal_guideline_file = $request->file('legal_policy_pdf')->storeAs('programs', $originalName, 'public_uploads');
                }

                if ($request->delegation_option == 'yes' && $request->hasFile('delegation_pdf')) {
                    $originalName = $request->file('delegation_pdf')->getClientOriginalName();
                    $cycle->delegation_file = $request->file('delegation_pdf')->storeAs('programs', $originalName, 'public_uploads');
                }
                if ($request->hasFile('img_url')) {
                    $image = $request->file('img_url');
                    $imageName = time() . '.' . $image->getClientOriginalExtension();

                    $image->move(public_path('programs/'), $imageName);
                    $cycle->img_url = 'programs/' . $imageName;
                    
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
            return redirect()->route('programs.edit', ['id' => $cycle->id])->with('message', 'Saved');
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
            if( !$user->HaveCompany )
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

            $field_map_ids = Formfield::where('step_id', $id)
                ->where('builder_forms_id', $step->data)
                ->pluck('id')
                ->toArray();

            // جلب كل الحقول الخاصة بالـ step
            $fields = $step->fields()
                ->where('builder_forms_id', $step->data)
                ->orderBy('form_fields.id', 'asc')
                ->get();

            // تجهيز البيانات بشكل منسق
            $structured = $fields->map(function ($field) {
                $options = json_decode($field->pivot->options ?? '{}', true) ?? [];

                return [
                    'id'          => $field->id,
                    'label'       => $options['label'] ?? '',
                    'description' => $options['description'] ?? '',
                    'section'     => $options['section'] ?? 'N/A',
                    'type'        => $options['type'] ?? null,
                    'is_required' => (bool)($options['validation']['required'] ?? 0),
                    'options'     => isset($options['values'])
                        ? (is_array($options['values']) ? $options['values'] : explode(',', $options['values']))
                        : [],
                    'logic'       => $options['logic'] ?? 'continue',
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
            if( $user->company->step == $id ) {
                $answers = Formfield::join('form_data', 'form_fields.id', 'form_data.field_id')->where('user_id', Auth::id())->orderBy('form_data.id', 'ASC')->select('answer')->pluck('answer')->toArray();
            }
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

        $get_fields = Formfield::where('step_id', $id)->where('builder_forms_id', $step->data)->get();
        $field_map_ids = $get_fields->pluck('id')->toArray();

        // جلب كل الحقول الخاصة بالـ step
        $fields = $step->fields()
            ->where('builder_forms_id', $step->data)
            ->orderBy('form_fields.id', 'asc')
            ->get();

        // تجهيز البيانات بشكل منسق
        $structured = $get_fields->map(function ($field) {
            
            $options = (array) $field->options;
            return [
                'id'          => $field->id,
                'label'       => $options['label'] ?? '',
                'description' => $options['description'] ?? '',
                'section'     => $options['section'] ?? 'N/A',
                'type'        => $options['type'] ?? null,
                'is_required' => (bool)($options['validation']->required ?? 0),
                'options'     => isset($options['values'])
                    ? (is_array($options['values']) ? $options['values'] : explode(',', $options['values']))
                    : [],
                'logic'       => $options['logic'] ?? 'continue',
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
        $answers = Formfield::join('form_data', 'form_fields.id', 'form_data.field_id')->where('user_id', $user->id)->whereIn('form_data.field_id', $field_map_ids)->orderBy('form_data.id', 'ASC')->get();
        foreach ($answers as $answer) {
            $inputs[ 'field_' . $answer->field_id] = $answer->answer;
        };
        session()->flashInput($inputs);

        return view('admin.settings.cycles.form', $data)->with('data', $data)->with('step', $step)->with('c', $step->cycle_id)->with('answers', $answers);
    }

    public function saveForm(Request $request)
    {
        $id = $request->id;
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:steps,id',
            'field_ids' => 'required|string',
        ]);
        abort_if($validator->fails(), 422, "Data error");

        // generating validation rules based on dynamic field config data
        $field_map_ids = explode(",", $request->input('field_ids'));
        $field_required_rules = collect($field_map_ids)->mapWithKeys(function ($id) {
            $field_options = Formfield::findOrFail($id)->options;
            if ($field_options->validation->required == 1) {
                $rules = ['required'];
                if (isset($field_options->type) && $field_options->type == "email") $rules[] = 'email';
                return [
                    'field_' . $id => implode("|", $rules)
                ];
            }else {
                return ['field_' . $id => 'nullable'];
            }
        });

        $dynamic_validator = Validator::make($request->all(), $field_required_rules->toArray(), [
            'required' => "field can't be left blank",
            'email' => 'field must be a valid email address'
        ]);
        if ($dynamic_validator->fails()) {
            return redirect()->back()
                ->withErrors($dynamic_validator)
                ->withInput();
        }

        // gather the form data as field_name => [ label, data ]
        foreach ($field_map_ids as $fid) {

            $field_options = Formfield::findOrFail($fid)->options;
            $field_name = 'field_' . $fid;

            $uid = Auth::id();
            $cid = Auth::user()->company->id;

          if ($request->hasFile($field_name)) {
           $file = $request->file($field_name);
        //    $originalName = $file->getClientOriginalName();
        //    $file->storeAs('formsubmissions', $originalName, 'public_uploads');
        //    $request->{$field_name} = 'formsubmissions/' . $originalName;
         $imageName = $file->getClientOriginalName() . '.' . $file->getClientOriginalExtension();
             $file->move(public_path('formsubmissions/'), $imageName);
			 $request->{$field_name} = 'formsubmissions/' . $imageName;

        }
            // update cycle & step
            Auth::user()->company->update(['cycle' => $request->cycle, 'step' => $request->id]);

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
        }

        $data = [];
        $user = Auth::user();
        $name = $user->first_name_en . ' ' . $user->last_name_en;

        $data = [
            'name' => $name,
            'email' => $user->email,
            'step' => $user->company->stepdata->title,
            'cycle' => $user->company->cycledata->title,
        ];

        // إرسال الإشعار
        if (!in_array(env('APP_ENV', 'local'), ['local'])) {
            try {
                broadcast(new StartupJoining($data));
                $user->notify(new StartupJoiningNotification($data));
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
