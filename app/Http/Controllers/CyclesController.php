<?php

namespace App\Http\Controllers;

use App\Form;
use App\Step;
use App\User;
use App\Cycle;
use App\Company;
use App\Message;
use App\Formdata;
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
use Illuminate\Http\Request;
use App\Jobs\ExportCycleCompData;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Input;
use App\Mail\ProgramCreatedNotification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
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

        $data2 = [
            'step' => $request->input('step'),
            'userEmail' => auth()->user()->email
        ];

        // إرسال وظيفة (Job) لتحميل البيانات وإرسال البريد
        ExportCycleCompData::dispatch($data2);
        
        return response()->json(['message' => 'Export process started. You will receive an email with the download attached.']);
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
        $organization_id = auth()->user()->organization_id;

        $cycles = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
            return $q->where('organization_id', $organization_id);
        })->orderBy('start', 'DESC')->get();
        $steps = null;
        if (count($cycles) > 0) {
            if ($id == 0) {
                $id = $cycles->pluck('id')[0];
                $steps = Step::where('cycle_id', $id)->orderBy('arr')->get();
            } else {
                $steps = Step::where('cycle_id', $id)->orderBy('arr')->get();
            }
        }
        $svgs = ['notebook', 'search', 'idea', 'comments', 'trophy'];
        return view('admin.settings.cycles.index', compact('cycles', 'steps', 'id', 'svgs'));
    }

    public function users(Request $request, $id)
    {
        $organization_id = auth()->user()->organization_id;

        if ($id == 0) {
            $cycles = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
                return $q->where('organization_id', $organization_id);
            })->orderBy('id', 'DESC')->get();
            if (!empty($cycles->toArray())) {
                $id = $cycles->pluck('id')[0];
            }
        } else {
            $cycles = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
                return $q->where('organization_id', $organization_id);
            })->get();
        }
        $companies = Company::where('cycle', $id);
        $uniqueCycleIds = $companies->pluck('cycle')->unique();
        $steps = Step::whereIn('cycle_id', $uniqueCycleIds)->orderBy('arr')->get();
        if($request->step_id != 0){
            $companies = $companies->where('step', $request->step_id);
        }
        if($request->country_id != 0){
            $companies = $companies->where('country_id', $request->country_id);
        }
        if($request->sector_id != 0){
            $companies = $companies->where('sector', $request->sector_id);
        }
        if($request->Dstage_id != 0){
            $companies = $companies->where('stage', $request->Dstage_id);
        }
        if($request->funding_stage != 0){
            $companies = $companies->where('development_stage', $request->funding_stage);
        }
        if($request->targeted_market != 0){
            $companies = $companies->where(function ($query) use ($request) {
                foreach ($request->targeted_market as $market) {
                    $query->orWhereJsonContains('targeted_market', $market);
                }
            });
        }
        if($request->BType != 0){
            $companies = $companies->where('business_type', $request->BType);
        }


        $companies = $companies->get();
        return view('admin.settings.cycles.users', compact('cycles', 'id', 'companies', 'steps'));
    }

    public function modify(Request $request)
    {
        if( $request->cycleid == null && isset( $request->typeedit ) )
        {
            $id = $request->id;
            $type = $request->typeedit;
        }
        else
        {
            $id = $request->cycleid;
            $type = $request->type;
        }
        
        if ($request->has('date_range')) {
            
            $dates = explode(' - ', $request->date_range);
            $request->merge([
                'start' => Carbon::createFromFormat('m/d/Y', trim($dates[0]))->format('Y-m-d'), // التاريخ الأول
                'end' => Carbon::createFromFormat('m/d/Y', trim($dates[1]))->format('Y-m-d'),   // التاريخ الثاني
                'fromDate' => Carbon::createFromFormat('m/d/Y', trim($dates[0]))->format('Y-m-d'), // التاريخ الأول
                'toDate' => Carbon::createFromFormat('m/d/Y', trim($dates[1]))->format('Y-m-d'),   // التاريخ الثاني

            ]);
            
        }
       
        if ($type == 1) {
            
            
            /// Edit Cycle
            $request->validate([
                'organization_id2' => 'required|exists:organizations,id',
                'title' => 'required|string|max:250',
                'description' => 'required|string|max:2000',
                'start' => 'required|date',
                'end' => 'required|date',
            ]);

            

            $cycle = Cycle::find($id);

            $private = null;
            if ($request->private) {
                $private = sha1(now());
            }

            // Check if file is uploaded
            $programGuidelineFile = null;
            if ($request->hasFile('program-guideline-pdf')) {
                $programGuidelineFile = $request->file('program-guideline-pdf')->store('programs', 'program');
            }else{
                $programGuidelineFile = $cycle->program_guideline_file;
            }

            $financialGuidelineFile = null;
            if ($request->hasFile('financial_policy_pdf')) {
                $financialGuidelineFile = $request->file('financial_policy_pdf')->store('financials', 'program');
            }else{
                $financialGuidelineFile = $cycle->financial_guideline_file;
            }

            $legalPolicyPath = null;
            if ($request->hasFile('legal_policy_pdf')) {
                $legalPolicyPath = $request->file('legal_policy_pdf')->store('legal_policies', 'program');
            }else{
                $legalPolicyPath = $cycle->legal_guideline_file;
            }


            $delegationPdfPath = null;
            if ($request->delegation_option === 'yes' && $request->hasFile('delegation_pdf')) {
                $delegationPdfPath = $request->file('delegation_pdf')->store('delegations', 'program');
            }else{
                $delegationPdfPath = $cycle->delegation_file;
            }


            $cycle->organization_id = $request->organization_id2;
            $cycle->title = $request->title;
            $cycle->description = $request->description;
            $cycle->start = Carbon::parse($request->start);
            $cycle->end = Carbon::parse($request->end);
            $cycle->private = $private;
            $cycle->allow_non_company = $request->has('allow_non_company') ? 1 : 0;

            $cycle->program_guideline = $request->program_guideline;
            $cycle->program_guideline_file = $programGuidelineFile;

            $cycle->budget_allocation = $request->budget_allocation;
            $cycle->currency_type = $request->currency_type;
            $cycle->financial_guideline = $request->financial_policy;
            $cycle->financial_guideline_file = $financialGuidelineFile;

            $cycle->legal_guideline = $request->legal_policy;
            $cycle->legal_guideline_file = $legalPolicyPath;
            $cycle->delegation_file = $delegationPdfPath;
            $cycle->auto_approve_mentorship_requests = $request->has('auto_approve_mentorship_requests') ? 1 : 0;


            $cycle->save();
            return redirect()->back()->with('message', 'Saved');
        } elseif ($type == 2) {

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
                            'options' => json_encode($n->options)
                        ]);
                    }
                }
            } elseif ($stype == 2) {
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
            } elseif ($stype == 3) {
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
            } elseif ($stype == 4) {
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
            } elseif ($stype == 5) {
                if (is_null($request->fromDate) || is_null($request->toDate)) {
                    return redirect()->back()->with(['message2' => 'add start and end date first', 'class' => 'danger']);
                }
                // add service
                $data = $request->FinalScreeningSelect;
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
                            'options' => json_encode($n->options)
                        ]);
                    }
                }
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
            } elseif ($request->has("mFormSelect") && $request->mFormSelect) {
                Step::where('id', $request->sid)->update(['title' => $request->title, 'data' => $request->mFormSelect, 'description' => $request->description, 'from' => Carbon::parse($request->fromDate), 'to' => Carbon::parse($request->toDate)]);
            } else {
                Step::where('id', $request->sid)->update(['title' => $request->title, 'description' => $request->description, 'from' => Carbon::parse($request->fromDate), 'to' => Carbon::parse($request->toDate)]);
            }
            return redirect()->back()->with('message', 'Saved');
        } elseif ($request->tab_id == 1) {


            $request->validate([
                'organization_id2' => 'required|exists:organizations,id',
                'title' => 'required|string|max:250',
                'description' => 'required|string|max:2000',
                'start' => 'required|date|before:end',
                'end' => 'required|date|after:start',
            ]);
            $private = null;
            if ($request->private) {
                $private = sha1(now());
            }
            $cycle = new Cycle();
            $cycle->organization_id = $request->organization_id2;
            $cycle->title = $request->title;
            $cycle->description = $request->description;
            $cycle->start = Carbon::parse($request->start);
            $cycle->end = Carbon::parse($request->end);
            $cycle->private = $private;
            $cycle->allow_non_company = $request->has('allow_non_company') ? 1 : 0;
            $cycle->auto_approve_mentorship_requests = $request->has('auto_approve_mentorship_requests') ? 1 : 0;
            // Check if file is uploaded
            $programGuidelineFile = null;
            if ($request->hasFile('program-guideline-pdf')) {
                $programGuidelineFile = $request->file('program-guideline-pdf')->store('programs', 'public');
            }

            $cycle->program_guideline = $request->program_guideline;
            $cycle->program_guideline_file = $programGuidelineFile;

            // Check if file is uploaded
            $financialGuidelineFile = null;
            if ($request->hasFile('financial_policy_pdf')) {
                $financialGuidelineFile = $request->file('financial_policy_pdf')->store('financials', 'public');
            }

            $cycle->budget_allocation = $request->budget_allocation;
            $cycle->currency_type = $request->currency_type;
            $cycle->financial_guideline = $request->financial_policy;
            $cycle->financial_guideline_file = $financialGuidelineFile;

            // Handle file uploads
            $legalPolicyPath = null;
            if ($request->hasFile('legal_policy_pdf')) {
                $legalPolicyPath = $request->file('legal_policy_pdf')->store('legal_policies', 'public');
            }

            $delegationPdfPath = null;
            if ($request->delegation_option === 'yes' && $request->hasFile('delegation_pdf')) {
                $delegationPdfPath = $request->file('delegation_pdf')->store('delegations', 'public');
            }

            $cycle->legal_guideline = $request->legal_policy;
            $cycle->legal_guideline_file = $legalPolicyPath;
            $cycle->delegation_file = $delegationPdfPath;
            $cycle->save();

            if (auth()->user()->hasRole('OrganizationManager')) {

                $admin = User::role('Admin')->orderBy('id')->first();
            
                if ($admin) {
                    Mail::to($admin->email)->send(new ProgramCreatedNotification($cycle, $admin));
            
                    $message = new Message();
                    $message->name = auth()->user()->first_name_en . ' ' . auth()->user()->last_name_en;
                    $message->internal_id = $admin->id;
                    $message->email = auth()->user()->email; 
                    $message->subject = 'New Program Created';
                    $message->message = "A new program titled \"{$cycle->title}\" has been created by " . auth()->user()->name . ".";
                    $message->user_id = auth()->id(); 
                    $message->save();
            
                    $messenger = new Messenger();
                    $messenger->sender = auth()->id(); 
                    $messenger->receiver = $admin->id; 
                    $messenger->subject = 'New Program Created';
                    $messenger->message = "A new Program Titled \"{$cycle->title}\" has been created.";
                    $messenger->read = 0; 
                    $messenger->messgeId = $message->id; 
                    $messenger->save();
                }
            }
            return redirect()->back()->with('message', 'Saved');
        } else {
            // Show Cycle
            $cycles = Cycle::where('id', $id)->first()->toArray();

            $cycles['basename_guideline'] = basename($cycles['program_guideline_file']);

            return response()->json($cycles);
        }
    }


    function view($c, Request $request)
    {
       
        $cycle2 = Cycle::where('id', $c)->first();
        if($cycle2->allow_non_company == 1){
           
            if(!Auth::user()->HaveCompany){
                $user = Auth::user();
                $result = new Company();
                $result->name_en = $user->first_name_en." ".$user->last_name_en;
                $result->name_ar = $user->first_name_ar." ".$user->last_name_ar;
                $result->address_en = $user->address ?? 'Not Set';
                $result->address_ar = $user->address ?? 'Not Set';
                $result->save();
                User::where('id', $user->id)->update(['company_id' => $result->id, 'HaveCompany' => 1]);
            }
            }
        
        $private = Cycle::where('private', $c)->first();

        if ($private == null) {
            $private = Cycle::where('id', $c)->first();
            $c = $private->id;
        } else {
            $c = $private->id;
        }
      $user = User::where('id', Auth::user()->id)->first();
        // Check Company ---> forward to profile
        if (!$user->HaveCompany) {
            return redirect()->route('user.profile_Edit')->with(['msg' => 'You need to enable Have a company first', 'class' => 'danger']);
        }
        if (!$user->company) {
            return redirect()->route('company.profile_view_mine')->with(['msg' => 'You need to add your company details first', 'class' => 'danger']);
        }
        // Check enrolled in Cycle
        if ($user->company->cycle && $user->company->cycle != $c) {
            return redirect()->back()->with(['msg' => 'You already enrolled in a cycle.', 'class' => 'danger']);
        }
        //Check private
        $private = Cycle::findOrFail($c);
        //         if ($private->private==null) {

        //             return redirect(route('index.cycle',$c))->with(['message2'=>'هذا البرنامج خاص']);
        // //            abort(404);
        //         }

        // Check STEPS
        if (!$user->company->cycle || $user->company->cycle == $c) {
            $step = Step::where('cycle_id', $c)->orderBy('arr')->first();
            if ($step->stype != 1) {
                return redirect()->back()->with(['msg' => 'The selected cycle doesn\'t contain a data form.', 'class' => 'danger']);
            }
            if ($step->to < now()->format('Y-m-d')) {
                return redirect()->back()->with(['msg' => 'Error can\'t open form, Registration timed out.', 'class' => 'danger']);
            }

            $id = $step->id;
            $field_map_ids = Formfield::where('step_id', $id)->select('id')->pluck('id')->toArray();
            $answers = [];
            $data = [
                'title' => $step->title,
                'fields' => $step->fields()->orderBy('form_fields.id', 'asc')->get(),
                'form_id' => $step->id,
                'field_ids' => implode(",", $field_map_ids)
            ];
            if ($user->company->step == $id) {
                $answers = Formfield::join('form_data', 'form_fields.id', 'form_data.field_id')->where('user_id', Auth::id())->orderBy('id', 'ASC')->select('answer')->pluck('answer')->toArray();
            }
            return view('admin.settings.cycles.form', $data)->with('step', $step)->with('c', $c)->with('answers', $answers);
        }
    }

    function my($id)
    {
        $step = Step::where('stype', 1)->where('id', $id)->first();
        if (!$step) {
            return redirect()->back()->with(['msg' => 'Error opening the selected form.', 'class' => 'danger']);
        }
        //        if ($step->to < now()->format('Y-m-d')){return redirect()->back()->with(['msg' => 'Error can\'t open form, step timed out.', 'class' => 'danger']);}
        $id = $step->id;
        $field_map_ids = Formfield::where('step_id', $id)->select('id')->pluck('id')->toArray();
        $answers = [];
        $data = [
            'title' => $step->title,
            'fields' => $step->fields()->orderBy('form_fields.id', 'asc')->get(),
            'form_id' => $step->id,
            'field_ids' => implode(",", $field_map_ids)
        ];
        //        if (Auth::user()->company->step==$id) {
        $answers = Formfield::join('form_data', 'form_fields.id', 'form_data.field_id')->where('user_id', Auth::id())->orderBy('id', 'ASC')->select('answer')->pluck('answer')->toArray();
        //        }
        return view('admin.settings.cycles.form', $data)->with('step', $step)->with('c', $step->cycle_id)->with('answers', $answers);
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
            } else {
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
        //dd("ss");
        // gather the form data as field_name => [ label, data ]
        foreach ($field_map_ids as $fid) {

            $field_options = Formfield::findOrFail($fid)->options;
            $field_name = 'field_' . $fid;

            $uid = Auth::id();
            $cid = Auth::user()->company->id;

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
                'comments' => '{}'
            ]);
        }

        $data =[];
        $user = Auth::user();
		$name = $user->first_name_en . ' ' . $user->last_name_en;
        
        $data = [
            'name' => $name,
            'email' => $user->email,
            'step' => $user->company->stepdata->title,
            'cycle' => $user->company->cycledata->title,
        ];
        
        // إرسال الإشعار
        if( !in_array(env('APP_ENV', 'local'), ['local']) )
        {
            try {
                $user->notify(new StartupJoiningNotification($data));
                log::info('Sent notification to ' . $user->email);
            } catch (\Throwable $th) {
                throw $th;
            }

            $phoneNumber = $user->country_code_w_a . $user->whatsapp;
					$Massage = "Dear " . $user->name . ",
Congratulations! You have successfully joined the" . $data['cycle'] .", 
We are excited to have you on board and look forward to supporting your journey.
Thanks from " . env('APP_NAME');
					$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
					Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
        }

            
        return redirect(url('/dashboard'))->with(['msg' => 'CycleStoredSuccessfully', 'class' => 'success']);
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

}
