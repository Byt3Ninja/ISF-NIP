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
        $orgId = optional(optional($user)->organization)->id;

        // GV-9566: read the start in the meeting's own zone (the caller's step or
        // organization), not the creating user's organization zone.
        $startsAt = TimezoneService::wallClock($data['start_time'] ?? null, !empty($data['timezone']) && TimezoneService::isValid($data['timezone'])
            ? $data['timezone']
            : TimezoneService::platform($orgId));

        if (Settings::hasReachedMaxConcurrentSessions($startsAt, $data['duration'] ?? null, $orgId)) {
            return [
                'success' => false,
                'data'    => __('settings.max_concurrent_sessions_reached'),
            ];
        }

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
    
        $meetingTimezone = !empty($data['timezone']) && TimezoneService::isValid($data['timezone'])
            ? $data['timezone']
            : TimezoneService::platform($orgId);

        $meeting = [
            'subject' => $data['topic'],
            'startDateTime' => $this->toZoomTimeFormat($data['start_time'], null, $meetingTimezone),
            'endDateTime' => $this->toZoomTimeFormat($data['start_time'], $data['duration'], $meetingTimezone),
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


       public function getSessionReport(string $meetingId, ?string $userId = null)
    {
            // 1) Acquire access token (keep your getAccessToken implementation)
    $accessTokenResult = $this->getAccessToken();
    $accessToken = is_array($accessTokenResult)
        ? ($accessTokenResult['data'] ?? $accessTokenResult['access_token'] ?? null)
        : $accessTokenResult;

    if (!is_scalar($accessToken) || $accessToken === '') {
        Log::error('getSessionReport: invalid/missing access token', ['accessTokenResult' => $accessTokenResult]);
        return [
            'success' => false,
            'message' => 'Failed to retrieve a valid access token.',
            'details' => $accessTokenResult,
        ];
    }
    $accessToken = (string) $accessToken;

    // 2) Resolve userId (prefer passed-in, otherwise from settings via getUserIdByEmail)
    if ($userId) {
        $userIdSource = 'passed_in';
    } else {
        $setting = Settings::where('id', 1)->first();
        $microsoftEmail = $setting->MICROSOFT_APP_EMAIL ?? null;
        $userIdResult = $this->getUserIdByEmail($microsoftEmail);

        $userId = is_array($userIdResult)
            ? ($userIdResult['data'] ?? $userIdResult['user_id'] ?? $userIdResult['id'] ?? null)
            : $userIdResult;

        $userIdSource = 'from_setting';
    }

    if (!is_scalar($userId) || $userId === '' || $userId === null) {
        Log::error('getSessionReport: invalid userId', [
            'meetingId' => $meetingId,
            'userId_raw' => $userId ?? $userIdResult ?? null,
            'userIdSource' => $userIdSource ?? null,
        ]);
        return [
            'success' => false,
            'message' => 'Failed to retrieve a valid userId for building Graph URL.',
            'details' => $userIdResult ?? $userId,
        ];
    }
    $userId = (string)$userId;

    // 3) helper to normalize different kinds of responses safely
    $normalizeBody = function ($resp) {
        if (method_exists($resp, 'getBody')) {
            $candidate = $resp->getBody();
        } else {
            $candidate = $resp;
        }

        // If candidate is a string (typical), decode JSON if possible
        if (is_string($candidate)) {
            $raw = $candidate;
            $decoded = json_decode($raw, true);
            $data = json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
            return ['raw' => $raw, 'data' => $data];
        }

        // If candidate is array/object (SDK sometimes returns that), convert safely
        if (is_array($candidate) || is_object($candidate)) {
            $raw = @json_encode($candidate, JSON_UNESCAPED_UNICODE);
            $data = is_array($candidate) ? $candidate : (array)$candidate;
            return ['raw' => $raw, 'data' => $data];
        }

        // Fallback: attempt string cast inside try/catch
        try {
            $raw = (string)$candidate;
            $decoded = json_decode($raw, true);
            $data = json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
            return ['raw' => $raw, 'data' => $data];
        } catch (\Throwable $e) {
            return ['raw' => '<<unserializable_response>>', 'data' => null];
        }
    };

    try {
        // A) Get list of attendanceReports (users endpoint required for app-only tokens)
        $reportsUrl = "/users/{$userId}/onlineMeetings/{$meetingId}/attendanceReports";
        $reportsResponse = $this->graph->createRequest('GET', $reportsUrl)
            ->addHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->execute();

        $normalizedReports = $normalizeBody($reportsResponse);
        $reportsRaw = $normalizedReports['raw'];
        $reportsData = $normalizedReports['data'];

        // Find first reportId
        $reportId = null;
        if (is_array($reportsData) && !empty($reportsData['value']) && isset($reportsData['value'][0]['id'])) {
            $reportId = $reportsData['value'][0]['id'];
        } elseif (is_array($reportsData) && isset($reportsData[0]['id'])) {
            $reportId = $reportsData[0]['id'];
        }

        if (!$reportId) {
            Log::info('getSessionReport: no attendance reports found', [
                'meetingId' => $meetingId,
                'userId' => $userId,
                'reports_sample' => is_array($reportsData) ? array_slice($reportsData, 0, 3) : substr((string)$reportsRaw, 0, 500),
            ]);

            return [
                'success' => false,
                'message' => 'No attendance report found for this meeting.',
                'details' => $reportsData,
                'raw_reports' => $reportsRaw,
            ];
        }

        // B) Fetch the attendance report details (single report endpoint requested)
        $reportUrl = "/users/{$userId}/onlineMeetings/{$meetingId}/attendanceReports/{$reportId}";
        $reportResponse = $this->graph->createRequest('GET', $reportUrl)
            ->addHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->execute();

        $normalizedReport = $normalizeBody($reportResponse);
        $reportRaw = $normalizedReport['raw'];
        $reportData = $normalizedReport['data'];
        $reportStatus = method_exists($reportResponse, 'getStatusCode') ? $reportResponse->getStatusCode() : null;

        // C) Fetch the attendanceRecords (per-user rows) for that report
        $attendanceUrl = "/users/{$userId}/onlineMeetings/{$meetingId}/attendanceReports/{$reportId}/attendanceRecords";
        $attendanceResponse = $this->graph->createRequest('GET', $attendanceUrl)
            ->addHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ])->execute();

        $normalizedAttendance = $normalizeBody($attendanceResponse);
        $attendanceRaw = $normalizedAttendance['raw'];
        $attendanceData = $normalizedAttendance['data']; // typically ['value' => [ ... ]]

        // Extract participants array from attendanceData
        $participants = [];
        if (is_array($attendanceData) && !empty($attendanceData['value']) && is_array($attendanceData['value'])) {
            $participants = $attendanceData['value'];
        } elseif (is_array($attendanceData) && !empty($attendanceData)) {
            // sometimes the API may return array directly
            $participants = $attendanceData;
        }

        // Normalize participants to consistent shape
        $normalizedParticipants = [];
        foreach ($participants as $p) {
            $identity = $p['identity'] ?? ($p['user'] ?? null);
            // handle nested user inside identity
            $userNode = is_array($identity) && isset($identity['user']) ? $identity['user'] : $identity;

            $normalizedParticipants[] = [
                'emailAddress' => $p['emailAddress'] ?? ($userNode['upn'] ?? $userNode['email'] ?? null),
                'totalAttendanceInSeconds' => $p['totalAttendanceInSeconds'] 
                    ?? ($p['totalAttendanceSeconds'] ?? ($p['durationSeconds'] ?? ($p['totalAttendance'] ?? 0))),
                'role' => $p['role'] ?? null,
                'identity' => $identity,
                'attendanceIntervals' => $p['attendanceIntervals'] ?? ($p['intervals'] ?? []),
                '_raw' => $p,
            ];
        }

        // Build result
        $result = [
            'success' => ($reportStatus === null) ? true : ($reportStatus >= 200 && $reportStatus < 300),
            'status_code' => $reportStatus,
            'report_id' => $reportId,
            'data' => is_array($reportData) ? $reportData : null,       // the report details body
            'participants' => $normalizedParticipants,                  // normalized participant rows
            'attendance_raw' => $attendanceRaw,
            'raw_report' => $reportRaw,
            'raw_reports_list' => $reportsRaw,
            'attendance_count' => count($normalizedParticipants),
            'message' => ($reportStatus !== null && ($reportStatus < 200 || $reportStatus >= 300)) ? 'Non-2xx response' : 'OK',
            'details' => is_array($reportData) ? $reportData : $reportRaw,
        ];

        Log::debug('getSessionReport completed', [
            'meetingId' => $meetingId,
            'userId' => $userId,
            'reportId' => $reportId,
            'participants_count' => count($normalizedParticipants),
        ]);

        return $result;
    } catch (\GuzzleHttp\Exception\RequestException $re) {
        $resp = $re->hasResponse() ? $re->getResponse()->getBody() : null;
        $respRaw = $resp ? (method_exists($resp, 'getBody') ? (string)$resp->getBody() : (string)$resp) : null;
        Log::error('RequestException in getSessionReport', [
            'meetingId' => $meetingId,
            'userId' => $userId,
            'error' => $re->getMessage(),
            'response' => $respRaw,
        ]);
        return [
            'success' => false,
            'message' => 'RequestException fetching attendance report/records.',
            'details' => $respRaw ?? $re->getMessage(),
        ];
    } catch (\Throwable $e) {
        $err = $e->getMessage();
        if (!is_string($err)) {
            $err = json_encode($err);
        }
        Log::error('Exception in getSessionReport', [
            'meetingId' => $meetingId,
            'userId' => $userId,
            'error' => $err,
            'trace' => $e->getTraceAsString(),
        ]);
        return [
            'success' => false,
            'message' => 'Unexpected error: ' . $err,
            'details' => null,
        ];
    }

    }


    /**
     * Get Microsoft Teams Session Record.
     */
    public function getSessionRecord(string $meetingId, ?string $userId = null)
    {
         if (is_null($userId)) {
        $setting = Settings::where('id', 1)->first();
        $userIdResult = $this->getUserIdByEmail($setting->MICROSOFT_APP_EMAIL ?? null);
        $userId = is_array($userIdResult) ? ($userIdResult['data'] ?? $userIdResult['id'] ?? $userIdResult['user_id'] ?? null) : $userIdResult;
    }

    if (!is_scalar($userId) || $userId === '') {
        Log::error('getSessionRecord: invalid userId', ['meetingId' => $meetingId, 'userId_raw' => $userIdResult ?? $userId]);
        return [
            'success' => false,
            'message' => 'Failed to fetch user ID for the organizer email or userId invalid.',
            'details' => $userIdResult ?? $userId,
        ];
    }
    $userId = (string)$userId;

    try {
        $endpoint = "/users/{$userId}/onlineMeetings/{$meetingId}/recordings";
        $recordingsResponse = $this->graph->createRequest("GET", $endpoint)
            ->addHeaders(["Content-Type" => "application/json"])
            ->execute();

        $raw = method_exists($recordingsResponse, 'getBody') ? $recordingsResponse->getBody() : $recordingsResponse;
        if (is_string($raw)) {
            $data = json_decode($raw, true) ?: $raw;
        } elseif (is_object($raw) || is_array($raw)) {
            $data = (array)$raw;
        } else {
            $data = $raw;
        }

        if (is_array($data) && !empty($data['value'])) {
            return [
                'success' => true,
                'data' => $data['value'],
                'raw' => is_string($raw) ? $raw : json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
                'source' => 'graph_call_recordings',
            ];
        }

        return [
            'success' => false,
            'message' => 'No call recordings found via Graph recordings endpoint.',
            'details' => $data,
            'raw' => is_string($raw) ? $raw : json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
        ];

    } catch (\GuzzleHttp\Exception\RequestException $re) {
        $resp = $re->hasResponse() ? (string)$re->getResponse()->getBody() : null;
        Log::error('RequestException in getSessionRecord', ['endpoint' => $endpoint ?? null, 'error' => $re->getMessage(), 'response' => $resp]);
        return [
            'success' => false,
            'message' => 'RequestException fetching call recordings',
            'details' => $resp ?? $re->getMessage(),
        ];
    } catch (\Throwable $e) {
        $err = $e->getMessage();
        if (is_array($err) || is_object($err)) {
            $err = json_encode($err, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        }
        Log::error('Exception in getSessionRecord', ['endpoint' => $endpoint ?? null, 'error' => $err, 'trace' => $e->getTraceAsString()]);
        return [
            'success' => false,
            'message' => 'Failed to Retrieve Call Recordings: ' . (is_string($err) ? $err : json_encode($err)),
            'details' => null,
        ];
    }
    }


    /**
     * Teams wants a UTC instant (GV-9566). Accepts unix timestamps (training)
     * and platform wall-clock strings (screening, mentorship) -- strings used to
     * return null, so those meetings could not be created on Teams.
     */
    public function toZoomTimeFormat($timestamp, $duration = null, ?string $timezone = null)
    {
        $start = TimezoneService::wallClock($timestamp, $timezone ?? TimezoneService::platform());

        if (is_null($start)) {
            return null;
        }

        $start = $start->copy()->utc();

        if ($duration) {
         return $start->copy()->addMinutes($duration)->toIso8601String();
        }

        return $start->toIso8601String();
    }
}
