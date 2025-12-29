<?php

namespace App\Services;

use App\User; // تأكد من أن هذا هو مسار موديل المستخدم الصحيح
use App\Settings;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
// Illuminate\Support\Facades\Http; // لم نعد بحاجة إليه إذا استخدمنا Guzzle فقط

class ZoomService implements OnlineMeetingServiceInterface
{
    const MEETING_TYPE_INSTANT = 1;
    const MEETING_TYPE_SCHEDULE = 2;
    const MEETING_TYPE_RECURRING = 3;
    const MEETING_TYPE_FIXED_RECURRING_FIXED = 8;

    public Client $client;
    protected ?array $jwt = null; // Nullable and type hinted
    protected ?array $headers = null; // Nullable and type hinted
    protected ?string $zoomApiUrl = null; // For caching the API URL

    public function __construct()
    {
        $this->client = new Client();
        // لا تقم بإنشاء التوكن هنا لتجنب مشكلة Auth::user() == null
    }

    /**
     * Ensures that Zoom credentials (JWT and headers) are initialized.
     * This method should be called before any API request.
     *
     * @param User|null $user The user context for fetching credentials. Defaults to Auth::user().
     * @return bool True if initialization was successful, false otherwise.
     */
    protected function ensureCredentialsInitialized(?User $user = null): bool
    {
        if ($this->jwt !== null && $this->jwt['status']) {
            return true; // Already initialized and valid
        }

        $currentUser = $user ?? Auth::user();

        if (!$currentUser) {
            Log::warning('ZoomService: Attempted to initialize credentials without an authenticated user.');
            $this->jwt = ['status' => false, 'data' => __('admin.zoom_error_no_user_auth')]; // رسالة خطأ أوضح
            return false;
        }

        $this->jwt = $this->generateZoomToken($currentUser);

        if ($this->jwt['status']) {
            $this->headers = [
                'Authorization' => 'Bearer ' . $this->jwt['data'],
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ];
            return true;
        } else {
            Log::error('ZoomService: Failed to generate Zoom token.', ['error' => $this->jwt['data']]);
            $this->headers = null; // Ensure headers are null on failure
            return false;
        }
    }

    /**
     * Generate Zoom Access Token.
     * Now accepts a User object.
     */
    protected function generateZoomToken(User $user): array // User parameter is now required and type-hinted
    {
        $ZOOM_CLIENT_ID = '';
        $ZOOM_CLIENT_SECRET = '';
        $ZOOM_ACCOUNT_ID = '';

         
        try {
            $org = $user->organization; 
            if (!empty($org)) {
                $ZOOM_CLIENT_ID = $org->zoom_client_id;
                $ZOOM_CLIENT_SECRET = $org->zoom_client_secret;
                $ZOOM_ACCOUNT_ID = $org->zoom_account_id;
            }else{
                $setting = Settings::where('id', 1)->first();
                if ($setting) {
                    $ZOOM_CLIENT_ID = $setting->ZOOM_CLIENT_ID;
                    $ZOOM_CLIENT_SECRET = $setting->ZOOM_CLIENT_SECRET;
                    $ZOOM_ACCOUNT_ID = $setting->ZOOM_ACCOUNT_ID;
                }
             }
        } catch (\Exception $e) {
            Log::error('ZoomService: Error retrieving Zoom credentials for user.', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
            

        if (empty($ZOOM_CLIENT_ID) || empty($ZOOM_CLIENT_SECRET) || empty($ZOOM_ACCOUNT_ID)) {
            Log::error('ZoomService: Zoom API credentials not configured for user.', ['user_id' => $user->id]);
            return [
            'status' => false,
            'data' =>  __('admin.zoom_credentials_not_configured'),   
            ];
    }

        try {
            $client = new Client([ // Guzzle client for token generation
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($ZOOM_CLIENT_ID . ':' . $ZOOM_CLIENT_SECRET),
                    'Host' => 'zoom.us', // هذا الهيدر قد لا يكون ضرورياً مع Guzzle الأحدث
                ],
            ]);

            $response = $client->request('POST', "https://zoom.us/oauth/token", [
                'form_params' => [
                    'grant_type' => 'account_credentials',
                    'account_id' => $ZOOM_ACCOUNT_ID,
                ],
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);

            if (isset($responseBody['access_token'])) {
                return ['status' => true, 'data' => $responseBody['access_token']];
            }
            Log::error('ZoomService: Access token not found in Zoom OAuth response.', ['response_body' => $responseBody]);
            return ['status' => false, 'data' => __('admin.zoom_token_error_retrieval')];

        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            if ($e->hasResponse()) {
                $errorMessage .= ' | Response: ' . $e->getResponse()->getBody()->getContents();
            }
            Log::error('ZoomService: Exception during Zoom token generation.', ['error' => $errorMessage]);
            return ['status' => false, 'data' => $errorMessage];
        } catch (\Exception $e) {
            Log::error('ZoomService: General exception during Zoom token generation.', ['error' => $e->getMessage()]);
            return ['status' => false, 'data' => $e->getMessage()];
        }
    }


    private function retrieveZoomUrl(): string
    {
        if ($this->zoomApiUrl !== null) {
            return $this->zoomApiUrl;
        }

        $defaultUrl = 'https://api.zoom.us/v2/';
        try{
            $setting = Settings::where('id', 1)->first();
            if ($setting && !empty($setting->ZOOM_API_URL)) {
                $this->zoomApiUrl = rtrim($setting->ZOOM_API_URL, '/') . '/'; // Ensure trailing slash
                return $this->zoomApiUrl;
            }
        }catch (\Exception $e) {
            Log::error('ZoomService->retrieveZoomUrl: ' . $e->getMessage());
        }
        $this->zoomApiUrl = $defaultUrl;
        return $this->zoomApiUrl;
    }

    public function toZoomTimeFormat(string $dateTime): string
    {
        try {
            $date = new \DateTime($dateTime);
            return $date->format('Y-m-d\TH:i:s');
        } catch (\Exception $e) {
            Log::error('ZoomService->toZoomTimeFormat: ' . $e->getMessage());
            return ''; // أو throw exception
        }
    }
    protected function getAvailableZoomUserEmail(string $startTime, int $duration): ?string
    {
        $startTimestamp = strtotime($startTime);
        $endTimestamp = $startTimestamp + ($duration * 60);

        $licensedHosts = explode(',', env('ZOOM_LICENSED_HOSTS')); 

        if (empty($licensedHosts)) {
            Log::error('ZoomService: No licensed hosts configured.');
            return null;
        }

        foreach ($licensedHosts as $email) {
            if (!$this->isLicensedZoomUser($email)) {
                Log::warning("ZoomService: User $email is not a licensed Zoom user.");
                continue;
            }

            $url = "https://api.zoom.us/v2/users/" . urlencode($email) . "/meetings";
            try {
                $nextPageToken = null;
                do {
                    $query = [
                        'type' => 'scheduled',
                        'page_size' => 100,
                        'next_page_token' => $nextPageToken,
                    ];
                    $response = $this->client->get($url, [
                        'headers' => $this->headers,
                        'query' => $query,
                    ]);

                    $responseData = json_decode($response->getBody(), true);
                    $meetings = $responseData['meetings'] ?? [];
                    $nextPageToken = $responseData['next_page_token'] ?? null;

                    foreach ($meetings as $meeting) {
                        $existingStart = strtotime($meeting['start_time']);
                        $existingEnd = $existingStart + ($meeting['duration'] * 60);

                        if ($startTimestamp < $existingEnd && $endTimestamp > $existingStart) {
                            continue 2; // Conflict found, try next user
                        }
                    }
                } while ($nextPageToken);

                return $email; // No conflicts found
            } catch (RequestException $e) {
                Log::error("ZoomService: Failed to check meetings for $email.", ['error' => $e->getMessage()]);
                continue;
            }
        }

        Log::warning('ZoomService: No available licensed Zoom user found.');
        return null;
    }

    public function createMeeting($data): array
    {
        if (Settings::hasReachedMaxConcurrentSessions($data['start_time'] ?? null, $data['duration'] ?? null)) {
            return [
                'success' => false,
                'data'    => __('settings.max_concurrent_sessions_reached'),
            ];
        }

        $user = Auth::user();
        if (!$this->ensureCredentialsInitialized($user)) { // تمرير المستخدم الحالي
            return [
                'success' => false,
                'data'    => $this->jwt['data'] ?? __('admin.zoom_auth_failed'),
            ];
        }

        $timeZone = 'Africa/Cairo'; // Default
        if ($user) { // $user should be available if ensureCredentialsInitialized passed
            try{
                 $org = $user->organization;
                if ($org && $org->time_zone) {
                    $timeZone = $org->time_zone;
                }else{
                 $setting = Settings::where('id', 1)->first();
                 if ($setting && $setting->timezone) {
                    $timeZone = $setting->timezone;
                }
                }  
            }catch (\Exception $e) {
                Log::error('ZoomService->createMeeting: ' . $e->getMessage());
            }
        }

        $hostEmail = $this->getAvailableZoomUserEmail($data['start_time'], $data['duration']);
        if( empty( $hostEmail ) || !trim( $hostEmail ) )
        {
            $path = 'users/me/meetings';
        }
        else
        {
            $path = "users/{$hostEmail}/meetings";
        }
        $url = $this->retrieveZoomUrl();

        $payload = [
            'topic'      => $data['topic'],
            'type'       => self::MEETING_TYPE_SCHEDULE,
            'start_time' => $this->toZoomTimeFormat($data['start_time']),
            'duration'   => $data['duration'],
            'agenda'     => $data['agenda'] ?? null,
            'timezone'   => $timeZone,
            'settings'   => [
                'host_video'        => ($data['host_video'] == "1"),
                'participant_video' => ($data['participant_video'] == "1"),
                'waiting_room'      => false, // Consider making this configurable
                'join_before_host'  => true, 
                'auto_recording'    => 'cloud',
                "share_recording"   => "publicly",
                "viewer_download"   => true
            ],
        ];

        try {
            $response = $this->client->post($url . $path, [
                'headers' => $this->headers,
                'json'    => $payload, // Use 'json' for Guzzle to auto-encode and set Content-Type
            ]);
            if ($response->getStatusCode() === 201) {
                $responseData = json_decode($response->getBody()->getContents(), true);
                return [
                    'success' => true,
                    'data'    => [
                        'id'        => $responseData['id'],
                        'start_url' => $responseData['start_url'],
                        'join_url'  => $responseData['join_url'],
                        'passcode' => array_key_exists('password', $responseData) ? $responseData['password'] : null,
                    ],
                ];

            } else {
                Log::error('ZoomService::createMeeting - Unexpected status code.', [
                    'status' => $response->getStatusCode(),
                    'body' => $response->getBody()->getContents()
                ]);
                return ['success' => false, 'data' => json_decode($response->getBody()->getContents(), true) ?? 'Unknown error'];
            }
        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            if ($e->hasResponse()) {
                $errorMessage .= ' | Response: ' . $e->getResponse()->getBody()->getContents();
            }
            Log::error('ZoomService::createMeeting - RequestException', ['error' => $errorMessage]);
            return ['success' => false, 'data' => $errorMessage];
        } catch (\Exception $e) {
            Log::error('ZoomService::createMeeting - General Exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'data' => $e->getMessage()];
        }
    }

    public function isLicensedZoomUser($email): bool
    {
        if (!$this->ensureCredentialsInitialized()) {
            Log::error('ZoomService: Cannot validate user due to failed credential initialization.');
            return false;
        }

        try {
            $response = $this->client->get("https://api.zoom.us/v2/users/" . urlencode($email), [
                'headers' => $this->headers,
            ]);
            $userData = json_decode($response->getBody(), true);
            return $userData['type'] === 2;
        } catch (\Exception $e) {
            Log::error("ZoomService: User validation failed for $email: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $data): array
    {
        $user = Auth::user();
        if (!$this->ensureCredentialsInitialized($user)) {
            return [
                'success' => false,
                'data'    => $this->jwt['data'] ?? __('admin.zoom_auth_failed'),
            ];
        }

        $timeZone = 'Africa/Cairo'; // Default, same logic as createMeeting for dynamic timezone
         if ($user) {
            try{
                if ($user->hasRole('Admin')) {

                    $setting = Settings::where('id', 1)->first();
                    if ($setting && $setting->timezone) {
                        $timeZone = $setting->timezone;
                    }
                } elseif ($user->hasRole('organizationManager')) {
                    $org = $user->organization;
                    if ($org && $org->time_zone) {
                        $timeZone = $org->time_zone;
                    }
                }
            }catch (\Exception $e) {
                Log::error('ZoomService->updateMeeting: ' . $e->getMessage());
            }
        }

        $path = 'meetings/' . $id;
        $url = $this->retrieveZoomUrl();

        $payload = [
            'topic'      => $data['topic'],
            'type'       => self::MEETING_TYPE_SCHEDULE, // Or make it dynamic if needed
            'start_time' => $this->toZoomTimeFormat($data['start_time']),
            'duration'   => $data['duration'],
            'agenda'     => $data['agenda'] ?? null,
            'timezone'   => $timeZone,
            'settings'   => [
                'host_video'        => ($data['host_video'] == "1"),
                'participant_video' => ($data['participant_video'] == "1"),
                'waiting_room'      => false,
            ],
        ];

        try {
            $response = $this->client->patch($url . $path, [
                'headers' => $this->headers,
                'json'    => $payload,
            ]);

            // Zoom API returns 204 No Content on successful PATCH
            return [
                'success' => $response->getStatusCode() === 204,
                'data'    => $response->getStatusCode() === 204 ? null : json_decode($response->getBody()->getContents(), true),
            ];
        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            if ($e->hasResponse()) {
                $errorMessage .= ' | Response: ' . $e->getResponse()->getBody()->getContents();
            }
            Log::error('ZoomService::update - RequestException', ['meeting_id' => $id, 'error' => $errorMessage]);
            return ['success' => false, 'data' => $errorMessage];
        }
    }

    // Helper for GET requests
    private function makeGetRequest(string $path, array $queryParams = []): array
    {
        if (!$this->ensureCredentialsInitialized()) {
            return [
                'success' => false,
                'data'    => $this->jwt['data'] ?? __('admin.zoom_auth_failed'),
            ];
        }
        $url = $this->retrieveZoomUrl();

        try {
            $response = $this->client->get($url . $path, [
                'headers' => $this->headers,
                'query'   => $queryParams // For GET request query parameters
            ]);
            // GET requests usually return 200 OK with data.
            // If Zoom truly returns 204 for some GETs, the original logic might be needed for specific endpoints.
            // For now, assuming 200 OK for successful GETs with data.
            if ($response->getStatusCode() === 200) {
                 return [
                    'success' => true,
                    'data'    => json_decode($response->getBody()->getContents(), true),
                ];
            } elseif ($response->getStatusCode() === 204) { // Handle specific 204 if Zoom API behaves this way for GET
                 return [
                    'success' => true, // Technically success, but no content
                    'data'    => null,
                ];
            } else {
                Log::error('ZoomService::makeGetRequest - Unexpected status code.', [
                    'path' => $path,
                    'status' => $response->getStatusCode(),
                    'body' => $response->getBody()->getContents()
                ]);
                return ['success' => false, 'data' => json_decode($response->getBody()->getContents(), true) ?? 'Unknown error'];
            }

        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            if ($e->hasResponse()) {
                $errorMessage .= ' | Response: ' . $e->getResponse()->getBody()->getContents();
            }
            Log::error('ZoomService::makeGetRequest - RequestException', ['path' => $path, 'error' => $errorMessage]);
            return ['success' => false, 'data' => $errorMessage];
        }
    }

    public function get($id): array
    {
        return $this->makeGetRequest('meetings/' . $id);
    }

    public function getregistrants($id): array
    {
        // Zoom API might require pagination parameters like page_size, next_page_token
        return $this->makeGetRequest('meetings/' . $id . '/registrants', ['page_size' => 300]); // Example: get up to 300
    }

    public function getall(): array
    {
        // Zoom API might require pagination parameters
        return $this->makeGetRequest('users/me/meetings', ['page_size' => 100]); // Example: get up to 100
    }

    public function delete($id): array
    {
        if (!$this->ensureCredentialsInitialized()) {
            return [
                'success' => false,
                'data'    => $this->jwt['data'] ?? __('admin.zoom_auth_failed'),
            ];
        }
        $path = 'meetings/' . $id;
        $url = $this->retrieveZoomUrl();

        try {
            $response = $this->client->delete($url . $path, [
                'headers' => $this->headers,
            ]);
            // Zoom API returns 204 No Content on successful DELETE
            return [
                'success' => $response->getStatusCode() === 204,
            ];
        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
             if ($e->hasResponse()) {
                $errorMessage .= ' | Response: ' . $e->getResponse()->getBody()->getContents();
            }
            Log::error('ZoomService::delete - RequestException', ['meeting_id' => $id, 'error' => $errorMessage]);
            return ['success' => false, 'data' => $errorMessage];
        }
    }

    // For methods using specific token generation like reports/recordings,
    // it's better to use the instance's initialized token if possible.
    // If they truly need a separate token flow, that needs to be justified.
    // For consistency, let's try to use the instance's token.

    public function getSessionReport(string $meetingId, ?string $userId = null): array // userId is not used currently
    {
        // Use ensureCredentialsInitialized which now can take a user if needed
        // If $userId is important for selecting different credentials, then pass it:
        // $targetUser = $userId ? User::find($userId) : Auth::user();
        // if (!$this->ensureCredentialsInitialized($targetUser)) { ... }
        if (!$this->ensureCredentialsInitialized()) { // Uses Auth::user() by default
            return [
                'success' => false,
                'message' => $this->jwt['data'] ?? __('admin.zoom_auth_failed'), // Use message key as per original
            ];
        }

        // Zoom API might require pagination for participants
        // The path for report is different (uses /report/meetings/)
        $url = "https://api.zoom.us/v2/report/meetings/{$meetingId}/participants";
        try {
            $response = $this->client->get($url, [
                'headers' => $this->headers,
                'query'   => ['page_size' => 300] // Example pagination
            ]);

            if ($response->getStatusCode() === 200) {
                return [
                    'success' => true,
                    'data'    => json_decode($response->getBody()->getContents(), true),
                ];
            }
            return [
                'success' => false,
                'message' => json_decode($response->getBody()->getContents(), true)['message'] ?? 'Failed to fetch report',
            ];

        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                $decodedBody = json_decode($responseBody, true);
                $errorMessage = $decodedBody['message'] ?? $responseBody;
            }
            Log::error('ZoomService::getSessionReport - RequestException', ['meeting_id' => $meetingId, 'error' => $errorMessage]);
            return ['success' => false, 'message' => $errorMessage];
        }
    }

    public function getSessionRecord(string $meetingId, ?string $userId = null): array // userId is not used
    {
        if (!$this->ensureCredentialsInitialized()) {
            return [
                'success' => false,
                'message' => $this->jwt['data'] ?? __('admin.zoom_auth_failed'),
            ];
        }

        $url = "https://api.zoom.us/v2/meetings/{$meetingId}/recordings";

        try {
            $response = $this->client->get($url, [
                'headers' => $this->headers,
            ]);

            if ($response->getStatusCode() === 200) {
                return [
                    'success' => true,
                    'data'    => json_decode($response->getBody()->getContents(), true),
                ];
            }
            return [
                'success' => false,
                'message' => json_decode($response->getBody()->getContents(), true)['message'] ?? 'Failed to fetch recordings',
            ];
        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            if ($e->hasResponse()) {
                $responseBody = $e->getResponse()->getBody()->getContents();
                $decodedBody = json_decode($responseBody, true);
                $errorMessage = $decodedBody['message'] ?? $responseBody;
            }
             Log::error('ZoomService::getSessionRecord - RequestException', ['meeting_id' => $meetingId, 'error' => $errorMessage]);
            return ['success' => false, 'message' => $errorMessage];
        }
    }

    public function getUserIdFromMeeting(string $meetingId): array
    {
        // This essentially gets meeting details
        $result = $this->get($meetingId); // Reuse the existing get method

        if ($result['success'] && isset($result['data']['host_id'])) {
            return [
                'success' => true,
                'user_id' => $result['data']['host_id'], // Zoom user ID, not your app's user ID
                'data'    => $result['data'],
            ];
        } else {
            return [
                'success' => false,
                'message' => $result['data']['message'] ?? $result['data'] ?? 'Failed to fetch meeting details or host_id.',
            ];
        }
    }
}