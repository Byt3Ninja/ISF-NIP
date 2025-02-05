<?php

namespace App\Services;

use App\Settings;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $client;
    protected $instanceId;
    protected $accessToken;

    public function __construct()
    {
        $settings = Settings::where('id', 1)->first();
        $this->client = new Client([
            'base_uri' => $settings->whatsApp_URL_callback ?? 'https://app.wawp.net/api/',
        ]);

        $this->instanceId = $settings->whatsApp_INSTANCE_ID ?? '';
        $this->accessToken = $settings->whatsApp_ACCESS_TOKEN ?? '';
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


    public function sendMessage($phoneNumber, $Massage)
    {
        try {
            $response = $this->client->post("send", [
                'json' => [
                    'number' => $phoneNumber,
                    'type' => 'text',
                    'message' => $Massage,
                    'instance_id' => $this->instanceId,
                    'access_token' => $this->accessToken,
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            Log::error('Failed to send Massage via WhatsApp: ' . $e->getMessage());
            return false;
        }
    }






}
