<?php

namespace App\Services;

use App\User;
use App\Settings;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use League\OAuth2\Client\Provider\GenericProvider;

class TeamsService implements OnlineMeetingServiceInterface
{
    protected $client;
    protected $graph;
    protected $accessToken = null;

    public function __construct()
{
    $this->client = new Client();
    $this->graph = new Graph();
   

    $accessToken = $this->getAccessToken();
    $this->accessToken = $accessToken;
    if ($accessToken['status']) {
        $this->graph->setAccessToken($accessToken['data']);
    }

}

    // public function redirect($mentorship_id)
    // {
    //     $provider = new GenericProvider([
    //         'clientId'                => env('MICROSOFT_CLIENT_ID'),
    //         'clientSecret'            => env('MICROSOFT_CLIENT_SECRET_VALUE'),
    //         'redirectUri'             => env('MICROSOFT_REDIRECT_URI'),
    //         'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
    //         'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
    //         'urlResourceOwnerDetails' => '',
    //         'scopes'                  => 'openid profile User.Read OnlineMeetings.ReadWrite'
    //     ]);

    //     $authorizationUrl = $provider->getAuthorizationUrl();
    //     session(['oauth2state' => $provider->getState()]);
    //     session(['mentorship_id' => $mentorship_id]);

    //     return redirect()->away($authorizationUrl);
    // }

    // public function callback(Request $request)
    // {
    //     dd($request->session()->pull('mentorship_id'));
    //     $provider = new GenericProvider([
    //         'clientId'                => env('MICROSOFT_CLIENT_ID'),
    //         'clientSecret'            => env('MICROSOFT_CLIENT_SECRET_VALUE'),
    //         'redirectUri'             => env('MICROSOFT_REDIRECT_URI'),
    //         'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
    //         'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
    //         'urlResourceOwnerDetails' => '',
    //         'scopes'                  => 'openid profile User.Read OnlineMeetings.ReadWrite'
    //     ]);

    //     // Validate the OAuth state
    //     $state = $request->session()->pull('oauth2state');
    //     if (empty($request->input('state')) || ($request->input('state') !== $state)) {
    //         abort(403, 'Invalid state');
    //     }

    //     // Get the access token using the authorization code grant
    //     try {
    //         $accessToken = $provider->getAccessToken('authorization_code', [
    //             'code' => $request->input('code')
    //         ]);
    //         // Use the access token to interact with Microsoft Graph API
    //         $graph = new Graph();
    //         $graph->setAccessToken($accessToken->getToken());

    //         $user = $graph->createRequest('GET', '/me')
    //             ->setReturnType(Model\User::class)
    //             ->execute();

    //         $meeting = [
    //             'subject' => 'Team Meeting',
    //             'startDateTime' => '2024-05-18T14:30:34.2444915-07:00',
    //             'endDateTime' => '2024-05-18T14:40:34.2444915-07:00',
    //         ];
    //         $meeting = $graph->createRequest("POST", "/me/onlineMeetings")
    //             ->attachBody($meeting)
    //             ->setReturnType(Model\OnlineMeeting::class)
    //             ->execute();

    //         // You can now use $user object to get user details and store them in your database
    //         dd($user);
    //     } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    //         // Failed to get the access token or user details
    //         abort(500, $e->getMessage());
    //     }
    // }

    /**
     * Generate Microsoft Access Token.
     */
    protected function getAccessToken()
 {
    $user = auth()->user();

    $MICROSOFT_TENANT_ID = '';
    $MICROSOFT_CLIENT_ID = '';
    $MICROSOFT_CLIENT_SECRET_VALUE = '';

    if( !Auth::check() )
    {
        return [
            'status' => false, 
            'data' => __('admin.teams_error')
        ];
    }

    try{
        $org = $user->organization;

        if ($org) {
            $MICROSOFT_TENANT_ID = $org->microsoft_tenant_id;
            $MICROSOFT_CLIENT_ID = $org->microsoft_client_id;
            $MICROSOFT_CLIENT_SECRET_VALUE = $org->microsoft_client_secret_value;
        }
        else{
            $setting = Settings::where('id', 1)->first();
            if ($setting) {
                $MICROSOFT_TENANT_ID = $setting->MICROSOFT_TENANT_ID;
                $MICROSOFT_CLIENT_ID = $setting->MICROSOFT_CLIENT_ID;
                $MICROSOFT_CLIENT_SECRET_VALUE = $setting->MICROSOFT_CLIENT_SECRET_VALUE;
            }
        }
    }catch(\Exception $e){
        Log::error('TeamsService: Error retrieving Microsoft credentials for user.', ['user_id' => $user->id, 'error' => $e->getMessage()]);
    }

    if (empty($MICROSOFT_TENANT_ID) || empty($MICROSOFT_CLIENT_ID) || empty($MICROSOFT_CLIENT_SECRET_VALUE)) {
        return [
            'status' => false, 
            'data' => __('admin.teams_error')
        ];
    }

    if ($MICROSOFT_TENANT_ID && $MICROSOFT_CLIENT_ID && $MICROSOFT_CLIENT_SECRET_VALUE) {
        $response = $this->client->post('https://login.microsoftonline.com/' . $MICROSOFT_TENANT_ID . '/oauth2/v2.0/token', [
            'form_params' => [
                'client_id' => $MICROSOFT_CLIENT_ID,
                'client_secret' => $MICROSOFT_CLIENT_SECRET_VALUE,
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ],
        ]);

        $body = json_decode($response->getBody(), true);
        return [
            'status' => true, 
            'data' => $body['access_token']
        ];
    }

}
    

    /**
     * Get User By Email.
     */
    public function getUserIdByEmail($email)
    {
        if(!$this->accessToken['status']){
           return  $this->accessToken;
        }
        $user = $this->graph->createRequest("GET", "/users?\$filter=mail eq '{$email}'")
            ->setReturnType(Model\User::class)
            ->execute();

        return [
            'status' => true,
            'data' => $user[0]->getId()
        ]; // Get the user ID
    }

    /**
     * Create Microsoft Teams Meeting.
     */
    public function createMeeting($data)
    {
        $user = Auth::user();

        try{
            $org = $user->organization;
    
            if ($org) {
                $userId = $this->getUserIdByEmail($org->microsoft_app_email);
            }
            else{
            $setting = Settings::where('id', 1)->first();

              if ($setting) {
                $userId = $this->getUserIdByEmail($setting->MICROSOFT_APP_EMAIL);
              }
            }
            
        }catch(\Exception $e){
            Log::error('TeamsService: Error retrieving Microsoft credentials for user.', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    
        $meeting = [
            'subject' => $data['topic'],
            'startDateTime' => $this->toZoomTimeFormat($data['start_time']),
            'endDateTime' => $this->toZoomTimeFormat($data['start_time'], $data['duration']),
            "lobbyBypassSettings" => [
                "scope" => "everyone",
                "isDialInBypassEnabled" => true
            ],
             'allowRecording' => true,
             'recordAutomatically' => true,

        ];


        if (!$this->accessToken['status'] || !$userId['status']) {
            return [ 
                'success' => false,
                'data' => $this->accessToken['data'],
            ];
        }
        $userId = $userId['data'];

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
     * Get Microsoft Teams Attendance Report List.
     */

    // public function getSessionReport(string $meetingId, ?string $userId = null)
    // {
    //     $accessToken = $this->getAccessToken();

    //     if (!$accessToken) {
    //         return [
    //             'success' => false,
    //             'message' => 'Failed to retrieve access token.',
    //         ];
    //     }

    //     try {
    //         $url = "/me/onlineMeetings/{$meetingId}/attendanceReports";

    //         $response = $this->graph->createRequest("GET", $url)
    //             ->addHeaders([
    //                 "Content-Type" => "application/json",
    //                 "Authorization" => "Bearer {$accessToken}",
    //             ])
    //             ->execute();

    //         $data = $response->getBody();
    //         if (is_string($data)) {
    //             $data = json_decode($data, true);
    //         }

    //         return [
    //             'success' => true,
    //             'data' => $data,
    //         ];
    //     } catch (\Exception $e) {
    //         return [
    //             'success' => false,
    //             'message' => 'Failed to fetch Attendance Report: ' . $e->getMessage(),
    //         ];
    //     }
    // }

    public function getSessionReport(string $meetingId, ?string $userId = null)
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve access token.',
            ];
        }

        $Setting = Settings::where('id', 1)->first();
        $userId = $this->getUserIdByEmail($Setting->MICROSOFT_APP_EMAIL);

        try {
            $url = "/users/{$userId}/onlineMeetings/{$meetingId}/attendanceReports";

            $response = $this->graph->createRequest("GET", $url)
                ->addHeaders([
                    "Content-Type" => "application/json",
                    "Authorization" => "Bearer {$accessToken}",
                ])
                ->execute();

            $data = $response->getBody();

            return [
                'success' => true,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to Fetch Attendance Report: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get Microsoft Teams Attendance Report ID.
     */

    // public function getMeetingAttendanceReport(string $meetingId, string $reportId)
    // {
    //     $accessToken = $this->getAccessToken();

    //     if (!$accessToken) {
    //         return [
    //             'success' => false,
    //             'message' => 'Failed to retrieve access token.',
    //         ];
    //     }

    //     try {
    //         $url = "/me/onlineMeetings/{$meetingId}/attendanceReports/{$reportId}";

    //         $response = $this->graph->createRequest("GET", $url)
    //             ->addHeaders([
    //                 "Content-Type" => "application/json",
    //                 "Authorization" => "Bearer {$accessToken}",
    //             ])
    //             ->execute();

    //         $data = $response->getBody();
    //         if (is_string($data)) {
    //             $data = json_decode($data, true);
    //         }

    //         return [
    //             'success' => true,
    //             'data' => $data,
    //         ];
    //     } catch (\Exception $e) {
    //         return [
    //             'success' => false,
    //             'message' => 'Failed to fetch Meeting Attendance Report: ' . $e->getMessage(),
    //         ];
    //     }
    // }

    public function getMeetingAttendanceReport(string $meetingId, string $reportId)
    {
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve access token.',
            ];
        }

        // Fetch the Microsoft email from settings
        $settings = Settings::where('id', 1)->first();
        if (!$settings || !$settings->MICROSOFT_APP_EMAIL) {
            return [
                'success' => false,
                'message' => 'Microsoft App Email not found in settings.',
            ];
        }

        // Get user ID using email
        $userId = $this->getUserIdByEmail($settings->MICROSOFT_APP_EMAIL);

        if (!$userId) {
            return [
                'success' => false,
                'message' => 'Unable to fetch User ID for the provided Microsoft App Email.',
            ];
        }

        try {
            $url = "/users/{$userId}/onlineMeetings/{$meetingId}/attendanceReports/{$reportId}";

            $response = $this->graph->createRequest("GET", $url)
                ->addHeaders([
                    "Content-Type" => "application/json",
                    "Authorization" => "Bearer {$accessToken}",
                ])
                ->execute();

            $data = $response->getBody();
            if (!is_array($data)) {
                $data = json_decode($data, true);
            }

            return [
                'success' => true,
                'data' => $data,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to fetch Meeting Attendance Report: ' . $e->getMessage(),
            ];
        }
    }


    /**
     * Get Microsoft Teams Session Record.
     */
    public function getSessionRecord(string $meetingId, ?string $userId = null)
    {
        if (is_null($userId)) {
            $Setting = Settings::where('id', 1)->first();
            $userId = $this->getUserIdByEmail($Setting->MICROSOFT_APP_EMAIL);
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


    public function toZoomTimeFormat($timestamp, $duration = null)
    {
        if (!is_numeric($timestamp)) {
            return null;
        }

        $start = Carbon::createFromTimestamp($timestamp)->utc();

        if ($duration) {
         return $start->copy()->addMinutes($duration)->toIso8601String();
        }

        return $start->toIso8601String();
    }
}
