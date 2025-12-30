<?php

namespace App\Http\Controllers\Calender;

use App\Cycle;
use App\Finalscreening;
use App\Http\Controllers\Controller;
use App\MentorshipRequest;
use App\Screening;
use App\Step;
use App\TrainingSession;
use Illuminate\Http\Request;

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
            $orgIds = $user->hasRole('admin') ? null : $user->organizations_ids;
            if ($user->hasAnyRole('Admin|Editor|Manager|organizationManager|subadmin')) {

               $data = $this->AdminCalender($orgIds);

            }elseif ($user->hasAnyRole('Mentor')){

              $data = $this->MentorCalender();

            }elseif ($user->hasRole('Judger')){

                $data = $this->JudgerCalender();
            }elseif ($user->hasAnyRole(['Registered', 'Page Manager', 'Pages Editor'])) {
                $data=$this->StartupCalender();
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
											<div class="d-flex align-items-center gap-1">'.
                                     __('admin.Mentor') .': '.$mentorshipResquest->mentor->name
											.'</div>
									</div>'.
                        '<div class="media align-items-center mt-2">
										<div class="media-left mr-50">
											<img src="'.$mentorshipResquest->user->image_url.'" alt="avatar" class="rounded-circle" height="40" width="40">
										</div>
											<div class="d-flex align-items-center gap-1">'.
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

    protected function AdminCalender($orgIds){
        $mentorshipRequests = MentorshipRequest::when(!empty($orgIds), function ($q) use ($orgIds) {
            return $q->whereIn('organization_id', $orgIds);
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

        $mysteps = Screening::all();
        $csteps = Step::whereIn('id',$mysteps->pluck('step_id'))->get();
        $finalScreenings = Finalscreening::all();

        foreach ($csteps as $key) {
            $data[] = [
                'id' => $key->id,
                'title' => __('admin.judge Sessions'),
                'start' => $key->from->format('d-m-Y'),
                'end' => $key->to->format('d-m-Y'),
                'calendarId' => 'S',
                'color' => '#1a233a',
                'allDay' => true,
                'textColor' => '#ffffff'
            ];
        }
        foreach ($finalScreenings as $key) {
            $data[] = [
                'id' => $key->id,
                'title' => __('admin.Final Screening'),
                'start' => $key->StartDateOrg,
                'end' => $key->EndDateOrg,
                'calendarId' => 'F',
                'color' => '#2c6de9',
                'allDay' => false,
                'textColor' => '#ffffff'
            ];
        }

        return $data;
    }

    protected function MentorCalender()
{
    $data = []; 

    $mentorshipRequests = auth()->user()?->mentorSessions()?->get() ?? collect();
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

    $cycles = auth()->user()?->mentorCycles()?->get() ?? collect();
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

    protected function JudgerCalender()
{
    if (!auth()->check()) {
        return [];
    }

    $data = [];
    $myid = auth()->id();
    $mysteps = auth()->user()?->screeningRequests()?->get() ?? collect();
    $csteps = Step::whereIn('id', $mysteps->pluck('step_id'))->get();
    $finalScreenings = Finalscreening::where('judges', 'like', "%\"{$myid}\"%")->get();

    foreach ($csteps as $key) {
        $data[] = [
            'id' => $key->id,
            'title' => __('admin.judge Sessions'),
            'start' => $key->from ? $key->from->format('d-m-Y') : null,
            'end' => $key->to ? $key->to->format('d-m-Y') : null,
            'calendarId' => 'S',
            'color' => '#1a233a',
            'allDay' => true,
            'textColor' => '#ffffff'
        ];
    }

    foreach ($finalScreenings as $key) {
        $data[] = [
            'id' => $key->id,
            'title' => __('admin.Final Screening'),
            'start' => $key->StartDateOrg,
            'end' => $key->EndDateOrg,
            'calendarId' => 'F',
            'color' => '#2c6de9',
            'allDay' => false,
            'textColor' => '#ffffff'
        ];
    }

    return $data;
}


    protected function StartupCalender()
    {
        $data=[];
        if (!auth()->user()->company){
            return $data;
        }
        $cycle = auth()->user()->company->cycledata;
        if (!is_null($cycle)){
            $data[] = [
                'id' => $cycle->id,
                'title' => $cycle->title,
                'start' => $cycle->start,
                'end' => $cycle->end,
                'calendarId' => 'C',
                'color' => '#009faa',
                'allDay' => true,
                'textColor' => '#ffffff'
            ];
        }
        $mentorshipRequests = auth()->user()->mentorship;
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
        $sessions = TrainingSession::where("step_id", auth()->user()->company->step)->get();
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
        $finalScreenings = auth()->user()->company->finalScreening;

        foreach ($finalScreenings as $key) {
            $data[] = [
                'id' => $key->id,
                'title' => __('admin.Final Screening'),
                'start' => $key->StartDateOrg,
                'end' => $key->EndDateOrg,
                'calendarId' => 'F',
                'color' => '#2c6de9',
                'allDay' => false,
                'textColor' => '#ffffff'
            ];
        }
        $csteps = auth()->user()->company->stepdata()->where('stype',2)->first();

        if (isset($csteps)){
            $data[] = [
                'id' => $csteps->id,
                'title' => __('admin.judge Sessions'),
                'start' => $csteps->from->format('d-m-Y'),
                'end' => $csteps->to->format('d-m-Y'),
                'calendarId' => 'S',
                'color' => '#1a233a',
                'allDay' => true,
                'textColor' => '#ffffff'
            ];
        }
        return $data;
    }
}
