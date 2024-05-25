<?php
namespace App\Services;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Illuminate\Http\Request;

class GoogleMeetService
{
    protected $keyPath;
    protected $client;


    public function __construct()
    {
        $this->keyPath = public_path('google-service-account.json');

        $this->client = new Google_Client();
        $this->client->setAuthConfigFile($this->keyPath);
        $this->client->addScope(Google_Service_Calendar::CALENDAR);
//        $this->client->useApplicationDefaultCredentials();
    }

    public function creatMeeting(){
        $service = new Google_Service_Calendar($this->client);

        $event = new Google_Service_Calendar_Event([
            'summary' => 'Google Meet Test Event',
            'description' => 'A chance to hear more about Google Meet and how to integrate it.',
            'start' => [
                'dateTime' => '2024-05-23T10:00:00-07:00',
                'timeZone' => 'America/Los_Angeles',
            ],
            'end' => [
                'dateTime' => '2024-05-23T11:00:00-07:00',
                'timeZone' => 'America/Los_Angeles',
            ],
            'conferenceData' => [
                'createRequest' => [
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    'requestId' => (string)uniqid()
                ]
            ]
        ]);

        $event = $service->events->insert('primary', $event);

        return response()->json(['event' => $event]);
    }

}