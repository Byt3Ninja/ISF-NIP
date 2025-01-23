<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $client;
    protected $instanceId;
    protected $accessToken;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://app.wawp.net/api/',
        ]);

        $this->instanceId = env('WAWP_INSTANCE_ID');
        $this->accessToken = env('WAWP_ACCESS_TOKEN');
    }

    public function sendOTP($phoneNumber, $otp)
    {
        try {
            $response = $this->client->post("send", [
                'json' => [
                    'number' => $phoneNumber,
                    'type' => 'text',
                    'message' => "Your OTP is: {$otp}",
                    'instance_id' => $this->instanceId,
                    'access_token' => $this->accessToken,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Failed to send OTP via WhatsApp: ' . $e->getMessage());
            return false;
        }
    }
}
