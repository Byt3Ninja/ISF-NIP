<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Settings;
use GuzzleHttp\Client;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use League\OAuth2\Client\Provider\GenericProvider;

class TeamsService implements OnlineMeetingServiceInterface
{
    protected $client;
    protected $graph;

    public function __construct()
    {
        $this->client = new Client();
        $this->graph = new Graph();
        $this->graph->setAccessToken($this->getAccessToken());
    }
    public function redirect($mentorship_id)
    {
        $provider = new GenericProvider([
            'clientId'                => env('MICROSOFT_CLIENT_ID'),
            'clientSecret'            => env('MICROSOFT_CLIENT_SECRET_VALUE'),
            'redirectUri'             => env('MICROSOFT_REDIRECT_URI'),
            'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => '',
            'scopes'                  => 'openid profile User.Read OnlineMeetings.ReadWrite'
        ]);

        $authorizationUrl = $provider->getAuthorizationUrl();
        session(['oauth2state' => $provider->getState()]);
        session(['mentorship_id' => $mentorship_id]);

        return redirect()->away($authorizationUrl);
    }

    public function callback(Request $request)
    {
        dd($request->session()->pull('mentorship_id'));
        $provider = new GenericProvider([
            'clientId'                => env('MICROSOFT_CLIENT_ID'),
            'clientSecret'            => env('MICROSOFT_CLIENT_SECRET_VALUE'),
            'redirectUri'             => env('MICROSOFT_REDIRECT_URI'),
            'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => '',
            'scopes'                  => 'openid profile User.Read OnlineMeetings.ReadWrite'
        ]);

        // Validate the OAuth state
        $state = $request->session()->pull('oauth2state');
        if (empty($request->input('state')) || ($request->input('state') !== $state)) {
            abort(403, 'Invalid state');
        }

        // Get the access token using the authorization code grant
        try {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $request->input('code')
            ]);
            // Use the access token to interact with Microsoft Graph API
            $graph = new Graph();
            $graph->setAccessToken($accessToken->getToken());

            $user = $graph->createRequest('GET', '/me')
                ->setReturnType(Model\User::class)
                ->execute();

            $meeting = [
                'subject' => 'Team Meeting',
                'startDateTime' => '2024-05-18T14:30:34.2444915-07:00',
                'endDateTime' => '2024-05-18T14:40:34.2444915-07:00',
            ];
            $meeting = $graph->createRequest("POST", "/me/onlineMeetings")
                ->attachBody($meeting)
                ->setReturnType(Model\OnlineMeeting::class)
                ->execute();

            // You can now use $user object to get user details and store them in your database
            dd($user);
        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Failed to get the access token or user details
            abort(500, $e->getMessage());
        }
    }

    protected function getAccessToken()
    {
        if (Schema::hasTable('settings')) {
            $Setting = Settings::where('id', 1)->first();

            if ($Setting) {
                $MICROSOFT_TENANT_ID = $Setting->MICROSOFT_TENANT_ID;
                $MICROSOFT_CLIENT_ID = $Setting->MICROSOFT_CLIENT_ID;
                $MICROSOFT_CLIENT_SECRET_VALUE = $Setting->MICROSOFT_CLIENT_SECRET_VALUE;
            } else {
                $MICROSOFT_TENANT_ID = '';
                $MICROSOFT_CLIENT_ID = '';
                $MICROSOFT_CLIENT_SECRET_VALUE = '';
            }


            $response = $this->client->post('https://login.microsoftonline.com/' . $MICROSOFT_TENANT_ID . '/oauth2/v2.0/token', [
                'form_params' => [
                    'client_id' => $MICROSOFT_CLIENT_ID,
                    'client_secret' => $MICROSOFT_CLIENT_SECRET_VALUE,
                    'scope' => 'https://graph.microsoft.com/.default',
                    'grant_type' => 'client_credentials',
                ],
            ]);

            $body = json_decode($response->getBody(), true);
            return $body['access_token'];
        }
    }

    public function getUserIdByEmail($email)
    {
        $user = $this->graph->createRequest("GET", "/users?\$filter=mail eq '{$email}'")
            ->setReturnType(Model\User::class)
            ->execute();

        return $user[0]->getId(); // Get the user ID

    }

    public function createMeeting($data)
    {
        $Setting = Settings::where('id', 1)->first();
        $userId = $this->getUserIdByEmail($Setting->MICROSOFT_APP_EMAIL);
        $meeting = [
            'subject' => $data['topic'],
            'startDateTime' => $this->toZoomTimeFormat($data['start_time']),
            'endDateTime' => $this->toZoomTimeFormat($data['start_time'], $data['duration']),
            "lobbyBypassSettings" => [
                "scope" => "everyone",
                "isDialInBypassEnabled" => true
            ]

        ];


        $response = $this->graph->createRequest('POST', "/users/{$userId}/onlineMeetings")
            ->attachBody($meeting)
            //            ->setReturnType(\ArrayObject::class)
            ->execute();
        return [
            'success' => 201,
            'data'    => [
                'id' => $response->getBody()['id'],
                'start_url' => $response->getBody()['joinUrl'],
                'join_url' => $response->getBody()['joinUrl'],
            ],
        ];
    }

    /**
     * Get Session Report.
     */
    public function getSessionReport(string $meetingId)
    {
        try {
            $response = $this->graph->createRequest("GET", "/onlineMeetings/{$meetingId}")
                ->addHeaders(["Content-Type" => "application/json"])
                ->execute();

            $data = $response->getBody();

            return [
                'success' => true,
                'data' => json_decode($data, true),
            ];
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('TeamsService->getSessionReport: Graph API Error - ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to Retrieve Session Report: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('TeamsService->getSessionReport: General Error - ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to Retrieve Session Report: ' . $e->getMessage(),
            ];
        }
    }


    /**
     * Get the recorded video for a session.
     */
    public function getSessionRecord(string $meetingId)
    {
        try {
            $response = $this->graph->createRequest("GET", "/me/onlineMeetings/{meetingId}/recordings")
                ->addHeaders(["Content-Type" => "application/json"])
                ->execute();

            $data = $response->getBody();

            return [
                'success' => true,
                'data' => json_decode($data, true),
            ];
        } catch (\Microsoft\Graph\Exception\GraphException $e) {
            Log::error('TeamsService->getSessionRecordedVideo: Graph API Error - ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to Retrieve Recorded Video: ' . $e->getMessage(),
            ];
        } catch (\Exception $e) {
            Log::error('TeamsService->getSessionRecordedVideo: General Error - ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to Retrieve Recorded Video: ' . $e->getMessage(),
            ];
        }
    }

    public function toZoomTimeFormat(string $dateTime, $duration = null)
    {
        try {
            if (is_null($duration)) {
                $date = new \DateTime($dateTime);
                return $date->format('Y-m-d\TH:i:s.u\Z');
            } else {
                $date = new \DateTime($dateTime);
                $interval = new \DateInterval('PT' . $duration . 'M');
                $date->add($interval);

                return $date->format('Y-m-d\TH:i:s.u\Z');
            }
        } catch (\Exception $e) {
            Log::error('Teams->TimeFormat : ' . $e->getMessage());
            return '';
        }
    }
}
