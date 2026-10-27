<?php

namespace App\Services;

use App\User; // تأكد من أن هذا هو مسار موديل المستخدم الصحيح
use App\Settings;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
// Illuminate\Support\Facades\Http; // لم نعد بحاجة إليه إذا استخدمنا Guzzle فقط

class ZoomService implements OnlineMeetingServiceInterface
{
    const MEETING_TYPE_INSTANT = 1;
    const MEETING_TYPE_SCHEDULE = 2;
    const MEETING_TYPE_RECURRING = 3;
    const MEETING_TYPE_FIXED_RECURRING_FIXED = 8;

    /**
     * Hard ceilings on every Zoom call. Without these Guzzle waits forever, so a
     * slow or unresponsive Zoom endpoint stalled the whole web request.
     */
    const CONNECT_TIMEOUT = 5;
    const REQUEST_TIMEOUT = 15;

    /** How long a host's licensed/basic status stays cached. */
    const LICENSE_CACHE_TTL = 21600; // 6 hours

    public Client $client;
    protected ?array $jwt = null; // Nullable and type hinted
    protected ?array $headers = null; // Nullable and type hinted
    protected ?string $zoomApiUrl = null; // For caching the API URL

    public function __construct()
    {
        $this->client = new Client([
            'timeout'         => self::REQUEST_TIMEOUT,
            'connect_timeout' => self::CONNECT_TIMEOUT,
        ]);
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

        // The account-credentials token is valid for ~1 hour and is identical for
        // every request made with the same credential set. Minting a fresh one on
        // each booking added a full round-trip to every meeting creation, so cache
        // it against a hash of the credentials (never the credentials themselves)
        // and expire it slightly early to stay clear of the boundary.
        $tokenCacheKey = 'zoom:token:' . sha1($ZOOM_CLIENT_ID . '|' . $ZOOM_ACCOUNT_ID);
        $cachedToken = Cache::get($tokenCacheKey);
        if (is_string($cachedToken) && $cachedToken !== '') {
            return ['status' => true, 'data' => $cachedToken];
        }

        try {
            $client = new Client([ // Guzzle client for token generation
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($ZOOM_CLIENT_ID . ':' . $ZOOM_CLIENT_SECRET),
                    'Host' => 'zoom.us', // هذا الهيدر قد لا يكون ضرورياً مع Guzzle الأحدث
                ],
                'timeout'         => self::REQUEST_TIMEOUT,
                'connect_timeout' => self::CONNECT_TIMEOUT,
            ]);

            $response = $client->request('POST', "https://zoom.us/oauth/token", [
                'form_params' => [
                    'grant_type' => 'account_credentials',
                    'account_id' => $ZOOM_ACCOUNT_ID,
                ],
            ]);

            $responseBody = json_decode($response->getBody()->getContents(), true);

            if (isset($responseBody['access_token'])) {
                // Zoom reports expires_in in seconds (typically 3600). Renew a
                // minute early so an in-flight request never uses a dead token.
                $ttl = max(60, ((int) ($responseBody['expires_in'] ?? 3600)) - 60);
                Cache::put($tokenCacheKey, $responseBody['access_token'], $ttl);

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

    /**
     * Zoom reads `start_time` as local time in the payload's `timezone`.
     *
     * GV-9566: accepts platform wall-clock strings, ISO strings with an offset
     * and unix timestamps (training sessions). Timestamps used to reach
     * `new \DateTime("1727000000")`, which throws, so training meetings were
     * created without a start time.
     */
    public function toZoomTimeFormat($dateTime, ?string $timezone = null): string
    {
        $timezone = $timezone ?? TimezoneService::platform();
        $moment = TimezoneService::wallClock($dateTime, $timezone);

        if (is_null($moment)) {
            Log::error('ZoomService->toZoomTimeFormat: unparseable start time.', ['start_time' => $dateTime]);
            return '';
        }

        return $moment->copy()->setTimezone($timezone)->format('Y-m-d\TH:i:s');
    }

    /** The zone a meeting's start time is expressed in (GV-9566). */
    private function meetingTimezone(array $data, $user): string
    {
        if (!empty($data['timezone']) && TimezoneService::isValid($data['timezone'])) {
            return $data['timezone'];
        }

        return TimezoneService::platform(optional(optional($user)->organization)->id);
    }

    protected function getAvailableZoomUserEmail($startTime, int $duration, ?string $timezone = null): ?string
    {
        // A real instant: Zoom lists existing meetings in UTC.
        $startTimestamp = optional(TimezoneService::wallClock($startTime, $timezone ?? TimezoneService::platform()))->timestamp;
        if (empty($startTimestamp)) {
            Log::warning('ZoomService: Unparseable start time for host selection.', ['start_time' => $startTime]);
            return null;
        }
        $endTimestamp = $startTimestamp + ($duration * 60);

        $licensedHosts = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('ZOOM_LICENSED_HOSTS'))
        )));

        if (empty($licensedHosts)) {
            Log::error('ZoomService: No licensed hosts configured.');
            return null;
        }

        // Only meetings on the booking's own day can overlap it. Previously this
        // pulled every scheduled meeting each host had ever created and paged
        // through all of them, which cost several round-trips per host and grew
        // without bound. Zoom's from/to filters narrow that to a single small
        // page. The window is padded by a day on each side so meetings recorded
        // in another timezone still surface.
        $window = [
            'from' => gmdate('Y-m-d', $startTimestamp - 86400),
            'to'   => gmdate('Y-m-d', $endTimestamp + 86400),
        ];

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
                        'type'            => 'scheduled',
                        'page_size'       => 100,
                        'from'            => $window['from'],
                        'to'              => $window['to'],
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
                        if (empty($meeting['start_time'])) {
                            continue; // Recurring meetings with no fixed time cannot clash.
                        }

                        $existingStart = strtotime($meeting['start_time']);
                        if ($existingStart === false) {
                            continue;
                        }
                        $existingEnd = $existingStart + (((int) ($meeting['duration'] ?? 0)) * 60);

                        if ($startTimestamp < $existingEnd && $endTimestamp > $existingStart) {
                            continue 3; // Conflict found, try next host.
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
        $user = Auth::user();
        $orgId = optional(optional($user)->organization)->id;

        // GV-9566: read the start in the meeting's own zone (the caller's step or
        // organization), not the creating user's organization zone.
        $startsAt = TimezoneService::wallClock($data['start_time'] ?? null, $this->meetingTimezone($data, $user));

        if (Settings::hasReachedMaxConcurrentSessions($startsAt, $data['duration'] ?? null, $orgId)) {
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

        // A missing duration used to reach the int-typed host lookup as null and 500 the page.
        if ((int) ($data['duration'] ?? 0) <= 0) {
            Log::error('ZoomService::createMeeting - Missing meeting duration.', ['duration' => $data['duration'] ?? null]);
            return ['success' => false, 'data' => 'Missing meeting duration'];
        }

        $timeZone = $this->meetingTimezone($data, $user);

        $hostEmail = $this->getAvailableZoomUserEmail($data['start_time'], (int) $data['duration'], $timeZone);
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
            'start_time' => $this->toZoomTimeFormat($data['start_time'], $timeZone),
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

        // A host's plan type effectively never changes, but this was re-checked
        // over the network for every host on every booking. Cache the answer so
        // host selection costs no round-trips in the common case. Only a
        // definitive answer is cached — transient failures must not pin a host
        // to "unlicensed" for hours.
        $cacheKey = 'zoom:licensed:' . sha1(strtolower(trim((string) $email)));
        $cached = Cache::get($cacheKey);
        if (is_bool($cached)) {
            return $cached;
        }

        try {
            $response = $this->client->get("https://api.zoom.us/v2/users/" . urlencode($email), [
                'headers' => $this->headers,
            ]);
            $userData = json_decode($response->getBody(), true);
            $isLicensed = isset($userData['type']) && (int) $userData['type'] === 2;

            Cache::put($cacheKey, $isLicensed, self::LICENSE_CACHE_TTL);

            return $isLicensed;
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

        // GV-9566: the same zone createMeeting() uses; the two used to disagree.
        $timeZone = $this->meetingTimezone($data, $user);

        $path = 'meetings/' . $id;
        $url = $this->retrieveZoomUrl();

        $payload = [
            'topic'      => $data['topic'],
            'type'       => self::MEETING_TYPE_SCHEDULE, // Or make it dynamic if needed
            'start_time' => $this->toZoomTimeFormat($data['start_time'], $timeZone),
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