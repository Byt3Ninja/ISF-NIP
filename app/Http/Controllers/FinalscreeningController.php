<?php

namespace App\Http\Controllers;

use App\Step;
use App\User;
use App\Company;
use App\FinalData;
use App\Formfield;
use Carbon\Carbon;
use App\Finalscreening;
use App\ScreeningEvaluation;
use App\ScreeningEvaluationResults;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Notifications\TemplateEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\Factories\OnlineMeetingFactory;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Notifications\WelcomeJudgerNotification;

class FinalscreeningController extends Controller
{
    protected $meetingService;
    public function __construct()
    {
        $this->meetingService =  OnlineMeetingFactory::create();
    }

    public function index(Request $request, $id = 0)
    {
        $organizationId = auth()->user()->organization_id;

        /** -------------------------
         * Base steps query with filters
         * ------------------------- */
        $stepsQuery = Step::with(['cycle.organization'])
                ->when($organizationId, function ($q) use ($organizationId) {
                        $q->whereHas('cycle', fn ($c) =>
                                $c->where('organization_id', $organizationId)
                        );
                })
                ->where('stype', 5);

        // Apply filters
        if ($request->filled('search')) {
                $search = $request->search;
                $stepsQuery->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                                ->orWhereHas('cycle', function ($c) use ($search) {
                                        $c->where('title', 'like', "%{$search}%");
                                })
                ->orWhereHas('cycle.organization', function ($o) use ($search) {
                        $o->where('name_en', 'like', "%{$search}%")
                                ->orWhere('name_ar', 'like', "%{$search}%");
                });
                });
        }

        if ($request->filled('status')) {
                $today = now()->toDateString();
                switch ($request->status) {
                        case 'upcoming':
                                $stepsQuery->where('from', '>', $today);
                                break;
                        case 'in_progress':
                                $stepsQuery->where('from', '<=', $today)->where('to', '>=', $today);
                                break;
                        case 'completed':
                                $stepsQuery->where('to', '<', $today);
                                break;
                }
        }

        if ($request->filled('program')) {
                $stepsQuery->whereHas('cycle', function ($c) use ($request) {
                        $c->where('id', $request->program);
                });
        }

        if ($request->filled('judge')) {
                $stepsQuery->where(function ($q) use ($request) {
                        // Check if judge is in the JSON list field
                        $q->whereRaw("JSON_CONTAINS(list, ?)", [json_encode($request->judge)])
                                ->orWhereRaw("JSON_CONTAINS(list, ?)", [json_encode([$request->judge])])
                                ->orWhereRaw("JSON_CONTAINS(list, ?)", [$request->judge]);
                });
        }

        if ($request->filled('startup')) {
                $stepsQuery->whereHas('companies', function ($c) use ($request) {
                        $c->where('id', $request->startup);
                });
        }

        $steps = $stepsQuery->orderBy('from')->get();

        /** -------------------------
         * Preload all related data
         * ------------------------- */
        $stepIds   = $steps->pluck('id');
        $cycleIds  = $steps->pluck('cycle_id')->unique();

        $judges = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('roles.default_for', 'judger')
                ->select('users.id', 'users.first_name_en', 'users.last_name_en')
                ->get()
                ->keyBy('id');

        $finalscreenings = Finalscreening::whereIn('step_id', $stepIds)
                ->get()
                ->groupBy('step_id');

        $companies = Company::whereIn('step', $stepIds)
                ->get()
                ->groupBy('step');

        $forms = Step::whereIn('cycle_id', $cycleIds)
                ->where('stype', 1)
                ->get()
                ->keyBy(fn ($f) => "{$f->cycle_id}-{$f->arr}");

        /** -------------------------
         * Build finalScreeningSteps
         * ------------------------- */
        $finalScreeningSteps = $steps->map(function ($step) use (
                $judges,
                $finalscreenings,
                $companies,
                $forms
        ) {
                $judgersList = collect(json_decode($step->list, true) ?? [])
                        ->map(fn ($j) => is_array($j) ? array_map('intval', $j) : [(int) $j]);

                /** Collect judges for step */
                $judgeIds = $judgersList->flatten()->unique();

                $judgesForStep = $judgeIds
                        ->map(fn ($id) => $judges->get($id))
                        ->filter()
                        ->map(fn ($j) => (object) [
                                'id'   => $j->id,
                                'name' => "{$j->first_name_en} {$j->last_name_en}",
                        ])
                        ->values();

                /** Companies */
                $companiesForStep = ($companies[$step->id] ?? collect())
                        ->map(fn ($c) => (object) [
                                'id'   => $c->id,
                                'name' => $c->name_en,
                        ])
                        ->values();

                /** Form */
                $formKey = "{$step->cycle_id}-" . ($step->arr - 1);
                $form    = $forms->get($formKey);

                /** Screening type */
                $screeningType = match ($step->data) {
                        'onetoall'     => 'one-to-all',
                        'grouptoall'   => 'group-to-all',
                        'grouptogroup' => 'group-to-group',
                        default        => 'one-to-all',
                };

                return (object) [
                        'id'            => $step->id,
                        'title'         => $step->title,
                        'description'   => $step->description ?? '',
                        'program'       => $step->cycle
                                ? (object) ['id' => $step->cycle->id, 'title' => $step->cycle->title]
                                : null,
                        'organization'  => $step->cycle?->organization
                                ? (object) [
                                        'id'   => $step->cycle->organization->id,
                                        'name' => $step->cycle->organization->name,
                                ]
                                : null,
                        'start_date'    => optional($step->from)->format('Y-m-d'),
                        'end_date'      => optional($step->to)->format('Y-m-d'),
                        'screening_type'=> $screeningType,
                        'judges'        => $judgesForStep,
                        'companies'     => $companiesForStep,
                        'form'          => $form
                                ? (object) ['id' => $form->id, 'title' => $form->title]
                                : null,
                ];
        });

        return view('admin.settings.final.index', compact('finalScreeningSteps'));
    }

    public function show($id = 0)
    {
        $organization_id = auth()->user()->organization_id;
        $steps = Step::when(!is_null($organization_id), function ($q) use ($organization_id) {
            return $q->whereHas('cycle', function ($query) use ($organization_id) {
                $query->where('organization_id', $organization_id);
            });
        })->where('stype', 5)->orderBy('from')->get();

        // $judges = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 7)->get();
		$judges = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
			->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
			->where('roles.default_for', 'judger')
			->select('users.*')
			->get();

        if ($id == 0) {
            $id = $steps->pluck('id')->first();
            $cstep = Step::when(!is_null($organization_id), function ($q) use ($organization_id) {
                return $q->whereHas('cycle', function ($query) use ($organization_id) {
                    $query->where('organization_id', $organization_id);
                });
            })->where('stype', 5)->where('id', $id)->first();
        } else {
            $cstep = Step::when(!is_null($organization_id), function ($q) use ($organization_id) {
                return $q->whereHas('cycle', function ($query) use ($organization_id) {
                    $query->where('organization_id', $organization_id);
                });
            })->where('stype', 5)->where('id', $id)->first();

            if( empty( $cstep ) ) {
                return abort(404);
            }
        }

        if ($cstep) {
            $form = Step::when(!is_null($organization_id), function ($q) use ($organization_id) {
                return $q->whereHas('cycle', function ($query) use ($organization_id) {
                    $query->where('organization_id', $organization_id);
                });
            })->where('cycle_id', $cstep->cycle_id)->where('stype', 1)->where('arr', $cstep->arr - 1)->first();
        } else {
            $form = collect([]);
        }
        $judgers_count = 0;
        $cList = collect(json_decode($cstep?->list, true) ?? []);
        if( in_array( $cstep?->data, ['onetoall', 'grouptoall']) ) { $judgers_count = $cList->count(); }
        else
        {
            $judgers_count = $cList->flatten()->unique()->count();
        }
        $svgs = ['notebook', 'search', 'idea', 'comments', 'trophy'];

        $judgers_list = collect( json_decode($cstep?->list) )->map(function($user) {
            if( is_array( $user ) ) { $user = array_map('intval', $user); } else { $user = intval( $user ); }
            return $user;
        });

        $finalscreenings = collect([]);
        if( $cstep ) { $finalscreenings = Finalscreening::where('step_id', $cstep->id)->get(); }

        foreach( $judgers_list as $panel_id => $judgers_ids )
        {
            $users_list = $finalscreenings->where('panel', $panel_id)->map(function($finalscreening) {
                if( is_array( $finalscreening->companies ) ) { return $finalscreening->companies; }
                else { return collect(json_decode($finalscreening->companies, true) ?? []); }
            })->flatten()->unique()->values()->all();

            $judgers_list[$panel_id] = ['judgers' => $judgers_ids, 'companies' => $users_list ];
        }
        $check = ScreeningEvaluation::where('step_id', $id)->first();
        if( empty( $check ) )
        {
            $evaluation = new ScreeningEvaluation();
            $evaluation->step_id = $id;
            $evaluation->save();
        }
        $companies = [];
        $evaluationForm = ScreeningEvaluation::getEvaluationData( $id );
        if( $cstep ) { $companies = Company::whereIn('step', [$form?->id ?? 0, $cstep?->id ?? 0])->get(); } else { $cstep = []; }
        return view('admin.settings.final.show', compact('cstep', 'judgers_list', 'judgers_count', 'companies', 'judges', 'form', 'steps', 'id', 'svgs', 'evaluationForm'));
    }

    public function update(Request $request)
    {
        if ($request->step) {
            $list = $request->judges;
           // dd();
            $companies = $request->users;
            
            if (!$companies or !$list) {return Redirect::back()->with('message2', 'You need to select judges and companies to update');}
            foreach ($companies as $company) {
                Finalscreening::where('company_id',$company)->where('step_id', $request->step)->delete();
                Finalscreening::insert(['company_id'=>$company,'step_id'=>$request->step, 'judges'=>json_encode($list)]);

            }
            foreach ($list as $judge) {
                
                $result = User::findOrFail($judge);
                $name = $result->first_name_en . ' ' . $result->last_name_en;
                $email = $result->email;
                
                $data = [
                    'role'=>'Judger',
                    'Type' => 'Final_Screening',
                    'name' => $name,
                    'email' => $email,
                    'start_date' => '',
                    'end_date' => '',
                ];
                // إرسال الإشعار
                if( !in_array(env('APP_ENV', 'local'), ['local']) )
                {
                    try {
                        $result->notify(new WelcomeJudgerNotification($data));
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }
            


            return Redirect::back()->with('message', 'Saved');
        } else {
            return Redirect::back()->with('message2', 'error');
        }
    }

    public function view($sid, $cid)
    {
        // Insure company belong to Judger first
        $myid = Auth::id();
        $fs = Finalscreening::where('company_id',$cid)->where('step_id',$sid)->where('judges', 'like', "%\"{$myid}\"%")->get();
        if (!$fs) { return Redirect::back()->with('message2', 'error');}
        // Check step
        $step = Step::findOrFail($sid);
        if (!$step) {return Redirect::back()->with('message2', 'error');}
        $field_map_ids = Formfield::where('step_id',$sid)->where('builder_forms_id', $step->data)->orderBy('id')->select('id')->pluck('id')->toArray();
        $data = [
            'title' => $step->title,
            'fields' => $step->fields()->where('builder_forms_id', $step->data)->orderBy('form_fields.id', 'asc')->get(),
            'form_id' => $step->id,
            'field_ids' => implode(",", $field_map_ids)
        ];
        $answers = Formfield::join('final_data', 'form_fields.id', 'final_data.field_id')->where('judge_id',$myid)->
        where('company_id',$cid)->orderBy('form_fields.id', 'ASC')->select('answer')->pluck('answer')->toArray();
        $company = Company::findOrFail($cid);
        return view('admin.settings.final.form', $data)->with('step',$step)->with('company', $company)->with('answers',$answers);
    }

    public function save(Request $request)
    {
        $id = $request->id;
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:steps,id',
            'field_ids' => 'required|string',
        ]);
        abort_if($validator->fails(), 422, "Data error");
        // generating validation rules based on dynamic field config data
        $field_map_ids = explode(",", $request->input('field_ids'));
        $field_required_rules = collect($field_map_ids)->mapWithKeys(function($id){
            $field_options = Formfield::findOrFail($id)->options;
            if($field_options->validation->required == 1) {
                $rules = ['required'];
                if(isset($field_options->type) && $field_options->type == "email") $rules[] = 'email';
                return [
                    'field_'. $id => implode("|", $rules)
                ];
            }
            else {
                return ['field_'. $id => 'nullable'];
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
        $uid = Auth::id();
        $cid = $request->cid;
        foreach($field_map_ids as $fid){
            $field_options = Formfield::findOrFail($fid)->options;
            $field_name = 'field_'. $fid;
            FinalData::where('field_id',$fid)->where('judge_id',$uid)->where('company_id',$cid)->delete();
            FinalData::updateOrCreate(['field_id'=>$fid, 'judge_id'=>$uid, 'company_id'=>$cid, 'step_id'=>$id, 'question'=>$field_options->label,'answer'=>$request->input($field_name)]);
        }
        return Redirect::back()->with(['msg' => 'StoredSuccessfully','class'=>'success']);
    }

    public function addzoom(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'period' => 'required|integer|min:30'
        ],[
            'date.required' => __('admin.Date_is_required'),
            'date.date' => __('admin.Date_must_be_a_valid_date'),
            'period.required' => __('admin.Period_is_required'),
            'period.integer' => __('admin.Period_must_be_an_integer'),
            'period.min' => __('admin.Period_must_be_at_least_30_minutes'),
        ]);
        if ($request->period) {
            $topic = "Final Screening (C" . $request->company . ')';
            $data = ['topic' => $topic,
             "start_time" => $request->date,
              'duration' => $request->period,
               'host_video' => 1,
                'participant_video' => 1
            ];
            
            $onlineMeeting = $this->meetingService->createMeeting($data);
            if (!$onlineMeeting['success']) {
                return back()->with(['message2' => "API Error, can't create zoom meeting."]);
            }
            
            $finalScreening = Finalscreening::where('step_id', $request->step)->where('company_id', $request->company)->first();
            
            $finalScreening->update([
                'datetime' => $request->date,
                'duration' => $request->period,
                'zoom_id' => $onlineMeeting['data']['id'],
                'start_url' =>  $onlineMeeting['data']['start_url'],
                'join_url' =>  $onlineMeeting['data']['join_url'],
                'zoom_passcode' => $onlineMeeting['data']['passcode'] ?? null,
            ]);
                
            $startup_owner = Company::findOrFail($request->company)->user;
            $startup_owner_name = $startup_owner->name;
                $start_date = $finalScreening->datetime;
                $duration = $request->period;
                $end_dateCarbon = Carbon::parse($start_date)->addMinutes($duration);
                $end_date = $end_dateCarbon->toDateTimeString(); // Format: 2025-02-10 13:00:00

            $judges_names = '';
            foreach ($finalScreening->judges as $judge) {

                // $judge_name = $judge->name;
                // $user = new User();
                // $user->email = $judge->email;
                // $user->id = $judge->id;

                // $MSG = new TemplateEmail;
                // $MSG->Message=["New Final Screen session - VI portal","Dear ".$judge_name.",","Kindly note you have a New Final Screen session with '".$startup_owner_name."', Session will start:".$request->date.
                //     ", Check your account to join the session ","/dashboard"];

                // $user->notify($MSG);
                // $judges_names .= $judge->name .', ';

                $result = User::findOrFail($judge);
                $name = $result->first_name_en . ' ' . $result->last_name_en;
                $email = $result->email;
                
                $data = [
                       'role'=>'Judger',
                    'Type' => 'Final_Screening',
                    'name' => $name,
                    'email' => $email,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                ];
                // إرسال الإشعار
                if( !in_array(env('APP_ENV', 'local'), ['local']) )
                {
                    try {
                        $result->notify(new WelcomeJudgerNotification($data));
                    } catch (\Throwable $th) {
                        // throw $th;
                    }
                }
            }
            
            
            /// Notify Startup Owner
            $startup_owner = User::findOrFail($startup_owner->id);
            
            $startup_owner_name = $startup_owner->name;
            
            $user = new User();
            $user->email = $startup_owner->email;
            $user->id = $startup_owner->id;
            $MSG = new TemplateEmail;
            $MSG->Message=["New Final Screen session - VI portal","Dear ".$startup_owner_name.",","Kindly note you have a New Final Screen session with '".$judges_names."', Session will start:".$request->date.
                ", Check your account to join the session ","/dashboard"];
            if( !in_array(env('APP_ENV', 'local'), ['local']) || env('APP_NAME', 'incubator') != "BIAC" )
            {
                try {
                    $user->notify($MSG);
                } catch (\Throwable $th) {
                    // throw $th;
                }
            }
            /// End of mail send
            
            return Redirect::back()->with('message', 'Meeting_added_successfully');
        } else {return Redirect::back()->with('message2', 'Error');}
    }
    public function results(Request $request, $id = 0)
    {
        $organization_id=auth()->user()->organization_id;
        $steps = Step::when(!is_null($organization_id),function ($q) use($organization_id){
            return $q->whereHas('cycle',function ($query) use ($organization_id){
                $query->where('organization_id',$organization_id);
            });
        })->where('stype', 5)->orderBy('from')->get();

        if ($id == 0) {
            $id = $steps->pluck('id')->first();
            $cstep = Step::where('stype', 5)->where('id', $id)->first();
        } else {
            $cstep = Step::where('stype', 5)->where('id', $id)->first();
        }
        $companies = Company::where('step',$id)->get();
        $c =  $cj = null;
        if ($request->c) {
            $c = Company::findOrFail($request->c);
            $cj = FinalData::where('company_id',$c->id)->where('step_id', $id)->distinct('judge_id')->get(['judge_id']);
        }
        $check = ScreeningEvaluation::where('step_id', $id)->first();
		if( empty( $check ) )
		{
			$evaluation = new ScreeningEvaluation();
			$evaluation->step_id = $id;
			$evaluation->save();
		}
		$evaluationForm = ScreeningEvaluation::getEvaluationData( $id );
        $metadata = ScreeningEvaluationResults::metadata( $id );
        $companies = $metadata['companies']->where('step', $id)->values();
		$_steps = $metadata['_steps'];
		$judges = $metadata['judges'];
		$evaluation = $metadata['evaluation'];
		$results = $metadata['results'];
		$_companies = $metadata['_companies']->where('step', $id)->values();

        $judges = $judges->map(function( $item ) {
            $item->companies_ids = collect($item->companies)->pluck('id')->toArray();
            return $item;
        });
        return view('admin.settings.final.results', compact('cstep', 'judges', 'steps', '_steps', 'id', '_companies', 'companies', 'evaluation', 'results', 'c', 'cj', ));
    }

    public function delsession($id, Request $request)
    {
        // $zoomid = TrainingSession::findOrFail($id)->zoom_id;
        if($request)
        {
            // if ($zoomid)
            // {
            //     $zoom = $this->delete($zoomid);
            //     if (!$zoom['success']) { return response()->json(['state' => 'failed'], 500); }
            // }
            Finalscreening::findOrFail($id)->delete();
            return response()->json(['state' => 'success'], 200);
        } else {
            return response()->json(['state' => 'failed'], 500);
        }
    }
}
