<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\MentorshipRequest;
use App\MentorshipTask;
use Illuminate\Http\Request;

class MentorshipController extends Controller
{
    public function index(){
        $sessions = auth()->user()->mentorSessions()->whereDate('zoom_date','<',now()->format('Y-m-d'))->where('approved','Yes')->orderBy('zoom_date','DESC')->get();
        return view('mentor.mentorshipSessions.index',compact('sessions'));
    }
    public function meetingRedirect($mentorship_request_id)
    {
       $mentorship_request = MentorshipRequest::findOrFail($mentorship_request_id);
       if ($mentorship_request->mentorshipTasks()->first()){
           $response['message']=__('mentor.mentorshipTasks.Task created before');
           $response['join_url']=$mentorship_request->join_url;
           $response['mentorship_id']=$mentorship_request->id;
           return response()->json($response,200);
       }

       $mentorship_request->mentorshipTasks()->create([
           'mentor_id'=>$mentorship_request->mentor_id,
           'company_id'=>$mentorship_request->user->company->id,
           'user_id'=>$mentorship_request->user_id,
       ]);
        $response['message']=__('mentor.mentorshipTasks.Task created successfully');
        $response['join_url']=$mentorship_request->join_url;
        $response['mentorship_id']=$mentorship_request->id;
        return response()->json($response,200);

    }
}
