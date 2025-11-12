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
        $organization_id=auth()->user()->organization_id;
        $steps = Step::when(!is_null($organization_id),function ($q) use($organization_id){
            return $q->whereHas('cycle',function ($query) use ($organization_id){
                $query->where('organization_id',$organization_id);
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
            $cstep = Step::where('stype', 5)->where('id', $id)->first();
        } else {
            $cstep = Step::where('stype', 5)->where('id', $id)->first();
        }
        $companies = Company::where('step',$id);
        if($request->judge_id != 0){
            $list = Finalscreening::whereJsonContains('judges', (string) $request->judge_id)->get();
            $companies = $companies->whereIn('id',$list->pluck('company_id'));
        }
        if($request->startup_id != 0){
            $companies = $companies->where('id',$request->startup_id);
        }

        $companies = $companies->get();
        $check = ScreeningEvaluation::where('step_id', $id)->first();
		if( empty( $check ) )
		{
			$evaluation = new ScreeningEvaluation();
			$evaluation->step_id = $id;
			$evaluation->save();
		}
		$evaluationForm = ScreeningEvaluation::getEvaluationData( $id );

        return view('admin.settings.final.index', compact('cstep', 'judges', 'steps', 'id', 'companies', 'evaluationForm'));
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