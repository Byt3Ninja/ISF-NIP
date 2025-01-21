<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Settings;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Schema;

class ZoomService implements OnlineMeetingServiceInterface
{
    const MEETING_TYPE_INSTANT = 1;
    const MEETING_TYPE_SCHEDULE = 2;
    const MEETING_TYPE_RECURRING = 3;
    const MEETING_TYPE_FIXED_RECURRING_FIXED = 8;

    public $client;
    public $jwt;
    public $headers;

    public function __construct()
    {
        $this->client = new Client();
        $this->jwt = $this->generateZoomToken();
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->jwt,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }


    /**
     * Generate Zoom Access Token.
     */
    protected function generateZoomToken()
    {
        if (Schema::hasTable('settings')) {
            $Setting = Settings::where('id', 1)->first();

            if ($Setting) {
                $ZOOM_CLIENT_ID = $Setting->ZOOM_CLIENT_ID;
                $ZOOM_CLIENT_SECRET = $Setting->ZOOM_CLIENT_SECRET;
                $ZOOM_ACCOUNT_ID = $Setting->ZOOM_ACCOUNT_ID;
            } else {
                $ZOOM_CLIENT_ID = '';
                $ZOOM_CLIENT_SECRET = '';
                $ZOOM_ACCOUNT_ID = '';
            }
            $client = new Client([
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($ZOOM_CLIENT_ID . ':' . $ZOOM_CLIENT_SECRET),
                    'Host' => 'zoom.us',
                ],
            ]);

            $response = $client->request('POST', "https://zoom.us/oauth/token", [
                'form_params' => [
                    'grant_type' => 'account_credentials',
                    'account_id' => $ZOOM_ACCOUNT_ID,
                ],
            ]);

            $responseBody = json_decode($response->getBody(), true);
            return $responseBody['access_token'];
        }
    }

    private function retrieveZoomUrl()
    {
        if (Schema::hasTable('settings')) {
            $Setting = Settings::where('id', 1)->first();
            $zoom_api_url = $Setting->ZOOM_API_URL;
            if (!empty($Setting) && !empty($zoom_api_url)) {
                $ZOOM_API_URL = $Setting->ZOOM_API_URL;
            } else {
                $ZOOM_API_URL = 'https://api.zoom.us/v2/';
            }

            return $ZOOM_API_URL;
        }
    }

    /**
     * Zoom Time Format.
     */
    public function toZoomTimeFormat(string $dateTime)
    {
        try {
            $date = new \DateTime($dateTime);
            return $date->format('Y-m-d\TH:i:s');
        } catch (\Exception $e) {
            Log::error('ZoomService->toZoomTimeFormat: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Create Zoom Meeting.
     */
    public function createMeeting($data)
    {
        $path = 'users/me/meetings';
        $url = $this->retrieveZoomUrl();

        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([
                'topic'      => $data['topic'],
                'type'       => self::MEETING_TYPE_SCHEDULE,
                'start_time' => $this->toZoomTimeFormat($data['start_time']),
                'duration'   => $data['duration'],
                'agenda'     => (! empty($data['agenda'])) ? $data['agenda'] : null,
                'timezone'     => 'Africa/Cairo',
                'settings'   => [
                    'host_video'        => ($data['host_video'] == "1") ? true : false,
                    'participant_video' => ($data['participant_video'] == "1") ? true : false,
                    'waiting_room'      => false,
                    'auto_recording' => 'cloud',
                    "share_recording" => "publicly",
                    "viewer_download" => true
                ],
            ]),
        ];

        $response =  $this->client->post($url . $path, $body);

        if ($response && $response->getStatusCode() === 201) {
            $responseData = json_decode($response->getBody(), true);
            return [
                'success' => true,
                'data'    => [
                    'id'        => $responseData['id'],
                    'start_url' => $responseData['start_url'],
                    'join_url'  => $responseData['join_url'],
                ],
            ];
        } else {
            return [
                'success' => false,
                'data'    => null,
            ];
        }
    }
    public function update($id, $data)
    {
        $path = 'meetings/' . $id;
        $url = $this->retrieveZoomUrl();

        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([
                'topic'      => $data['topic'],
                'type'       => self::MEETING_TYPE_SCHEDULE,
                'start_time' => $this->toZoomTimeFormat($data['start_time']),
                'duration'   => $data['duration'],
                'agenda'     => (! empty($data['agenda'])) ? $data['agenda'] : null,
                'timezone'     => 'Africa/Cairo',
                'settings'   => [
                    'host_video'        => ($data['host_video'] == "1") ? true : false,
                    'participant_video' => ($data['participant_video'] == "1") ? true : false,
                    'waiting_room'      => false,
                ],
            ]),
        ];
        $response =  $this->client->patch($url . $path, $body);

        return [
            'success' => $response->getStatusCode() === 204,
            'data'    => json_decode($response->getBody(), true),
        ];
    }

    public function get($id)
    {
        $path = 'meetings/' . $id;
        $url = $this->retrieveZoomUrl();
        $this->jwt = $this->generateZoomToken();
        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([]),
        ];
        $response =  $this->client->get($url . $path, $body);
        return [
            'success' => $response->getStatusCode() === 204,
            'data'    => json_decode($response->getBody(), true),
        ];
    }

    public function getregistrants($id)
    {
        $path = 'meetings/' . $id . '/registrants';
        $url = $this->retrieveZoomUrl();
        $this->jwt = $this->generateZoomToken();
        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([]),
        ];
        $response =  $this->client->get($url . $path, $body);
        return [
            'success' => $response->getStatusCode() === 204,
            'data'    => json_decode($response->getBody(), true),
        ];
    }

    public function getall()
    {
        $path = '/users/me/meetings';
        $url = $this->retrieveZoomUrl();
        $this->jwt = $this->generateZoomToken();
        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([]),
        ];
        $response =  $this->client->get($url . $path, $body);
        return [
            'success' => $response->getStatusCode() === 204,
            'data'    => json_decode($response->getBody(), true),
        ];
    }

    /**
     * @param string $id
     *
     * @return bool[]
     */
    public function delete($id)
    {
        $path = 'meetings/' . $id;
        $url = $this->retrieveZoomUrl();
        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([]),
        ];
        $response =  $this->client->delete($url . $path, $body);
        return [
            'success' => $response->getStatusCode() === 204,
        ];
    }


    /**
     * Get Zoom Meeting Report.
     */

    public function getSessionReport(string $meetingId, ?string $userId = null)
    {
        $accessToken = $this->generateZoomToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to Generate Access Token.'];
        }

        $url = "https://api.zoom.us/v2/report/meetings/{$meetingId}/participants";

        $response = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'Content-Type'  => 'application/json',
        ])->get($url);

        $responseData = $response->json();

        if ($response->successful()) {
            return [
                'success' => true,
                'data'    => $responseData,
            ];
        } else {
            return [
                'success' => false,
                'message' => $responseData['message'],
            ];
        }
    }


    /**
     * Get Zoom Meeting Recording.
     */

    public function getSessionRecord($meetingId, ?string $userId = null)
    {
        $accessToken = $this->generateZoomToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to generate access token.'];
        }

        $url = "https://api.zoom.us/v2/meetings/{$meetingId}/recordings";

        $response = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'Content-Type'  => 'application/json',
        ])->get($url);

        if ($response->successful()) {
            return [
                'success' => true,
                'data'    => $response->json(),
            ];
        } else {
            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Failed to Fetch Recorded Sessions',
            ];
        }
    }

    public function getUserIdFromMeeting(string $meetingId)
    {
        $accessToken = $this->generateZoomToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to Generate Access Token.'];
        }

        $url = "https://api.zoom.us/v2/meetings/{$meetingId}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'Content-Type'  => 'application/json',
        ])->get($url);

        $responseData = $response->json();

        if ($response->successful()) {
            return [
                'success' => true,
                'user_id' => $responseData['host_id'],
                'data'    => $responseData,
            ];
        } else {
            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'Failed to fetch meeting details.',
            ];
        }
    }
}
