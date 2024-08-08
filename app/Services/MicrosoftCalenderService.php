<?php
namespace App\Services;
use App\UserOauthToken;
use GuzzleHttp\Client;
use Microsoft\Graph\Graph;
use League\OAuth2\Client\Provider\GenericProvider;
use Microsoft\Graph\Model;


class MicrosoftCalenderService{
    public $provider;
    protected $client;
    protected $graph;
    public function __construct()
    {
        $this->provider = new GenericProvider([
            'clientId'                => env('MICROSOFT_CLIENT_ID'),
            'clientSecret'            => env('MICROSOFT_CLIENT_SECRET_VALUE'),
            'redirectUri'             => env('MICROSOFT_REDIRECT_URI'),
            'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => '',
            'scopes'                  => 'Calendars.ReadWrite'
        ]);
        $this->client = new Client();
        $this->graph = new Graph();

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
                    'refresh_token' => $accessToken->getRefreshToken()??'',
//                    'expires_at' => now()->addSeconds($accessToken->getExpires()),
                ]
            );

        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Failed to get the access token or user details
            abort(500, $e->getMessage());
        }
    }

    public  function createEvent($user,$eventData){
        // Fetch user's token
        $oauthToken = $user->oauthToken()->where('provider', 'microsoft')->firstOrFail();

        if ($oauthToken) {
            $this->graph->setAccessToken($oauthToken->access_token);

        }
        $microsoftEvent = new Model\Event([
            'subject' => $eventData['title'],
            'start' => [
                'dateTime' => $this->toZoomTimeFormat($eventData['start_date_time']),
                'timeZone'=>'Asia/Riyadh'
            ],
            'end' => [
                'dateTime' => $this->toZoomTimeFormat($eventData['end_date_time']),
                'timeZone'=>'Asia/Riyadh'
            ],
            'location' => [
                'displayName' => 'Online'
            ],
            'onlineMeeting' => [
                'joinUrl' => $eventData['location']??null
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
    public function toZoomTimeFormat(string $dateTime, $duration=null)
    {
        try {
            if (is_null($duration)){
                $date = new \DateTime($dateTime);
                return $date->format('Y-m-d\TH:i:s.u\Z');
            }else{
                $date = new \DateTime($dateTime);
                $interval = new \DateInterval('PT' . $duration . 'M');
                $date->add($interval);

                return $date->format('Y-m-d\TH:i:s.u\Z');
            }

        } catch (\Exception $e) {
            Log::error('Teams->TimeFormat : '.$e->getMessage());
            return '';
        }
    }


}