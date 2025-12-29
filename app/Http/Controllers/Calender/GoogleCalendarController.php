<?php


namespace App\Http\Controllers\Calender;

use App\Step;
use App\Cycle;
use App\Screening;
use App\Finalscreening;
use App\TrainingSession;
use App\MentorshipRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Session;

class GoogleCalendarController extends Controller
{
    protected $googleService;

    public function __construct(GoogleCalendarService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function redirectToGoogle()
    {
        //  dd(route('integrations.calendar_google_callback'));
        return redirect($this->googleService->getAuthUrl());
    }


    public function handleGoogleCallback(Request $request)
    {
        if( $request->has('error') || !$request->has('code') )
        {
            Session::flash('error', trans('auth.auth_error', ['reason' => $request->error]));
            return redirect( route('calendar.events_list') );
        }
        else
        {
            try {
                $code = $request->get('code');
                $user = auth()->user();
                $this->googleService->authenticate($code, $user->id);
                return redirect(route('integrations.calendar_google_sync'));                return redirect( route('calendar.events_list') );
            } catch (\Exception $e) {
                Session::flash('error', trans('auth.auth_error', ['reason' => $e->getMessage()]));
                return redirect( route('calendar.events_list') );
            }
        }
    }

    public function syncEvents()
    {
        $user = auth()->user();
        $orgIds = $user->hasRole('admin') ? null : $user->organizations_ids;

        if( $user->hasAnyRole('Admin|Editor|Manager|organizationManager') ) { $data = $this->AdminCalender( $user, $orgIds ); }
        elseif( $user->hasAnyRole('Mentor') ) { $data = $this->MentorCalender( $user ); }
        elseif( $user->hasRole('Judger') ) { $data = $this->JudgerCalender( $user ) ; }
        elseif( $user->hasRole('Registered|Page Manager|Pages Editor') ) { $data = $this->StartupCalender( $user ); }

        /* if( $data ) { return redirect(route('calendar'))->with('msg','Events synced successfully!'); }
        else { return redirect(route('calendar'))->with('message2','Events synced Failed!'); } */
       
        return redirect()->route('calendar.events_list')->with('success','Events synced successfully!');
    }

    protected function AdminCalender($user,$orgIds){
        $mentorshipRequests = MentorshipRequest::when(!empty($orgIds), function ($q) use ($orgIds) {
            return $q->whereIn('organization_id', $orgIds);
        })->get();
        foreach ($mentorshipRequests as $key) {

            if ($key->CalenderSync()->where(['user_id'=>auth()->id(),'provider'=>'google'])->first()){
                continue;
            }
            // Fetch your project events from the database
            $eventData = [
                'title' => __('admin.mentorship session'),
                'description' => 'Mentor: '.$key->mentor->name.' Startup: '.$key->user->name,
                'location' => $key->join_url,
                'start_date_time' => $key->ZoomDateOrg, // Make sure it's in 'Y-m-d H:i:s' format
                'end_date_time' => $key->EndZoomDateOrg, // Make sure it's in 'Y-m-d H:i:s' format
            ];

            $event = $this->googleService->createEvent($user, $eventData);
            $key->CalenderSync()->create(['provider'=>'google', 'event_id'=>$event->id,'user_id'=>auth()->id()]);
        }
        $cycles = Cycle::when(!is_null($organization_id), function ($q) use ($organization_id) {
            return $q->where('organization_id', $organization_id);
        })->get();
        foreach ($cycles as $key) {
            if ($key->CalenderSync()->where(['user_id'=>auth()->id(),'provider'=>'google'])->first()){
                continue;
            }
            // Fetch your project events from the database
            $eventData = [
                'title' => $key->title,
                'description' => 'Cycle: '.$key->title,
                'start_date_time' => $key->start, // Make sure it's in 'Y-m-d H:i:s' format
                'end_date_time' => $key->end, // Make sure it's in 'Y-m-d H:i:s' format
            ];

            $event = $this->googleService->createEvent($user, $eventData);
            $key->CalenderSync()->create(['provider'=>'google', 'event_id'=>$event->id,'user_id'=>auth()->id()]);
        }
        $sessions = TrainingSession::whereHas('step', function ($q) use ($organization_id) {
            $q->where('stype', 4)->when(!is_null($organization_id), function ($q) use ($organization_id) {
                return $q->whereHas('cycle', function ($query) use ($organization_id) {
                    $query->where('organization_id', $organization_id);
                });
            });
        })->get();
//        foreach ($sessions as $key) {
//
//        if ($key->CalenderSync()->where(['user_id'=>auth()->id(),'provider'=>'google'])->first()){
//                continue;
//            }
//            // Fetch your project events from the database
//            $eventData = [
//                'title' => $key->title,
//                'description' => 'Training: '.$key->title,
//                'start_date_time' => $key->start_org, // Make sure it's in 'Y-m-d H:i:s' format
//                'end_date_time' => $key->end_org, // Make sure it's in 'Y-m-d H:i:s' format
//            ];
//
//            $event = $this->googleService->createEvent($user, $eventData);
//            $key->CalenderSync()->create(['provider'=>'google', 'event_id'=>$event->id,'user_id'=>auth()->id()]);
//        }

        $mysteps = Screening::all();
        $csteps = Step::whereIn('id',$mysteps->pluck('step_id'))->get();
        $finalScreenings = Finalscreening::all();

        foreach ($csteps as $key) {
            if ($key->CalenderSync()->where(['user_id'=>auth()->id(),'provider'=>'google'])->first()){
                continue;
            }
            // Fetch your project events from the database
            $eventData = [
                'title' => __('admin.judge Sessions'),
                'start_date_time' => $key->from->format('Y-m-d H:i:s'), // Make sure it's in 'Y-m-d H:i:s' format
                'end_date_time' => $key->to->format('Y-m-d H:i:s'), // Make sure it's in 'Y-m-d H:i:s' format
            ];

            $event = $this->googleService->createEvent($user, $eventData);
            $key->CalenderSync()->create(['provider'=>'google', 'event_id'=>$event->id,'user_id'=>auth()->id()]);
        }
//        foreach ($finalScreenings as $key) {
//            if ($key->CalenderSync()->where(['user_id'=>auth()->id(),'provider'=>'google'])->first()){
//                continue;
//            }
//            // Fetch your project events from the database
//            $eventData = [
//                'title' => __('admin.Final Screening'),
//                'start_date_time' => $key->StartDateOrg, // Make sure it's in 'Y-m-d H:i:s' format
//                'end_date_time' => $key->EndDateOrg, // Make sure it's in 'Y-m-d H:i:s' format
//            ];
//
//            $event = $this->googleService->createEvent($user, $eventData);
//            $key->CalenderSync()->create(['provider'=>'google', 'event_id'=>$event->id,'user_id'=>auth()->id()]);
//        }

        return true;
    }

    protected function MentorCalender($user){
        $mentorshipRequests = auth()->user()->mentorSessions()->get();
        foreach ($mentorshipRequests as $key) {
            if ($key->CalenderSync()->where(['user_id'=>auth()->id(),'provider'=>'google'])->first()){
                continue;
            }
            // Fetch your project events from the database
            $eventData = [
                'title' => __('admin.mentorship session'),
                'description' => 'Mentor: '.$key->mentor->name.' Startup: '.$key->user->name,
                'location' => $key->join_url,
                'start_date_time' => $key->ZoomDateOrg, // Make sure it's in 'Y-m-d H:i:s' format
                'end_date_time' => $key->EndZoomDateOrg, // Make sure it's in 'Y-m-d H:i:s' format
            ];

            $event = $this->googleService->createEvent($user, $eventData);
            $key->CalenderSync()->create(['provider'=>'google', 'event_id'=>$event->id,'user_id'=>auth()->id()]);
        }
        $cycles = auth()->user()->mentorCycles()->get();
        foreach ($cycles as $key) {
            if ($key->CalenderSync()->where(['user_id'=>auth()->id(),'provider'=>'google'])->first()){
                continue;
            }
            // Fetch your project events from the database
            $eventData = [
                'title' => $key->title,
                'description' => 'Cycle: '.$key->title,
                'start_date_time' => $key->start, // Make sure it's in 'Y-m-d H:i:s' format
                'end_date_time' => $key->end, // Make sure it's in 'Y-m-d H:i:s' format
            ];

            $event = $this->googleService->createEvent($user, $eventData);
            $key->CalenderSync()->create(['provider'=>'google', 'event_id'=>$event->id,'user_id'=>auth()->id()]);
        }
        return true;
    }

     protected function JudgerCalender($user)
    {
        $myid=auth()->id();
        $mysteps = auth()->user()->screeningRequests()->get();
        $csteps = Step::whereIn('id',$mysteps->pluck('step_id'))->get();
        $finalScreenings = Finalscreening::where('judges', 'like', "%\"{$myid}\"%")->get();

        foreach ($csteps as $key) {
            if ($key->CalenderSync()->where(['user_id'=>auth()->id(),'provider'=>'google'])->first()){
                continue;
            }
            // Fetch your project events from the database
            $eventData = [
                'title' => __('admin.judge Sessions'),
                'start_date_time' => $key->from->format('d-m-Y'), // Make sure it's in 'Y-m-d H:i:s' format
                'end_date_time' => $key->to->format('d-m-Y'), // Make sure it's in 'Y-m-d H:i:s' format
            ];

            $event = $this->googleService->createEvent($user, $eventData);
            $key->CalenderSync()->create(['provider'=>'google', 'event_id'=>$event->id,'user_id'=>auth()->id()]);
        }
//        foreach ($finalScreenings as $key) {
//            if ($key->CalenderSync()->where(['user_id'=>auth()->id(),'provider'=>'google'])->first()){
//                continue;
//            }
//            // Fetch your project events from the database
//            $eventData = [
//                'title' => __('admin.Final Screening'),
//                'start_date_time' => $key->StartDateOrg, // Make sure it's in 'Y-m-d H:i:s' format
//                'end_date_time' => $key->EndDateOrg, // Make sure it's in 'Y-m-d H:i:s' format
//            ];
//
//            $event = $this->googleService->createEvent($user, $eventData);
//            $key->CalenderSync()->create(['provider'=>'google', 'event_id'=>$event->id,'user_id'=>auth()->id()]);
//        }
        return true;
    }



    

    protected function StartupCalender($user)
    {
        if (!auth()->user()->company){
            return false;
        }
        $cycle = auth()->user()->company->cycledata;
        if (!is_null($cycle)){
            if (!$cycle->CalenderSync()->where(['user_id'=>auth()->id(),'provider'=>'google'])->first()){

                $eventData = [
                    'title' => $cycle->title,
                    'description' => 'Cycle: '.$cycle->title,
                    'start_date_time' => $cycle->start, // Make sure it's in 'Y-m-d H:i:s' format
                    'end_date_time' => $cycle->end, // Make sure it's in 'Y-m-d H:i:s' format
                ];

                $event = $this->googleService->createEvent($user, $eventData);
                $cycle->CalenderSync()->create(['provider'=>'google', 'event_id'=>$event->id,'user_id'=>auth()->id()]);
            }


        }
        $mentorshipRequests = auth()->user()->mentorship;
        foreach ($mentorshipRequests as $key) {
            if ($key->CalenderSync()->where(['user_id'=>auth()->id(),'provider'=>'google'])->first()){
                continue;
            }
            // Fetch your project events from the database
            $eventData = [
                'title' => __('admin.mentorship session'),
                'description' => 'Mentor: '.$key->mentor->name.' Startup: '.$key->user->name,
                'location' => $key->join_url,
                'start_date_time' => $key->ZoomDateOrg, // Make sure it's in 'Y-m-d H:i:s' format
                'end_date_time' => $key->EndZoomDateOrg, // Make sure it's in 'Y-m-d H:i:s' format
            ];

            $event = $this->googleService->createEvent($user, $eventData);
            $key->CalenderSync()->create(['provider'=>'google', 'event_id'=>$event->id,'user_id'=>auth()->id()]);
        }
//        $sessions = TrainingSession::where("step_id", auth()->user()->company->step)->get();
//        foreach ($sessions as $key) {
//        if ($key->CalenderSync()->where(['user_id'=>auth()->id(),'provider'=>'google'])->first()){
//            continue;
//        }
//            // Fetch your project events from the database
//            $eventData = [
//                'title' => $key->title,
//                'description' => 'Training: '.$key->title,
//                'start_date_time' => $key->start_org, // Make sure it's in 'Y-m-d H:i:s' format
//                'end_date_time' => $key->end_org, // Make sure it's in 'Y-m-d H:i:s' format
//            ];
//
//            $event = $this->googleService->createEvent($user, $eventData);
//            $key->CalenderSync()->create(['provider'=>'google', 'event_id'=>$event->id,'user_id'=>auth()->id()]);
//        }
        $finalScreenings = auth()->user()->company->finalScreening;

//        foreach ($finalScreenings as $key) {
//            if ($key->CalenderSync()->where(['user_id'=>auth()->id(),'provider'=>'google'])->first()){
//                continue;
//            }
//            // Fetch your project events from the database
//            $eventData = [
//                'title' => __('admin.Final Screening'),
//                'start_date_time' => $key->StartDateOrg, // Make sure it's in 'Y-m-d H:i:s' format
//                'end_date_time' => $key->EndDateOrg, // Make sure it's in 'Y-m-d H:i:s' format
//            ];
//
//            $event = $this->googleService->createEvent($user, $eventData);
//            $key->CalenderSync()->create(['provider'=>'google', 'event_id'=>$event->id,'user_id'=>auth()->id()]);
//        }
        $csteps = auth()->user()->company->stepdata()->where('stype',2)->first();

        if (isset($csteps)){
            if (!$csteps->CalenderSync()->where(['user_id'=>auth()->id(),'provider'=>'google'])->first()) {
                // Fetch your project events from the database
                $eventData = [
                    'title' => __('admin.judge Sessions'),
                    'start_date_time' => $csteps->from->format('Y-m-d H:i:s'), // Make sure it's in 'Y-m-d H:i:s' format
                    'end_date_time' => $csteps->to->format('Y-m-d H:i:s'), // Make sure it's in 'Y-m-d H:i:s' format
                ];

                $event = $this->googleService->createEvent($user, $eventData);
                $csteps->CalenderSync()->create(['provider'=>'google', 'event_id'=>$event->id,'user_id'=>auth()->id()]);
            }

        }
        return true;
    }

//    public function listEvents()
//    {
//        $service = $this->googleService->getCalendarService(Auth::user());
//        $events = $service->events->listEvents('primary');
//
//        return view('events', compact('events'));
//    }

}

