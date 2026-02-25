<?php

namespace App\Http\Controllers\Mentor;

use App\User;
use App\Company;
use App\MentorshipTask;
use App\SurveyResponses;
use App\MentorshipRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Traits\HasRoles;

class MentorshipController extends Controller
{
    public function index(){

        $mentorRequests =  MentorshipRequest::all();
        $available_surveys = SurveyResponses::whereIn('user_id', $mentorRequests->pluck('mentor_id'))->whereIn('triggered_at', $mentorRequests->pluck('id'))->where('triggered_by', 'session')->get();

        $sessions = collect([]);
        if(auth()->user() && auth()->user()->hasRole('Mentor')) {
            $sessions = auth()->user()->mentorSessions()->whereDate('zoom_date','<',now()->format('Y-m-d H:i'))->where('approved','Yes')->orderBy('zoom_date','DESC')->get();
        }
        if(auth()->user() && auth()->user()->hasRole('Registered')) {
            $company_id = auth()->user()->company_id;
            if(auth()->user()->company) {
                $usersCompany = auth()->user()->company->users()->where('company_id', $company_id)->get();
                $sessions = MentorshipRequest::whereIn('user_id', $usersCompany->pluck('id'))->whereDate('zoom_date','<',now()->format('Y-m-d H:i'))->where('approved','Yes')->orderBy('zoom_date','DESC')->get();
            } else {
                $sessions = collect(); // Empty collection if no company
            }
        }
        if(auth()->user() && auth()->user()->hasRole('Admin')) {
            $company_id = Company::all();
            $usersCompany = User::whereIn('company_id', $company_id->pluck('id'))->get();
            $sessions = MentorshipRequest::whereIn('user_id', $usersCompany->pluck('id'))->whereDate('zoom_date','<',now()->format('Y-m-d H:i'))->where('approved','Yes')->orderBy('zoom_date','DESC')->get();
        }
        
        return view('mentor.mentorshipSessions.index',compact('sessions','available_surveys'));
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
