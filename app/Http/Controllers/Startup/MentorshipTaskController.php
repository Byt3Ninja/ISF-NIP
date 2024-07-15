<?php

namespace App\Http\Controllers\Startup;

use App\Http\Controllers\Controller;
use App\MentorshipRequest;
use App\MentorshipTask;
use Illuminate\Http\Request;

class MentorshipTaskController extends Controller
{
    public function index($id){
        $mentorship= MentorshipRequest::findOrFail($id);
        $mentorshipTasks = $mentorship->mentorshipTasks;
        return view('startup.mentorshipTask.index',compact('mentorshipTasks','mentorship'));
    }
    public function home(){
        $mentorshipTasks = MentorshipTask::where('company_id',auth()->user()->company->id)->get();
        return view('startup.mentorshipTask.home',compact('mentorshipTasks'));
    }

    public function show($id){
        $task= MentorshipTask::findOrFail($id);
        return view('startup.mentorshipTask.show',compact('task'));
    }

    public function complete(Request $request,$id){
        try{

            $task= MentorshipTask::findOrFail($id);
            $result=$task->update(['status'=>'completed','done_date'=>now()->format('Y-m-d'),'reply'=>$request->reply]);

            if($result)
                return redirect(route('startup.mentorshipTasks.index',['mentorship_id'=>$task->mentorship_request_id]))->with( ['message' => 'StoredSuccessfully'] );
            else
                return back()->withInput()->with( ['message2' => 'FailedStore'] );

        }catch (Exception $e){
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);

        }

    }

}
