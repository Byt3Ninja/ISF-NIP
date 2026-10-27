<?php
// app/Services/GoogleCalendarService.php

namespace App\Services;

use App\Settings;
// use Google_Service_Calendar;
use Carbon\Carbon;
use Google\Client;
use Google_Client;
use App\UserOauthToken;

use Google_Service_Calendar_Event;
use Illuminate\Support\Facades\Schema;
use Google\Service\Calendar as Google_Service_Calendar;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    protected $client;



    public function __construct()
    {
        try{
            $Setting = Settings::where('id', 1)->first();

            if ($Setting) {
                $GOOGLE_CLIENT_ID = $Setting->GOOGLE_CALENDAR_CLIENT_ID;
                $GOOGLE_CLIENT_SECRET = $Setting->GOOGLE_CALENDAR_CLIENT_SECRET;
                $CALENDER_GOOGLE_REDIRECT_URI = $Setting->GOOGLE_CALENDAR_REDIRECT;
            } else {
                $GOOGLE_CLIENT_ID = '';
                $GOOGLE_CLIENT_SECRET = '';
                $CALENDER_GOOGLE_REDIRECT_URI = '';
            }


            $this->client = new Google_Client();
            $this->client->setClientId($GOOGLE_CLIENT_ID);
            $this->client->setClientSecret($GOOGLE_CLIENT_SECRET);
            $this->client->setRedirectUri($CALENDER_GOOGLE_REDIRECT_URI);
            $this->client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
            $this->client->addScope('https://www.googleapis.com/auth/calendar.events');
            $this->client->addScope('https://www.googleapis.com/auth/userinfo.email');
            $this->client->addScope('openid');
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');
        }catch(\Exception $e){
            Log::error('GoogleCalendarService::__construct: ' . $e->getMessage());
        }
    }

    public function getAuthUrl()
    {

        return $this->client->createAuthUrl();
    }

    public function authenticate($code, $userId)
    {
        $this->client->authenticate($code);
        $token = $this->client->getAccessToken();
        // Save tokens in the database
        UserOauthToken::updateOrCreate(
            ['user_id' => $userId],
            [
                'provider' => 'google',
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token'] ?? '',
                'expires_at' => now()->addSeconds($token['expires_in']),
            ]
        );
    }

    public function getCalendarService($user)
    {
        // Fetch user's token
       $oauthToken = $user->oauthToken()->where('provider', 'google')->firstOrFail();

        if ($oauthToken) {
            $this->client->setAccessToken([
                'access_token' => $oauthToken->access_token,
                'refresh_token' => $oauthToken->refresh_token,
                'expires_at' => $oauthToken->expires_at,
            ]);

            // Refresh access token if needed
            if ($this->client->isAccessTokenExpired()) {
              $newToken = $this->client->fetchAccessTokenWithRefreshToken($oauthToken->refresh_token);

            if (isset($newToken['error'])) {
                   throw new \Exception("Failed to refresh access token: " . $newToken['error_description'] ?? 'Unknown error');
            }

         $oauthToken->update([
              'access_token' => $newToken['access_token'],
              'expires_at' => now()->addSeconds($newToken['expires_in']),
         ]);
        }

            return new Google_Service_Calendar($this->client);
        }

        throw new \Exception("User OAuth Token not found.");

    }
    public function createEvent($user, $eventData)
    {
        $service = $this->getCalendarService($user);

        // GV-9566: platform wall-clock -> real UTC instants. This used to
        // subtract a fixed 3 hours, i.e. assume every platform ran on UTC+3.
        $s = TimezoneService::wallClock($eventData['start_date_time'])->utc();
        $e = TimezoneService::wallClock($eventData['end_date_time'])->utc();
        $event = new Google_Service_Calendar_Event(array(
            'summary' => $eventData['title'],
            'location' => $eventData['location'] ?? '',
            'description' => $eventData['description'] ?? '',
            'start' => [
                'dateTime' => $s->format(\DateTime::RFC3339),
                'timeZone' => 'UTC'
            ],
            'end' => [
                'dateTime' => $e->format(\DateTime::RFC3339),
                'timeZone' => 'UTC'
            ]

        ));

        $createdEvent = $service->events->insert('primary', $event);
        return $createdEvent; // Returns the link to the created Google Calendar event
    }
}
