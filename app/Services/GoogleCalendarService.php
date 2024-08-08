<?php
// app/Services/GoogleCalendarService.php

namespace App\Services;

use Google_Client;
use Google_Service_Calendar;
use App\UserOauthToken;
use Google_Service_Calendar_Event;
use Carbon\Carbon;

class GoogleCalendarService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $this->client->addScope(Google_Service_Calendar::CALENDAR);
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
                'access_token' => $token['access_token'],
                'refresh_token' => $token['refresh_token']??'',
                'expires_at' => now()->addSeconds($token['expires_in']),
            ]
        );
    }

    public function getCalendarService($user)
    {
        // Fetch user's token
        $oauthToken = $user->oauthToken;

        if ($oauthToken) {
            $this->client->setAccessToken([
                'access_token' => $oauthToken->access_token,
                'refresh_token' => $oauthToken->access_token,
                'expires_at' => $oauthToken->expires_at,
            ]);

            // Refresh access token if needed
            if ($this->client->isAccessTokenExpired()) {
                $this->client->fetchAccessTokenWithRefreshToken($oauthToken->refresh_token);


                // Update the tokens in the database
                $oauthToken->update([
                    'access_token' => $this->client->getAccessToken()['access_token'],
                    'expires_at' => $this->client->getAccessToken()['expires_at'],
                ]);
            }

            return new Google_Service_Calendar($this->client);
        }

        throw new \Exception("User OAuth Token not found.");
    }
    public function createEvent($user, $eventData)
    {
        $service = $this->getCalendarService($user);

        $s = new \DateTime($eventData['start_date_time']);
        $e=new \DateTime($eventData['end_date_time']);
        $event = new Google_Service_Calendar_Event(array(
            'summary' => $eventData['title'],
            'location' => $eventData['location'] ?? '',
            'description' => $eventData['description'] ?? '',
            'start' =>[
                'dateTime'=>$s->format(\DateTime::RFC3339),
                'timeZone'=>'Asia/Riyadh'
                ],
            'end' =>[
                'dateTime'=>$e->format(\DateTime::RFC3339),
                'timeZone'=>'Asia/Riyadh'
            ]

        ));

        $createdEvent = $service->events->insert('primary', $event);
        return $createdEvent; // Returns the link to the created Google Calendar event
    }
}

