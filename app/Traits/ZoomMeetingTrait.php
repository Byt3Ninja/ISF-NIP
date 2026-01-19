<?php
namespace App\Traits;

use GuzzleHttp\Client;
use Log;
use App\Settings;


/**
 * trait ZoomMeetingTrait
 */
trait ZoomMeetingTrait
{
    public $client;

    public $jwt;
    public $headers;

    public function __construct()
    {
        $this->client = new Client();
        $this->jwt = $this->generateZoomToken();
        $this->headers = [
            'Authorization' => 'Bearer '.$this->jwt,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

//    ===================> old function <=====================
//    public function  getAccessToken()
//    {
//        $key = env('ZOOM_API_KEY', '');
//        $secret = env('ZOOM_API_SECRET', '');
//        $payload = [
//            'iss' => $key,
//            'exp' => strtotime('+1 minute'),
//        ];
//
//        return \Firebase\JWT\JWT::encode($payload, $secret, 'HS256');
//    }
    protected function generateZoomToken()
    {
        $Setting = Settings::where('id', 1)->first();
        if($Setting){
        $ZOOM_CLIENT_ID = $Setting->ZOOM_CLIENT_ID;
        $ZOOM_CLIENT_SECRET= $Setting->ZOOM_CLIENT_SECRET;
        $ZOOM_ACCOUNT_ID= $Setting->ZOOM_ACCOUNT_ID;
        }else{
            $ZOOM_CLIENT_ID = '';
            $ZOOM_CLIENT_SECRET= '';
            $ZOOM_ACCOUNT_ID= '';
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

    private function retrieveZoomUrl()
    {
        $Setting = Settings::where('id', 1)->first();
        if(!$Setting){
        $ZOOM_API_URL = $Setting->ZOOM_API_URL;
        }else{
            $ZOOM_API_URL = 'https://api.zoom.us/v2/';
        }


        return $ZOOM_API_URL;
    }

    public function toZoomTimeFormat(string $dateTime)
    {
        try {
            $date = new \DateTime($dateTime);
            return $date->format('Y-m-d\TH:i:s');
        } catch (\Exception $e) {
            Log::error('ZoomJWT->toZoomTimeFormat : '.$e->getMessage());
            return '';
        }
    }

    public function create($data)
    {
        $orgId = optional(optional(auth()->user())->organization)->id;
        if (Settings::hasReachedMaxConcurrentSessions($data['start_time'] ?? null, $data['duration'] ?? null, $orgId)) {
            return [
                'success' => false,
                'data'    => ['message' => __('settings.max_concurrent_sessions_reached')],
            ];
        }

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
                ],
            ]),
        ];

        $response =  $this->client->post($url.$path, $body);

        return [
            'success' => $response->getStatusCode() === 201,
            'data'    => json_decode($response->getBody(), true),
        ];
    }

    public function update($id, $data)
    {
        $path = 'meetings/'.$id;
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
        $response =  $this->client->patch($url.$path, $body);

        return [
            'success' => $response->getStatusCode() === 204,
            'data'    => json_decode($response->getBody(), true),
        ];
    }

    public function get($id)
    {
        $path = 'meetings/'.$id;
        $url = $this->retrieveZoomUrl();
        $this->jwt = $this->generateZoomToken();
        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([]),
        ];
        $response =  $this->client->get($url.$path, $body);
        return [
            'success' => $response->getStatusCode() === 204,
            'data'    => json_decode($response->getBody(), true),
        ];
    }

    public function getregistrants($id)
    {
        $path = 'meetings/'.$id.'/registrants';
        $url = $this->retrieveZoomUrl();
        $this->jwt = $this->generateZoomToken();
        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([]),
        ];
        $response =  $this->client->get($url.$path, $body);
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
        $response =  $this->client->get($url.$path, $body);
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
        $path = 'meetings/'.$id;
        $url = $this->retrieveZoomUrl();
        $body = [
            'headers' => $this->headers,
            'body'    => json_encode([]),
        ];
        $response =  $this->client->delete($url.$path, $body);
        return [
            'success' => $response->getStatusCode() === 204,
        ];
    }
}