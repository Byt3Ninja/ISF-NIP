<?php

namespace App\Services;

use App\Settings;
use GuzzleHttp\Client;
use App\UserOauthToken;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Illuminate\Support\Facades\Schema;
use League\OAuth2\Client\Provider\GenericProvider;

class MicrosoftCalenderService
{
    public $provider;
    protected $client;
    protected $graph;
    public function __construct()
    {
        try{
            $Setting = Settings::where('id', 1)->first();

            if ($Setting) {
                $MICROSOFT_CLIENT_ID = $Setting->MICROSOFT_CLIENT_ID;
                $MICROSOFT_CLIENT_SECRET_VALUE = $Setting->MICROSOFT_CLIENT_SECRET_VALUE;
                $MICROSOFT_REDIRECT_URI = $Setting->MICROSOFT_REDIRECT_URI;
            } else {
                $MICROSOFT_CLIENT_ID = '';
                $MICROSOFT_CLIENT_SECRET_VALUE = '';
                $MICROSOFT_REDIRECT_URI = '';
            }
            $this->provider = new GenericProvider([
                'clientId'                => $MICROSOFT_CLIENT_ID,
                'clientSecret'            => $MICROSOFT_CLIENT_SECRET_VALUE,
                'redirectUri'             => $MICROSOFT_REDIRECT_URI,
                'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
                'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
                'urlResourceOwnerDetails' => '',
                'scopes'                  => 'Calendars.ReadWrite'
            ]);
            $this->client = new Client();
            $this->graph = new Graph();
        }catch(\Exception $e){
            Log::error('MicrosoftCalenderService::__construct: ' . $e->getMessage());
        }
    }
    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
    }
    public function redirect()
    {
        $authorizationUrl = $this->provider->getAuthorizationUrl();
        session(['oauth2state' => $this->provider->getState()]);

        return redirect()->away($authorizationUrl);
    }

    public function authenticate($code, $userId)
    {
        // Get the access token using the authorization code grant
        try {
            $accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
            // Use the access token to interact with Microsoft Graph API

            $this->graph->setAccessToken($accessToken->getToken());

            // Save tokens in the database
            UserOauthToken::updateOrCreate(
                ['user_id' => $userId],
                [
                    'provider' => 'microsoft',
                    'access_token' => $accessToken->getToken(),
                    'refresh_token' => $accessToken->getRefreshToken() ?? '',
                    //                    'expires_at' => now()->addSeconds($accessToken->getExpires()),
                ]
            );
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Failed to get the access token or user details
            abort(500, $e->getMessage());
        }
    }

    public  function createEvent($user, $eventData)
    {
        // Fetch user's token
        $oauthToken = $user->oauthToken()->where('provider', 'microsoft')->firstOrFail();

        if ($oauthToken) {
            $this->graph->setAccessToken($oauthToken->access_token);
        }
        $microsoftEvent = new Model\Event([
            'subject' => $eventData['title'],
            'start' => [
                'dateTime' => $this->toZoomTimeFormat($eventData['start_date_time']),
                'timeZone' => 'UTC'
            ],
            'end' => [
                'dateTime' => $this->toZoomTimeFormat($eventData['end_date_time']),
                'timeZone' => 'UTC'
            ],
            'location' => [
                'displayName' => 'Online'
            ],
            'onlineMeeting' => [
                'joinUrl' => $eventData['location'] ?? null
            ]

        ]);

        try {
            $createdEvent = $this->graph->createRequest('POST', '/me/events')
                ->attachBody($microsoftEvent)
                ->setReturnType(Model\Event::class)
                ->execute();
            return $createdEvent;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create event: ' . $e->getMessage()], 400);
        }
    }
    /**
     * The event's platform wall-clock as a UTC instant (GV-9566). This used to
     * read it as Asia/Riyadh and subtract a fixed 3 hours.
     */
    public function toZoomTimeFormat(string $dateTime, $duration = null)
    {
        $moment = TimezoneService::wallClock($dateTime);

        if (is_null($moment)) {
            Log::error('Teams->TimeFormat : unparseable date ' . $dateTime);
            return '';
        }

        if (!is_null($duration)) {
            $moment = $moment->copy()->addMinutes((int) $duration);
        }

        return $moment->copy()->utc()->format('Y-m-d\TH:i:s.u\Z');
    }
}
