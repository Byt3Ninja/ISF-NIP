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

    /**
     * Generate Microsoft Access Token.
     */
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

    /**
     * Get User By Email.
     */
    public function getUserIdByEmail($email)
    {
        try {
            $response = $this->graph->createRequest("GET", "/users?\$filter=mail eq '{$email}'")
                ->execute();

            $data = $response->getBody();

            if (isset($data['value']) && count($data['value']) > 0) {
                return $data['value'][0]['id'];
            } else {
                Log::error("No user found with email {$email}");
                return null;
            }
        } catch (\Exception $e) {
            Log::error("Error fetching user ID by email: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * Create Microsoft Teams Meeting.
     */
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
            ],
            'isOnlineMeeting' => true,
            'isCalendarEvent' => true,
        ];

        try {
            $response = $this->graph->createRequest('POST', "/me/onlineMeetings")
                ->attachBody($meeting)
                ->execute();

            return [
                'success' => true,
                'data'    => [
                    'id' => $response->getBody()['id'],
                    'start_url' => $response->getBody()['joinUrl'],
                    'join_url' => $response->getBody()['joinUrl'],
                ],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create meeting: ' . $e->getMessage(),
            ];
        }
    }


    /**
     * Get Microsoft Teams Session Report.
     */

    public function getSessionReport(string $meetingId, ?string $userId = null)
    {
        if (is_null($userId)) {
            $organizerEmail = env('MICROSOFT_APP_EMAIL');
            $userId = $this->getUserIdByEmail($organizerEmail);

            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch user ID for the organizer email.',
                ];
            }
        }

        try {
            $response = $this->graph->createRequest(
                "GET",
                "/users/{$userId}/onlineMeetings/{$meetingId}/attendanceReports"
            )->addHeaders(["Content-Type" => "application/json"])
                ->execute();

            $data = $response->getBody();

            if (is_string($data)) {
                $data = json_decode($data, true);
            }

            return [
                'success' => true,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to list Attendance Reports: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get Microsoft Teams Session Record.
     */
    public function getSessionRecord(string $meetingId, ?string $userId = null)
    {
        if (is_null($userId)) {
            $organizerEmail = env('MICROSOFT_APP_EMAIL');
            $userId = $this->getUserIdByEmail($organizerEmail);

            if (!$userId) {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch user ID for the organizer email.',
                ];
            }
        }

        try {
            $meetingDetailsResponse = $this->graph->createRequest("GET", "/users/{$userId}/onlineMeetings/{$meetingId}")
                ->addHeaders(["Content-Type" => "application/json"])
                ->execute();

            $meetingData = $meetingDetailsResponse->getBody();

            if (!isset($meetingData['iCalUid'])) {
                return [
                    'success' => false,
                    'message' => 'This meeting is not calendar-backed and does not support recordings.',
                ];
            }

            $response = $this->graph->createRequest("GET", "/me/onlineMeetings/{$meetingId}/recordings")
                ->addHeaders(["Content-Type" => "application/json"])
                ->execute();

            $data = $response->getBody();

            if (empty($data['value'])) {
                return [
                    'success' => false,
                    'message' => 'No recordings found for this meeting.',
                ];
            }

            return [
                'success' => true,
                'data' => $data['value'],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to Retrieve Recorded Video: ' . $e->getMessage(),
            ];
        }
    }


    public function getUserIdFromMeeting(string $meetingId)
    {
        //
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
