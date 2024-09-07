<?php

namespace App\Http\Controllers;

use App\Company;
use App\Factories\OnlineMeetingFactory;
use App\Jobs\ExportTresultData;
use App\Notifications\ChangeCycle;
use App\Notifications\TemplateEmail;
use App\Notifications\TrainerEmail;
use App\Step;
use App\Training;
use App\TrainingCompleted;
use App\TrainingContent;
use App\TrainingSession;
use App\User;
use App\Quizzes;
use App\QuizResults;
use App\QuizResponses;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Storage;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;
use App\Traits\ZoomMeetingTrait;

class TrainingController extends Controller
{
//     use ZoomMeetingTrait;
//
//    const MEETING_TYPE_INSTANT = 1;
//    const MEETING_TYPE_SCHEDULE = 2;
//    const MEETING_TYPE_RECURRING = 3;
//    const MEETING_TYPE_FIXED_RECURRING_FIXED = 8;
    protected $meetingService;
    public function __construct()
    {
        $this->meetingService =  OnlineMeetingFactory::create();
    }


    public function exportTresult(Request $request)
    {
        // تحقق من أن البريد الإلكتروني موجود
       
        $data2 = [
            'step' => $request->input('step'),
            'location'=> $request->input('location'),
            'userEmail' => auth()->user()->email
        ];

        // إرسال وظيفة (Job) لتحميل البيانات وإرسال البريد
        ExportTresultData::dispatch($data2);

        return response()->json(['message' => 'Export process started. You will receive an email with the download attached.']);
    }


    public function changAllTL(Request $request ){

        

        $this->validate($request, [
            
            'checkget' => 'required|integer',
            'compid' => 'required|array',
            'compid.*' => 'required|integer',
            
        ]);
        $checkget = $request->input('checkget');
        $compid = $request->input('compid');
        foreach ($compid as $index => $id) {
           
            $company = Company::where('id',$id)->first();
            $data['last_step']= Step::findOrFail($checkget)->title;
            if ($checkget==0) {
                $by = Auth::id();
            } else {
                $cstep = Step::findOrFail($checkget);
                if ($cstep->arr > 1) {$approved=1;$by=Auth::id();}
                $data['new_step']=$cstep->title;
            }
            $update = $company->update(['step'=>$checkget, 'approved'=>$approved, 'approved_by'=>$by]);
            if ($checkget!=0) {
            /// notify Company Owner via Mail
            $company_owner = $company->user;
            $data['owner_name_en'] = $company_owner->first_name_en . " " . $company_owner->last_name_en;
            $data['owner_name_ar'] = $company_owner->first_name_ar . " " . $company_owner->last_name_ar;
            $data['company_name_ar'] = $company_owner->company->name_ar;
            $data['company_name_en'] = $company_owner->company->name_en;
            $data['organization_name_ar'] = $cstep->cycle->organization->name_ar;
            $data['organization_name_en'] = $cstep->cycle->organization->name_en;

            $mail = new ChangeCycle($data);
            // $MSG = new TemplateEmail;
            // $MSG->Message=["Step changed - VI portal","Dear ".$rfullname.",","Your step into Cycle changed, check your account now.","/dashboard"];
            $company_owner->notify($mail);
             }
            }
            /// End of mail send
            if ($update) {
            return response()->json(['state' => 'success'], 200);
            } else {
            return response()->json(['state' => 'failed'], 500);
            }



        return response()->json(['state' => 'success'], 200);
    }



    public function all($id=0) {
        $this->preProcessingCheck('edit_cycles');
        $courses = Training::orderBy('created_at','DESC')->get();
        if ($id==0 && count($courses)) {
            $id = $courses->pluck('id')[0];
        }
        $contents = TrainingContent::where('training_id',$id)->orderBy('arr','ASC')->get();
        return view('admin/settings/training/all', compact('courses','id','contents'));
    }
    public function edit($id) {
        $this->preProcessingCheck('edit_cycles');
        $courses = Training::findOrFail($id);
        return response()->json($courses);
    }
    public function store(Request $request) {
        $this->preProcessingCheck('edit_cycles');
        if ($request->hasFile('image')) {
            $img_url = $request->file('image');
            $img_url = $img_url->store('training', ['disk' => 'public_uploads']);
            Image::make(Storage::disk('public_uploads')->path($img_url))->resize(null, 350, function($con) {$con->aspectRatio();})->save();
            Training::updateOrCreate(['id'=>$request->id],['title'=>$request->title,'dsc'=>$request->dsc,'user_id'=>Auth::id(),'image'=>$img_url]);
        } else {
            Training::updateOrCreate(['id'=>$request->id],['title'=>$request->title,'dsc'=>$request->dsc,'user_id'=>Auth::id()]);
        }
        return back()->with(['class'=>'success','message'=>'Saved']);
    }

    public function delete($id, Request $request){
        $this->preProcessingCheck('edit_cycles');
        if ($request) {
         Training::where('id',$id)->delete();
         TrainingContent::where('training_id',$id)->delete();
         return response()->json(['state' => 'success']);
            } else {
         return response()->json(['state' => 'failed'], 500);
        }
    }

    public function addcontent($id) {
        $this->preProcessingCheck('edit_cycles');
        $check = Training::findOrFail($id);
        $arr = TrainingContent::where('training_id',$id)->orderBy('arr','ASC')->get(['title','arr']);
        if (count($arr)==0) {$arr=[];}
        if($check) {
            return view('admin/settings/training/add_content', compact('id','arr'));
        } else {
            return back()->with(['class'=>'danger','message'=>'must add training course first.']);
        }
    }

    public function editcontent($id) {
        $this->preProcessingCheck('edit_cycles');
        $content = TrainingContent::findOrFail($id);
        $arr = TrainingContent::where('training_id',$content->training_id)->orderBy('arr','ASC')->get(['title','arr']);
        if($content) {
            return view('admin/settings/training/edit_content', compact('content','id','arr'));
        } else {
            return back()->with(['class'=>'danger','message'=>'Error, content not exist.']);
        }
    }
    public function viewcontent($id) {
        $this->preProcessingCheck('edit_cycles');
        $content = TrainingContent::findOrFail($id);
        if($content) {
            return view('admin/settings/training/view_content', compact('content'));
        } else {
            return back()->with(['class'=>'danger','message'=>'Error, content not exist.']);
        }
    }

    public function storecontent(Request $request) {
        $this->preProcessingCheck('edit_cycles');
        if ($request->tcontent) {
            $id = $request->id ?? null;
            $arr = $i = 1;
            if ($request->arr) {
                $arr = $request->arr;
                $old = TrainingContent::where('training_id',$request->training_id)->orderBy('arr','ASC')->get();
                foreach ($old as $item) {
                    if($item->arr == $arr){$i++;}
                    TrainingContent::where('id',$item->id)->update(['arr'=>$i++]);
                }
            }
            TrainingContent::updateOrCreate(['id'=>$id],['title'=>$request->title,'content'=>$request->tcontent,'training_id'=>$request->training_id,'user_id'=>Auth::id(),'arr'=>$arr]);
            return redirect('/dashboard/training/all/'. $request->training_id)->with(['class'=>'success','message'=>'Saved']);
        }
        return back()->withInput()->with(['class'=>'danger','message'=>'Error']);
    }

    public function delcontent($id, Request $request){
        $this->preProcessingCheck('edit_cycles');
        if ($request) {
            $removedid = TrainingContent::where('id',$id)->first(['training_id'])->training_id;
            TrainingContent::where('id',$id)->delete();
            /// Rearrange
            $old = TrainingContent::where('training_id',$removedid)->orderBy('arr','ASC')->get();
            foreach ($old as $key=>$item) {
                    TrainingContent::where('id',$item->id)->update(['arr'=>$key+1]);
                }
            return response()->json(['state' => 'success'], 200);
        } else {
            return response()->json(['state' => 'failed'], 500);
        }
    }
    public function upload(Request $request) {
        $this->preProcessingCheck('edit_cycles');
        $fileName = $request->file('file');
        $fileName = $fileName->store('photos', ['disk' => 'TrainingDisk']);
        return response()->json(['location'=> '/dashboard/training/'.$fileName]); //env('APP_URL'). full URL
    }

    public function my($id, Request $request)
    {
        $step = optional(Auth::user()->company)->step;
        $tr = Step::findOrfail($step);
        $c = $request->c ?? 0;
        if( $tr->stype == 4 )
        {
            $contents = TrainingContent::where('training_id', $id)->orderBy('arr')->get(['id','title','training_id']);
            $ccontent = TrainingContent::where('training_id', $id);
            $ccontent = $c == 0 ? $ccontent->orderBy('arr','ASC') : $ccontent->where('id', $c);
            $ccontent = $ccontent->first(['id','title','content']);

            if( count( $contents ) == 0 ) { return back()->with(['class'=>'danger','message'=>'Error, content not exist.']); }
            // save progress
            TrainingCompleted::updateOrInsert(['training_id' => $ccontent->id, 'user_id' => Auth::id()], ['course_id' => $id]);
            $quizzes = Quizzes::where('training_id', $id)->where('status', 'published')->get();
            return view('admin/clients/training', compact('contents','ccontent', 'quizzes'));
        } else { return back()->with(['class'=>'danger','message'=>'ErrorNoPermissions']); }
    }

    public function myQuiz($id, $quizId, Request $request)
    {
        $user = auth()->user();
        if( $user->hasRole(['Registered']) )
        {
            if( !$user->HaveCompany ) { return redirect()->route('my_profile')->with(['msg' => 'You need to enable "Have a company" first.', 'class' => 'danger']); }
            if( !$user->company ) { return redirect()->route('my_company')->with(['msg' => 'You need to add your company details first.', 'class' => 'danger']); }

            $company = $user->company;
            $cycleData = $company->cycledata;

            if( empty( $cycleData ) ) { return redirect()->route('dashboard')->with(['msg' => 'You need to join program first.', 'class' => 'danger']); }
            if( empty( $cycleData->organization_id ) ) { return redirect()->route('dashboard')->with(['msg' => 'You need to join program first.', 'class' => 'danger']); }

            $organization_id = $cycleData->organization_id;
            $quiz = Quizzes::where('status', 'published')->where('training_id', $id)->where('organization_id', $organization_id)->with(['training', 'myAttempts', 'questions'])->findOrFail( $quizId );
            $questions = $quiz->questions;
            $tags = $questions->flatMap(function($question) { return $question->tags; })->unique()->values()->toArray();
            $training = $quiz->training;
            if( empty( $training ) ) { return abort(404); }
            $available = true;
            $available_at = null;
            $allowActions = false;
            $time_remaining = null;
            $attempts = $quiz->myAttempts;
            foreach( $attempts as $index => $attempt )
            {
                if( !$attempt->status )
                {
                    if( $attempt->started_at->diffInMinutes( now() ) > $quiz->time_limit )
                    {
                        $attempt->status = true;
                        $attempt->ended_at = $attempt->started_at->copy()->addMinutes($quiz->time_limit);
                        $attempt->save();
                        $attempts[$index]->status = $attempt->status;
                        $attempts[$index]->ended_at = $attempt->ended_at;
                    }
                }
            }
            $active_now = null;
            $get_active = $attempts->where('status', false)->first();
            if( !empty( $get_active ) ) { $active_now = $get_active->id; }
            $failures = $attempts->where('score', '<', $quiz->passing_criteria);
            $success = $attempts->where('score', '>=', $quiz->passing_criteria);
            if( $failures->count() >= $quiz->attempts || $success->count() > 0 ) { $allowActions = true; }
            $available = now() > $quiz->start && now() < $quiz->end ? true : false;
            $completion_percentage = $training->progress()['percentage'];
            $available = $available && $completion_percentage >= 100 ? true : false;
            if( $available && $attempts->count() == 0 ) { $available = true; }
            else
            {
                $available = $available && $failures->count() < $quiz->attempts ? true : false;
                $available = $available && $success->count() == 0 ? true : false;
                $lastAttempt = $attempts->last();
                if( $lastAttempt && $failures->count() < $quiz->attempts )
                {
                    if( $lastAttempt->score < $quiz->passing_criteria )
                    {
                        $retryAfter = Carbon::createFromFormat('H:i', $quiz->retry_after);
                        $last = !empty( $lastAttempt->ended_at ) ? $lastAttempt->ended_at : $lastAttempt->updated_at;
                        $available_at = $last->addHours($retryAfter->hour)->addMinutes($retryAfter->minute);
                        $available = $available && now() >= $available_at ? true : false;
                    }
                }
            }
            if( $quiz->start > now() )
            {
                $diff = $quiz->start->diff( now() );
                $time_remaining = sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
            }
            elseif( !empty( $available_at ) && now() < $available_at )
            {
                $diff = $available_at->diff(now());
                $time_remaining = sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
            }
            return view('admin/clients/quiz_overview', compact('quiz', 'training', 'questions', 'tags', 'attempts', 'available', 'available_at', 'time_remaining', 'completion_percentage', 'allowActions', 'active_now'));
        } else { return abort(401); }
    }

    public function doQuiz($id, $quizId, Request $request)
    {
        $user = auth()->user();
        if( $user->hasRole(['Registered']) )
        {
            if( !$user->HaveCompany ) { return redirect()->route('my_profile')->with(['msg' => 'You need to enable "Have a company" first.', 'class' => 'danger']); }
            if( !$user->company ) { return redirect()->route('my_company')->with(['msg' => 'You need to add your company details first.', 'class' => 'danger']); }

            $company = $user->company;
            $cycleData = $company->cycledata;

            if( empty( $cycleData ) ) { return redirect()->route('dashboard')->with(['msg' => 'You need to join program first.', 'class' => 'danger']); }
            if( empty( $cycleData->organization_id ) ) { return redirect()->route('dashboard')->with(['msg' => 'You need to join program first.', 'class' => 'danger']); }

            $organization_id = $cycleData->organization_id;
            $quiz = Quizzes::where('status', 'published')->where('training_id', $id)->where('organization_id', $organization_id)->with(['training', 'myAttempts', 'questions'])->findOrFail( $quizId );
            $training = $quiz->training;
            if( empty( $training ) ) { return abort(404); }

            if( $request->has('response') && $request->has('answer') )
            {
                $response = QuizResponses::where('user_id', $user->id)->find( $request->input('response') );
                if( !empty( $response ) )
                {
                    $result = $response->result;
                    $time_limit = $result->started_at->copy()->addMinutes( $quiz->time_limit );
                    $remaining = $time_limit->diffInSeconds( now() );
                    if( $remaining > 0 )
                    {
                        $answer = $request->input('answer');
                        $question = $response->question;
                        if( $question->type == 'tfq' )
                        {
                            $answer = $answer === "true" || $answer === true || $answer === 1 || $answer === '1' ? true : false;
                            $response->response = $answer;
                            $response->status = $answer == $question->correct;
                            $response->save();
                        }
                        elseif( $question->type == 'mcq' )
                        {
                            $correct_answers = $question->answers->where('status', true)->map(function( $item ) {
                                return md5( $item['text'] . '+' . $item['status'] );
                            })->values()->toArray();

                            $response->response = $answer;
                            $response->status = in_array( $answer, $correct_answers );
                            $response->save();
                        }
                        $quiz = Quizzes::where('status', 'published')->where('training_id', $id)->where('organization_id', $organization_id)->with(['training', 'myAttempts', 'questions'])->findOrFail( $quizId );
                    }
                    else
                    {
                        $result->status = true;
                        $result->ended_at = $time_limit->format('Y-m-d H:i:s');
                        $result->save();
                        return redirect()->route('quiz.overview', ['id' => $id, 'quiz_id' => $quizId])->with(['msg' => __('admin.quizTimesUp'), 'class' => 'danger']);
                    }
                }
            }
            $questions = $quiz->questions;
            if( $quiz->shuffle ) { $questions = $questions->shuffle(); }
            $attempts = $quiz->myAttempts;
            foreach( $attempts as $index => $attempt )
            {
                if( !$attempt->status )
                {
                    if( $attempt->started_at->diffInMinutes( now() ) > $quiz->time_limit )
                    {
                        $attempt->status = true;
                        $attempt->ended_at = $attempt->started_at->copy()->addMinutes($quiz->time_limit);
                        $attempt->save();
                        $attempts[$index]->status = $attempt->status;
                        $attempts[$index]->ended_at = $attempt->ended_at;
                    }
                }
            }
            $failures = $attempts->where('score', '<', $quiz->passing_criteria);
            $success = $attempts->where('score', '>=', $quiz->passing_criteria);
            if( $success->count() > 0 || $failures->where('status', true)->count() >= $quiz->attempts ) { return redirect()->route('quiz.overview', ['id' => $id, 'quiz_id' => $quizId])->with(['msg' => __('admin.quizAlreadyCompleted'), 'class' => 'danger']); }

            $result = $attempts->where('status', false)->first();
            if( !empty( $result ) ) { $responses = $result->responses->where('user_id', $user->id); }
            else
            {
                $result = new QuizResults;
                $result->quiz_id = $quiz->id;
                $result->user_id = $user->id;
                $result->score = 0;
                $result->started_at = now()->format('Y-m-d H:i:s');
                $result->ended_at = null;
                $result->status = false;
                $result->save();

                $responses = [];
                foreach( $questions as $question )
                {
                    $responses[] = [
                        'result_id' => $result->id,
                        'quiz_id' => $quiz->id,
                        'question_id' => $question->id,
                        'user_id' => $user->id,
                        'response' => null,
                        'status' => false,
                    ];
                }
                QuizResponses::insert( $responses );
                $responses = QuizResponses::where('quiz_id', $quiz->id)->where('user_id', $user->id)->where('result_id', $result->id)->get();
            }
            $time_limit = $result->started_at->copy()->addMinutes( $quiz->time_limit );
            $remaining = $time_limit->diffInSeconds( now() );

            if( $remaining > 0 )
            {
                $response = $responses->whereNull('response')->first();
                if( empty( $response ) )
                {
                    $answers = QuizResponses::where('quiz_id', $quizId)->where('user_id', $user->id)->where('result_id', $result->id)->get();
                    $score = $answers->where('status', true)->count();
                    $result->score = round( floatval( $score / $answers->count() ), 2 ) * 100;
                    $result->status = true;
                    $result->ended_at = now()->format('Y-m-d H:i:s');
                    $result->save();
                    return redirect()->route('quiz.overview', ['id' => $id, 'quiz_id' => $quizId])->with(['msg' => __('admin.quizCompleted'), 'class' => 'success']);
                }
                $question = $response->question;
    
                $diff = now()->diff( $time_limit );
                $time_remaining = sprintf('%02d:%02d:%02d', $diff->h, $diff->i, $diff->s);
                return view('admin/clients/quiz_taking', compact('quiz', 'training', 'questions', 'question', 'response', 'time_remaining'));
            }
            else
            {
                $result->status = true;
                $result->ended_at = $time_limit->format('Y-m-d H:i:s');
                $result->save();
                return redirect()->route('quiz.overview', ['id' => $id, 'quiz_id' => $quizId])->with(['msg' => __('admin.quizTimesUp'), 'class' => 'danger']);
            }
        } else { return abort(401); }
    }

    public function myQuizResponses($id, $quizId, $resultId)
    {
        $user = auth()->user();
        if( $user->hasRole(['Registered']) )
        {
            if( !$user->HaveCompany ) { return redirect()->route('my_profile')->with(['msg' => 'You need to enable "Have a company" first.', 'class' => 'danger']); }
            if( !$user->company ) { return redirect()->route('my_company')->with(['msg' => 'You need to add your company details first.', 'class' => 'danger']); }

            $company = $user->company;
            $cycleData = $company->cycledata;

            if( empty( $cycleData ) ) { return redirect()->route('dashboard')->with(['msg' => 'You need to join program first.', 'class' => 'danger']); }
            if( empty( $cycleData->organization_id ) ) { return redirect()->route('dashboard')->with(['msg' => 'You need to join program first.', 'class' => 'danger']); }

            $organization_id = $cycleData->organization_id;
            $quiz = Quizzes::where('status', 'published')->where('training_id', $id)->where('organization_id', $organization_id)->with(['training', 'myAttempts', 'questions'])->findOrFail( $quizId );
            $results = QuizResponses::where('quiz_id', $quizId)->where('user_id', $user->id)->where('result_id', $resultId)->with(['question'])->get();

            $training = $quiz->training;
            if( empty( $training ) ) { return abort(404); }
            return view('admin/clients/quiz_results', compact('quiz', 'results', 'training'));
        } else { return abort(401); }
    }

    public function results($id = 0)
    {
        $this->preProcessingCheck('edit_cycles');
        $organization_id=auth()->user()->organization_id;
        $sessions = TrainingSession::when(!is_null($organization_id),function ($q) use($organization_id){
            return $q->whereHas('step',function ($query) use ($organization_id){
                $query->whereHas('cycle',function ($qer) use ($organization_id){
                    $qer->where('organization_id',$organization_id);
                });
            });
        })->where('type','online')->orderBy('datetime')->get();
        if ($id == 0) {
            $id = $sessions->pluck('id')->first();
            $csession = TrainingSession::where('type','online')->where('id', $id)->first();
        } else {
            $csession = TrainingSession::where('type','online')->where('id', $id)->first();
        }
        $contents = 0;
        if ($csession) {
            $contents = TrainingContent::where('training_id', $csession->location)->count();
        }

        $steps11 = Step::where('id', $csession->step_id)->orderBy('arr')->get();
      foreach ($steps11 as $steps1) {
        $companies = Company::where('cycle',$steps1->cycle_id)->get();
      }
        $uniqueCycleIds = $companies->pluck('cycle')->unique();
        $steps = Step::whereIn('cycle_id', $uniqueCycleIds)->orderBy('arr')->get();
       

        return view('admin.settings.training.results', compact('csession', 'sessions', 'id', 'contents', 'steps'));
    }
    public function sessions($id=0) {
        $this->preProcessingCheck('edit_cycles');
        $organization_id=auth()->user()->organization_id;
        $steps = Step::when(!is_null($organization_id),function ($q) use($organization_id){
            return $q->whereHas('cycle',function ($query) use ($organization_id){
                $query->where('organization_id',$organization_id);
            });
        })->where('stype',4)->get();
        if ($id == 0) {$id = $steps->pluck('id')->first();}
        $sessions = TrainingSession::where('step_id',$id)->orderBy('datetime','ASC')->get();
        $online = Training::get();
        return view('admin/settings/training/sessions', compact('steps','id','sessions','online'));
    }

    public function mysessions() {
      	$sessions = [];
      	if( Auth::user()->company ) { $sessions = TrainingSession::where('step_id', Auth::user()->company->step)->orderBy('datetime','ASC')->get(); }
        return view('admin/settings/training/mysessions', compact('sessions'));
    }

    public function addsession(Request $request) {
        $this->preProcessingCheck('edit_cycles');
        $step = $request->step_id;
        $title = $request->title;
        $dsc = $request->dsc;
        $datetime = $request->datetime;
        $type =$request->type;
        if ($type=='sessions') {
          // Add Zoom
            $topic = "Training (" . $title . ')';
            $data = ['topic'=>$topic, "start_time"=>$datetime, 'duration'=>$request->duration, 'host_video'=>1, 'participant_video'=>1];
            $onlineMeeting = $this->meetingService->createMeeting($data);

            if (!$onlineMeeting['success']) {return back()->with(['message'=>'Can\'t create zoom meeting, API error','class' => 'danger']);}
            $zoomid =  $onlineMeeting['data']['id'];
            $start = $onlineMeeting['data']['start_url'];
            $join = $onlineMeeting['data']['join_url'];
            TrainingSession::insert(['type'=>$type,'step_id'=>$step,'title'=>$title,'dsc'=>$dsc,'zoom_id'=>$zoomid,'datetime'=>$datetime,
                'duration'=>$request->duration,'user_id'=>Auth::id(),'start_url'=>$start,'join_url'=>$join,'trainer_name'=>$request->trainer_name,'trainer_email'=>$request->trainer_email]);
            // ======> notify Trainer <=================
           $mail ['trainer_name'] = $request->trainer_name;
           $mail ['title'] = $topic;
           $mail ['date'] = $datetime;
           $mail ['organization_name_en'] = Step::find($step)->cycle->organization->name_en;
           $mail ['organization_name_ar'] = Step::find($step)->cycle->organization->name_ar;
            $MSG=new TrainerEmail($mail);
            \Notification::route('mail', $request->trainer_email)
                ->notify($MSG);
            // ==========> End Notify <================
            return back()->with(['message'=>'Session added successfully','class' => 'success']);
        } elseif ($type=='online') {
            if(!$request->online) {return back()->with(['message2','Can\'t add training you must add new training course first']);}
            TrainingSession::insert(['type'=>$type,'step_id'=>$step,'title'=>$title,'dsc'=>$dsc,'datetime'=>$datetime,'user_id'=>Auth::id(),'location'=>$request->online]);
            return back()->with(['message'=>'Saved','class' => 'success']);
        } elseif ($type=='offline') {
            TrainingSession::insert(['type'=>$type,'step_id'=>$step,'title'=>$title,'dsc'=>$dsc,'datetime'=>$datetime,'user_id'=>Auth::id(),'location'=>$request->location,
                'enddatetime'=>$request->enddatetime, 'trainer_name'=>$request->trainer_name,'trainer_email'=>$request->trainer_email]);
            // ======> notify Trainer <=================

            $mail ['trainer_name'] = $request->trainer_name;
            $mail ['title'] = $title;
            $mail ['date'] = $datetime;
            $mail ['organization_name_en'] = Step::find($step)->cycle->organization->name_en;
            $mail ['organization_name_ar'] = Step::find($step)->cycle->organization->name_ar;
            $MSG=new TrainerEmail($mail);
            \Notification::route('mail', $request->trainer_email)
                ->notify($MSG);
            // ==========> End Notify <================
            return back()->with(['message'=>'Saved','class' => 'success']);
        } else {
            return back()->with(['message'=>'Can\'t add session','class' => 'danger']);
        }
    }

    public function editsession(Request $request) {
        $this->preProcessingCheck('edit_cycles');
        $title = $request->title;
        $dsc = $request->dsc;
        $datetime = $request->datetime;
        $type =$request->stype;
        $id = $request->sid;
        if ($type=='sessions') {
            // Add Zoom
            $topic = "Training (" . $title . ')';
            $data = ['topic'=>$topic, "start_time"=>$datetime, 'duration'=>$request->duration, 'host_video'=>1, 'participant_video'=>1];
            $onlineMeeting = $this->meetingService->createMeeting($data);

            if (!$onlineMeeting['success']) {return back()->with(['message'=>'Can\'t create zoom meeting, API error','class' => 'danger']);}
            $zoomid =  $onlineMeeting['data']['id'];
            $start = $onlineMeeting['data']['start_url'];
            $join = $onlineMeeting['data']['join_url'];
            $session=TrainingSession::where('id',$id)->update(['type'=>$type,'title'=>$title,'dsc'=>$dsc,'zoom_id'=>$zoomid,'datetime'=>$datetime,
                'duration'=>$request->duration,'user_id'=>Auth::id(),'start_url'=>$start,'join_url'=>$join,'trainer_name'=>$request->trainer_name,'trainer_email'=>$request->trainer_email]);
            // ======> notify Trainer <=================
            $mail ['trainer_name'] = $request->trainer_name;
            $mail ['title'] = $topic;
            $mail ['date'] = $datetime;
            $mail ['organization_name_en'] = $session->step->cycle->organization->name_en;
            $mail ['organization_name_ar'] = $session->step->cycle->organization->name_ar;
            $MSG=new TrainerEmail($mail);
            \Notification::route('mail', $request->trainer_email)
                ->notify($MSG);

            // ==========> End Notify <================
            return back()->with(['message'=>'Session added successfully','class' => 'success']);
        } elseif ($type=='online') {
            if(!$request->online) {return back()->with(['message2','Can\'t add training you must add new training course first']);}
            TrainingSession::where('id',$id)->update(['type'=>$type,'title'=>$title,'dsc'=>$dsc,'datetime'=>$datetime,'user_id'=>Auth::id(),'location'=>$request->online]);
            return back()->with(['message'=>'Saved','class' => 'success']);
        } elseif ($type=='offline') {
            $session=TrainingSession::where('id',$id)->update(['type'=>$type,'title'=>$title,'dsc'=>$dsc,'datetime'=>$datetime,'user_id'=>Auth::id(),'location'=>$request->location,
                'enddatetime'=>$request->enddatetime, 'trainer_name'=>$request->trainer_name,'trainer_email'=>$request->trainer_email]);
            // ======> notify Trainer <=================
            $mail ['trainer_name'] = $request->trainer_name;
            $mail ['title'] = $title;
            $mail ['date'] = $datetime;
            $mail ['organization_name_en'] = $session->step->cycle->organization->name_en;
            $mail ['organization_name_ar'] = $session->step->cycle->organization->name_ar;
            $MSG=new TrainerEmail($mail);
            \Notification::route('mail', $request->trainer_email)
                ->notify($MSG);
            // ==========> End Notify <================
            return back()->with(['message'=>'Saved','class' => 'success']);
        } else {
            return back()->with(['message'=>'Can\'t edit session','class' => 'danger']);
        }
    }
    public function session(Request $request) {
        $this->preProcessingCheck('edit_cycles');
      if ($request->id) {
            $session = TrainingSession::findOrFail($request->id);
            return response()->json($session);
      }  else {
            return response()->json(['state' => 'failed'], 500);
      }
    }
    public function delsession($id, Request $request){
        $this->preProcessingCheck('edit_cycles');
//        $zoomid = TrainingSession::findOrFail($id)->zoom_id;
        if($request) {
//            if ($zoomid) {
//                $zoom = $this->delete($zoomid);
//                if (!$zoom['success']) {
//                    return response()->json(['state' => 'failed'], 500);
//                }
//            }
            TrainingSession::findOrFail($id)->delete();
            return response()->json(['state' => 'success'], 200);
        } else {
            return response()->json(['state' => 'failed'], 500);
        }
    }
    public function getdsc($id) {
        if($id) {
            $session = TrainingSession::findOrFail($id);
            return response()->json($session);
        } else {
            return response()->json(['state' => 'failed'], 500);
        }
    }
}