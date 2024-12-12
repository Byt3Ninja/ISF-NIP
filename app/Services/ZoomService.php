<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ZoomService implements OnlineMeetingServiceInterface
{
    const MEETING_TYPE_INSTANT = 1;
    const MEETING_TYPE_SCHEDULE = 2;
    const MEETING_TYPE_RECURRING = 3;
    const MEETING_TYPE_FIXED_RECURRING_FIXED = 8;

    public function generateZoomAccessToken()
    {
        $apiKey = env('ZOOM_CLIENT_ID');
        $apiSecret = env('ZOOM_CLIENT_SECRET');
        $accountId = env('ZOOM_ACCOUNT_ID');

        $base64Credentials = base64_encode("$apiKey:$apiSecret");

        $url = "https://zoom.us/oauth/token?grant_type=account_credentials&account_id=$accountId";

        $response = Http::withHeaders([
            'Authorization' => "Basic $base64Credentials",
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ])->post($url);

        $responseData = $response->json();

        if ($response->successful()) {
            return $responseData['access_token'];
        } else {
            Log::error('Zoom OAuth Token Response: ' . json_encode($responseData));
            return null;
        }
    }


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

    public function createMeeting($data)
    {
        $accessToken = $this->generateZoomAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to generate access token.'];
        }
        $url = 'https://api.zoom.us/v2/users/me/meetings';

        $meeting = [
            "topic"      => $data['topic'],
            "agenda"     => $data['agenda'] ?? null,
            "type"       => self::MEETING_TYPE_SCHEDULE,
            "start_time" => $this->toZoomTimeFormat($data['start_time']),
            "duration"   => $data['duration'],
            "timezone"   => 'Africa/Cairo',
            'settings' => [
                'join_before_host' => false,
                'host_video' => false,
                'participant_video' => false,
                'mute_upon_entry' => false,
                'waiting_room' => false,
                'audio' => 'both',
                'auto_recording' => 'none',
                'approval_type' => 0,
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'Content-Type'  => 'application/json',
        ])->post($url, $meeting);

        $responseData = $response->json();

        if ($response->successful()) {
            return [
                'success' => true,
                'data' => $responseData,
            ];
        } else {
            Log::error('Zoom Create Meeting Response: ' . json_encode($responseData));
            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'Failed to create Zoom meeting.',
            ];
        }
    }

    public function getSessionReport(string $meetingId)
    {
        $accessToken = $this->generateZoomAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to Generate Access Token.'];
        }

        $url = "https://api.zoom.us/v2/report/meetings/{$meetingId}";

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
            Log::error('Zoom Meeting Details Report Response: ' . json_encode($responseData));
            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'Failed to fetch meeting details report.',
            ];
        }
    }

    public function getRecordedSessions($meetingId)
    {
        $accessToken = $this->generateZoomAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to generate access token.'];
        }

        $url = "https://api.zoom.us/v2/meetings/{$meetingId}/recordings";

        // Make the API Request to Zoom
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
                'status_code' => $response->status(),
            ];
        }
    }
    /**
     * Fetch all Zoom Meetings.
     */
    public function getAllMeetings()
    {
        $accessToken = $this->generateZoomAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to generate access token.'];
        }

        $url = 'https://api.zoom.us/v2/users/me/meetings';

        $response = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'Content-Type'  => 'application/json',
        ])->get($url);

        $responseData = $response->json();

        if ($response->successful()) {
            return [
                'success' => true,
                'data'    => $responseData['meetings'],
            ];
        } else {
            Log::error('Zoom Get All Meetings Response: ' . json_encode($responseData));
            return [
                'success' => false,
                'message' => 'Failed to fetch meetings.',
            ];
        }
    }

    /**
     * Delete a Zoom Meeting.
     */
    public function deleteMeeting($id)
    {
        $accessToken = $this->generateZoomAccessToken();
        if (!$accessToken) {
            return ['success' => false, 'message' => 'Failed to Generate Access Token.'];
        }
        $url = "https://api.zoom.us/v2/users/me/meetings/{$id}";

        $response = Http::withHeaders([
            'Authorization' => "Bearer $accessToken",
            'Content-Type'  => 'application/json',
        ])->delete($url);

        if ($response->successful()) {
            return ['success' => true];
        } else {
            $responseData = $response->json();
            Log::error('Zoom Delete Meeting Response: ' . json_encode($responseData));
            return ['success' => false, 'message' => 'Failed to delete Zoom meeting.'];
        }
    }
}
