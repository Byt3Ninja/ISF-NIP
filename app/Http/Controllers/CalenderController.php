<?php

namespace App\Http\Controllers;

use App\Calendar;
use App\Cycle;
use App\MentorshipRequest;
use App\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalenderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request){

        if($request->ajax()) {

            /// Calender Data
            $data=[];
            $user = auth()->user();
            $organization_id = $user->organization_id;
            if ($user->hasanyRole('Admin|Editor|Manager|organizationManager')) {
               $data = $this->AdminCelander($organization_id);
            }elseif ($user->hasanyRole('Mentor')){
              $data = $this->MentorCelander();
            }

            return response()->json($data);
        }
        return view('admin.calendar');
    }
    public function ViewCall(Request $request) {

        try {
            switch ($request->type){
                case "M":
                    $mentorshipResquest = MentorshipRequest::find($request->id);
                    $data['body']= '<div class="media align-items-center mt-2">
										<div class="media-left mr-50">
											<img src="'.$mentorshipResquest->mentor->image_url.'" alt="avatar" class="rounded-circle" height="40" width="40">
										</div>
											<div class="media align-items-center">'.
                                     __('admin.Mentor') .': '.$mentorshipResquest->mentor->name
											.'</div>
									</div>'.
                        '<div class="media align-items-center mt-2">
										<div class="media-left mr-50">
											<img src="'.$mentorshipResquest->user->image_url.'" alt="avatar" class="rounded-circle" height="40" width="40">
										</div>
											<div class="media align-items-center">'.
                        __('admin.Registered') .': '.$mentorshipResquest->user->name
                        .'</div>
					</div>';
                    $data['start']= $mentorshipResquest->zoom_date;
                    $data['end']= $mentorshipResquest->end_zoom_date;
                    break;

            }
            return response()->json($data);
        }catch (\Exception $e){
            return response()->json([
                'state' => 'failed'
            ], 500);
        }


    }

    protected function AdminCelander($organization_id){
        $mentorshipRequests = MentorshipRequest::when(!is_null($organization_id), function ($q) use ($organization_id) {
            return $q->where('organization_id', $organization_id);
        })->get();
        foreach ($mentorshipRequests as $key) {
            $data[] = [
                'id' => $key->id,
                'title' => __('admin.mentorship session'),
                'start' => $key->ZoomDateOrg,
                'end' => $key->EndZoomDateOrg,
                'calendarId' => 'M',
                'color' => '#ff5b5c',
                'allDay' => false,
                'textColor' => '#ffffff'
            ];
        }
        $cycles = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
            return $q->where('organization_id', $organization_id);
        })->get();
        foreach ($cycles as $key) {
            $data[] = [
                'id' => $key->id,
                'title' => $key->title,
                'start' => $key->start,
                'end' => $key->end,
                'calendarId' => 'C',
                'color' => '#009faa',
                'allDay' => true,
                'textColor' => '#ffffff'
            ];
        }
        $sessions = TrainingSession::whereHas('step', function ($q) use ($organization_id) {
            $q->where('stype', 4)->when(!is_null($organization_id), function ($q) use ($organization_id) {
                return $q->whereHas('cycle', function ($query) use ($organization_id) {
                    $query->where('organization_id', $organization_id);
                });
            });
        })->get();
        foreach ($sessions as $key) {
            $data[] = [
                'id' => $key->id,
                'title' => $key->title,
                'start' => $key->start_org,
                'end' => $key->end_org,
                'calendarId' => 'T',
                'color' => '#fdac41',
                'allDay' => false,
                'textColor' => '#ffffff'
            ];
        }
        return $data;
    }

    protected function MentorCelander(){
        $mentorshipRequests = auth()->user()->mentorSessions()->get();
        foreach ($mentorshipRequests as $key) {
            $data[] = [
                'id' => $key->id,
                'title' => __('admin.mentorship session'),
                'start' => $key->ZoomDateOrg,
                'end' => $key->EndZoomDateOrg,
                'calendarId' => 'M',
                'color' => '#ff5b5c',
                'allDay' => false,
                'textColor' => '#ffffff'
            ];
        }
        $cycles = auth()->user()->mentorCycles()->get();
        foreach ($cycles as $key) {
            $data[] = [
                'id' => $key->id,
                'title' => $key->title,
                'start' => $key->start,
                'end' => $key->end,
                'calendarId' => 'C',
                'color' => '#009faa',
                'allDay' => true,
                'textColor' => '#ffffff'
            ];
        }
        return $data;
    }
}
