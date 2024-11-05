<?php

namespace App\Http\Controllers;

use App\Company;
use App\Cycle;
use App\CyclesMentors;
use App\Events\WebMessage;
use App\Factories\OnlineMeetingFactory;
use App\Jobs\ExportMentorsData;
use App\MentorshipRequest;
use App\Schedule;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Notifications\TemplateEmail;
use Carbon\Carbon;


class MentorsController extends Controller
{


    protected $meetingService;
    public function __construct()
    {
         $this->meetingService =  OnlineMeetingFactory::create();
    }
  


    public function expormentorship(Request $request)
    {
        // تحقق من أن البريد الإلكتروني موجود
       
        $data4 = [
            'request' => $request->input('request'),
            'userEmail' => auth()->user()->email
        ];

        // إرسال وظيفة (Job) لتحميل البيانات وإرسال البريد
        ExportMentorsData::dispatch($data4);

        return response()->json(['message' => 'Export process started. You will receive an email with the download attached.']);
    }


    public function index($id = 0)
    {
        $this->preProcessingCheck('edit_cycles');
        $organization_id=auth()->user()->organization_id;

        if($id==0){$id = Cycle::when(!is_null($organization_id),function ($q) use($organization_id){
            return $q->where('organization_id',$organization_id);
        })->orderBy('start','DESC')->pluck('id')->first();}
        $companies = Company::where('cycle',$id)->where('step','>',0)->get();
        $mentors = CyclesMentors::where('cycle_id',$id)->get();
        $cycles= Cycle::when(!is_null($organization_id),function ($q) use($organization_id){
            return $q->where('organization_id',$organization_id);
        })->get();
        return view('admin.settings.mentorship.index', compact( 'mentors','id','companies','cycles'));
    }
    public function list($id = 0){
        $this->preProcessingCheck('edit_cycles');
        $organization_id=auth()->user()->organization_id;

        if($id==0){$id = Cycle::when(!is_null($organization_id),function ($q) use($organization_id){
            return $q->where('organization_id',$organization_id);
        })->orderBy('start','DESC')->pluck('id')->first();}
        $mentors = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6)->get();
        $clist = CyclesMentors::where('cycle_id',$id)->pluck('user_id')->toArray();
        $cycles= Cycle::when(!is_null($organization_id),function ($q) use($organization_id){
            return $q->where('organization_id',$organization_id);
        })->get();

        return view('admin.settings.mentorship.assign', compact( 'mentors','id','clist','cycles'));
    }
    public function assign($id ,Request $request){
        $this->preProcessingCheck('edit_cycles');
        if ($request->id){
            CyclesMentors::where('cycle_id',$request->id)->delete();
            if ($request->users) {
            foreach ($request->users as $user) {
                CyclesMentors::insert(['cycle_id'=>$request->id,'user_id'=>$user]);
            }}
        }
        return back()->with(['message' => 'Request successfully added','class'=>'success']);
    }
    public function addwait(Request $request)
    {
        if ($request->mentorid) {
            if ($request->date < date("Y-m-d 00:00:00")) {return back()->with(['msg' => "Error, can't add late date.",'class'=>'danger']);}
            // uploadfile
            $fileName ='';
            if ($request->hasFile('file_url')) {
                $fileName = $request->file('file_url');
                $fileName = $fileName->store(Auth::id(), ['disk' => 'PrivateDisk']);
            }
            MentorshipRequest::create([
                'mentor_id' => $request->mentorid, 'user_id' => Auth::id(), 'comment' => $request->notes,
                'zoom_date' => $request->date, 'period' => $request->period, 'fileurl' => $fileName,
                'organization_id'=>$request->organization_id,'cycle_id'=>$request->cycle_id
            ]);
            // Notify Admin here
            event(new WebMessage('NewMent', Auth::user()->first_name_en .' '. Auth::user()->last_name_en));
            return back()->with(['msg' => 'Request successfully added','class'=>'success']);
        } else {
            return back()->with(['msg' => "Error, can't add request",'class'=>'danger']);
        }
    }
    public function add(Request $request)
    {
//        $this->preProcessingCheck('edit_cycles');

        if ($request->userid) {
            $dates = explode(',',$request->date);
            
            foreach ($dates as $key){
                $date = ltrim($key);
                
                
                $topic = "Online mentorship meeting (M" . $request->mentorid . '- U'.$request->userid . ')';
                
                $data = ['topic'=>$topic, "start_time"=>$date, 'duration'=>$request->period, 'host_video'=>1, 'participant_video'=>1];
                
                $onlineMeeting = $this->meetingService->createMeeting($data);
                
                if (!$onlineMeeting['success']) {return back()->with(['message2' => "meeting_error"]);}
                // $date = substr($date1, 0, -2);
                // $dateAM = substr($date1, -2);
                
                

                $dateFormat = new \DateTime($date);
                
                // dd($dateFormat->format('Y-m-d H:i'));

                MentorshipRequest::create([
                    'mentor_id' => $request->mentorid, 'user_id' => $request->userid, 'admin_comment' => $request->notes, 'approved' => 'Yes',
                    'admin_id' => Auth::id(), 'start_url' => $onlineMeeting['data']['start_url'], 'join_url' => $onlineMeeting['data']['join_url'],
                    'zoom_date' => $dateFormat->format('Y-m-d H:i'), 'period' => $request->period,'organization_id'=>$request->organization_id,'cycle_id'=>$request->cycle_id,
                ]);
                /// notify user via Mail

                $mentor = User::findOrFail($request->mentorid);
                $mentor_name = $mentor->name;

                $startup_owner = User::findOrFail($request->userid);
                $startup_owner_name = $startup_owner->name;

                /// Notify Mentor
                $user = new User();
                $user->email = $mentor->email;
                $user->id = $mentor->id;

                $MSG = new TemplateEmail;
                $MSG->Message=["New Mentorship session - VI portal","Dear ".$mentor_name.",","Kindly note you have a new mentorship session with '".$startup_owner_name."', Session will start:".$date.
                    ", Check your account to join the session ","/dashboard"];
                $user->notify($MSG);

                /// Notify Startup Owner
                $user = new User();
                $user->email = $startup_owner->email;
                $user->id = $startup_owner->id;
                $MSG = new TemplateEmail;
                $MSG->Message=["New Mentorship session - VI portal","Dear ".$startup_owner_name.",","Kindly note you have a new mentorship session with '".$mentor_name."', Session will start:".$date.
                    ", Check your account to join the session ","/dashboard"];
                $user->notify($MSG);
                /// End of mail send

            }
            return back()->with(['message' => 'Session added successfully']);
        } else{
            return back()->with(['message2' => "Error, can't add session"]);
        }
    }
    public function sessions(Request $request)
    {
        $this->preProcessingCheck('edit_cycles');
        $organization_id=auth()->user()->organization_id;

        if ($request->show == 'all') {

            $sessions = MentorshipRequest::when(!is_null($organization_id), function ($q) use ($organization_id) {
                return $q->where('organization_id', $organization_id);
            })->when($request->start_date && $request->end_date, function ($q) use ($request) {
                // التأكد من أن التواريخ متوافقة مع التنسيق المناسب
                $startDate = date('Y-m-d', strtotime($request->start_date));
                $endDate = date('Y-m-d', strtotime($request->end_date));
        
                // البحث باستخدام التاريخ من وإلى
                return $q->whereDate('zoom_date', '>=', $startDate)
                         ->whereDate('zoom_date', '<=', $endDate);
            })->orderBy('id','DESC')->get();

        } elseif ($request->show == 'approved')  {
            $sessions = MentorshipRequest::when(!is_null($organization_id),function ($q) use($organization_id){
                return $q->where('organization_id',$organization_id);
            })->whereNotNull('start_url')
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                // التأكد من أن التواريخ متوافقة مع التنسيق المناسب
                $startDate = date('Y-m-d', strtotime($request->start_date));
                $endDate = date('Y-m-d', strtotime($request->end_date));
        
                // البحث باستخدام التاريخ من وإلى
                return $q->whereDate('zoom_date', '>=', $startDate)
                         ->whereDate('zoom_date', '<=', $endDate);
            })->orderBy('id','DESC')->get();



        } elseif ($request->show == 'disapproved')  {
            $sessions = MentorshipRequest::when(!is_null($organization_id),function ($q) use($organization_id){
                return $q->where('organization_id',$organization_id);
            })->where('approved','No')
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                // التأكد من أن التواريخ متوافقة مع التنسيق المناسب
                $startDate = date('Y-m-d', strtotime($request->start_date));
                $endDate = date('Y-m-d', strtotime($request->end_date));
        
                // البحث باستخدام التاريخ من وإلى
                return $q->whereDate('zoom_date', '>=', $startDate)
                         ->whereDate('zoom_date', '<=', $endDate);
            })->orderBy('id','DESC')->get();


        } else {

            $sessions = MentorshipRequest::when(!is_null($organization_id),function ($q) use($organization_id){
                return $q->where('organization_id',$organization_id);
            })->whereNull('approved')
            ->when($request->start_date && $request->end_date, function ($q) use ($request) {
                // التأكد من أن التواريخ متوافقة مع التنسيق المناسب
                $startDate = date('Y-m-d', strtotime($request->start_date));
                $endDate = date('Y-m-d', strtotime($request->end_date));
        
                // البحث باستخدام التاريخ من وإلى
                return $q->whereDate('zoom_date', '>=', $startDate)
                         ->whereDate('zoom_date', '<=', $endDate);
            })->orderBy('id','DESC')->get();


        }
        return view('admin.settings.mentorship.sessions', compact( 'sessions'));
    }
  	public function getsession(Request $request) {
      if ($request){
       $my = MentorshipRequest::findOrFail($request->id);
       return response()->json($my);
      } else {
       return response()->json(['state' => 'failed'], 500); 
      }
    }
  	public function modifysession(Request $request) {
      if ($request){
      $approved = $start = $join = null;
      if ($request->approved) {
        $approved ='Yes';
        $topic = "Online mentorship meeting (M" . $request->mentorid . '- U'.$request->userid . ')';
        $data = ['topic'=>$topic, "start_time"=>$request->date, 'duration'=>$request->period, 'host_video'=>1, 'participant_video'=>1];
        $onlineMeeting = $this->meetingService->createMeeting($data);
        if (!$onlineMeeting['success']) {return back()->with(['message2' => "Error, can't create Online meeting."]);}
        $start = $onlineMeeting['data']['start_url'];
        $join = $onlineMeeting['data']['join_url'];
      }
      MentorshipRequest::find($request->id)->update(['period'=> $request->period, 'zoom_date'=>$request->date, 'admin_comment' => $request->notes, 'approved'=>$approved, 'start_url' => $start, 'join_url' => $join ]);
          /// Notify Mentor
          $session = MentorshipRequest::findOrFail($request->id);
          $rname = User::findOrFail($session->mentor_id);
          $rname_usr = User::findOrFail($session->user_id);
          $user = new User();
          $user->email = $rname->email;
          $user->id = $rname->id;
          $rfullname = $rname->name;
          $rfullname_usr = $rname_usr->name;
          $MSG = new TemplateEmail;
          $MSG->Message=["New Mentorship session - VI portal","Dear ".$rfullname.",","Kindly note you have a new mentorship session with '".$rfullname_usr."', Session will start:".$request->date.
              ", Check your account to join the session ","/dashboard"];
          $user->notify($MSG);
          /// Notify User
          $user = new User();
          $user->email = $rname_usr->email;
          $user->id = $rname_usr->id;
          $MSG = new TemplateEmail;
          $MSG->Message=["New Mentorship session - VI portal","Dear ".$rfullname_usr.",","Kindly note you have a new mentorship session with '".$rfullname."', Session will start:".$request->date.
              ", Check your account to join the session ","/dashboard"];
          $user->notify($MSG);
          /// End of mail send
      return back()->with(['message' => 'ٍsaved successfully']);
      } else {return back()->with(['message2' => 'Error saving data']);}
    }

    public function modify(Request $request)
    {
       // $this->preProcessingCheck('edit_mentorship'); /// modify permissions
            try {
                $type = $request->rtype;
                if ($type == 6) {
                  	$start = $join = null;
                  	   if($request->stat=='Yes'){
                             $mrequest = MentorshipRequest::findOrFail($request->id);
                             $topic = "Online mentorship meeting (M" . $mrequest->mentor_id . '- U'.$mrequest->user_id . ')';
                             $data = ['topic'=>$topic, "start_time"=>$mrequest->zoom_date, 'duration'=>$mrequest->period, 'host_video'=>1, 'participant_video'=>1];

                             $onlineMeeting = $this->meetingService->createMeeting($data);

                             if (!$onlineMeeting['success']) {return response()->json('failed, Online meeting api error');}
                             $start = $onlineMeeting['data']['start_url'];
                             $join = $onlineMeeting['data']['join_url'];
                         }
                    MentorshipRequest::find($request->id)->update(['approved'=>$request->stat, 'start_url' => $start, 'join_url' => $join ]);
                    /// Notify Mentor
                    $session = MentorshipRequest::findOrFail($request->id);
                    $rname = User::findOrFail($session->mentor_id);
                    $rname_usr = User::findOrFail($session->user_id);
                    $user = new User();
                    $user->email = $rname->email;
                    $user->id = $rname->id;
                    $rfullname = $rname->name;
                    $rfullname_usr = $rname_usr->name;
                    $MSG = new TemplateEmail;
                    $MSG->Message=["New Mentorship session - VI portal","Dear ".$rfullname.",","Kindly note you have a new mentorship session with '".$rfullname_usr."', Session will start:".$request->date.
                        ", Check your account to join the session ","/dashboard"];
                    $user->notify($MSG);
                    /// Notify User
                    $user = new User();
                    $user->email = $rname_usr->email;
                    $user->id = $rname_usr->id;
                    $MSG = new TemplateEmail;
                    $MSG->Message=["New Mentorship session - VI portal","Dear ".$rfullname_usr.",","Kindly note you have a new mentorship session with '".$rfullname."', Session will start:".$request->date.
                        ", Check your account to join the session ","/dashboard"];
                    $user->notify($MSG);
                    /// End of mail send
                    return response()->json('success');
                } elseif ($type == 5) {
                    $my = Schedule::where('active',1)->where('user_id', $request->id)->get();
                    $sessions = MentorshipRequest::where('mentor_id',$request->id)->where('zoom_date','>=',date("Y-m-d 00:00:00"))->where('approved','Yes')
                        ->get(['zoom_date as dfrom','period AS tfrom',DB::raw("(id + 1000000) AS id, 'Session' AS type, 'Mentorship session' AS notes")]);
                    $my = $my->merge($sessions);
                    return response()->json($my);
                } elseif ($type == 4) {
                    $values = ['type' => $request->type, 'days' => json_encode($request->days), 'dfrom' => $request->dfrom, 'dto' => $request->dto, 'tfrom' => $request->tfrom, 'tto' => $request->tto, 'notes' => $request->notes];
                    Schedule::where('user_id', Auth::id())->where('id', $request->id)->update($values);
                    return back()->with(['msg' => 'Saved', 'class' => 'success']);
                } elseif ($type == 3) {
                    Schedule::where('user_id', Auth::id())->where('id', $request->id)->delete();
                    return response()->json(['state' => 'success']);
                } elseif ($type == 2) {
                    $stat = $request->stat ? 0 : 1;
                    Schedule::where('user_id', Auth::id())->where('id', $request->id)->update(['active' => $stat]);
                    return response()->json(['state' => 'success']);
                } elseif ($type == 1) {
                    $my = Schedule::where('user_id', Auth::id())->where('id', $request->id)->first();
                    return response()->json($my);
                } else {

                    Schedule::create($request->all() + ['user_id' => Auth::id()]);
                    return back()->with(['msg' => 'Saved', 'class' => 'success']);
                }
            } catch (Exception $e) {
                return response()->json(['state' => 'failed','e'=>$e], 500);
            }
    }
    public function mine() {
        $sessions = MentorshipRequest::where('user_id',Auth::id())->get();
        return view('admin.clients.mymentorships', compact( 'sessions'));
    }
    public function rate(Request $request){
        if($request) {
            $request->done?$done=1:$done=null;
            MentorshipRequest::where('id',$request->id)->where('user_id',Auth::id())->update(['rating'=>$request->rate,'rate_comment'=>$request->comment,'done'=>$done]);
            return back();
        }else {
            return back();
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
            MentorshipRequest::findOrFail($id)->delete();
            return response()->json(['state' => 'success'], 200);
        } else {
            return response()->json(['state' => 'failed'], 500);
        }
    }
}