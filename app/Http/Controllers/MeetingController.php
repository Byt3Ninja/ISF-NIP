<?php

namespace App\Http\Controllers;

use App\Services\GoogleMeetService;
use App\Services\MicrosoftGraphService;
use App\Traits\ZoomMeetingTrait;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
//     use ZoomMeetingTrait;
//
//    const MEETING_TYPE_INSTANT = 1;
//    const MEETING_TYPE_SCHEDULE = 2;
//    const MEETING_TYPE_RECURRING = 3;
//    const MEETING_TYPE_FIXED_RECURRING_FIXED = 8;
//
//    public function showall()
//    {
//        $meetings = $this->getall();
//        return(dd($meetings));
//    }
//
//    public function show($id)
//    {
//        $meeting = $this->get($id);
//        return(dd($meeting));
//    }
//
//    public function registrants($id)
//    {
//        $registrants = $this->getregistrants($id);
//        return(dd($registrants));
//    }
//
//    public function store(Request $request)
//    {
//        $this->create($request->all());
//
//        return redirect()->route('meetings.index');
//    }
//
//    public function update($meeting, Request $request)
//    {
//        $this->update($meeting->zoom_meeting_id, $request->all());
//
//        return redirect()->route('meetings.index');
//    }
//
//    public function destroy(ZoomMeeting $meeting)
//    {
//        $this->delete($meeting->id);
//
//        return $this->sendSuccess('Meeting deleted successfully.');
//    }
    protected $googleMeet;

    public function __construct(GoogleMeetService $googleMeet)
    {
        $this->googleMeet = $googleMeet;
    }

    public function createMeeting()
    {

//        try {
            $meetingResponse = $this->googleMeet->creatMeeting();

            print_r($meetingResponse);
//        } catch (\Exception $e) {
//            echo 'Error: ' . $e->getMessage();
//        }
    }
}