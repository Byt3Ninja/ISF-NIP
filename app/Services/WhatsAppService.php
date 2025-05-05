<?php

namespace App\Services;

use App\Settings;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
//     protected $client;
//     protected $instanceId;
//     protected $accessToken;

    public function __construct()
    {
        // $settings = Settings::where('id', 1)->first();
        // $this->client = new Client([
        //     'base_uri' => $settings->whatsApp_URL_callback ?? 'https://app.wawp.net/api/',
        // ]);

        // $this->instanceId = $settings->whatsApp_INSTANCE_ID ?? '';
        // $this->accessToken = $settings->whatsApp_ACCESS_TOKEN ?? '';
    }

    public function sendOTP($phoneNumber, $otp)
{
    try {
        $sessionId = 'ehabwhats';
        $chatId = $this->getChatId($phoneNumber);
        
        if (!$chatId) {
            Log::error("Failed to get chatId for number: {$phoneNumber}");
            return ['success' => false, 'message' => 'Failed to get chatId'];
        }

        $sendResponse = Http::withHeaders([
            'x-api-key' => env('WAPI_KEY')
        ])->post("https://wapi.growthlabs.app/client/sendMessage/{$sessionId}", [
            'chatId' => $chatId,
            'contentType' => 'string',
            'content' => "Your OTP is: {$otp}"
        ]);

        if ($sendResponse->successful()) {
            return ['success' => true];
        } else {
            Log::error("Failed to send message to {$phoneNumber}: " . $sendResponse->body());
            return ['success' => false, 'message' => 'Failed to send OTP'];
        }
    } catch (\Exception $e) {
        Log::error('Failed to send OTP via WhatsApp: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

public function sendMessage($phoneNumber, $message)
{
    try {
        $sessionId = 'ehabwhats';
        $chatId = $this->getChatId($phoneNumber);
        
        if (!$chatId) {
            Log::error("Failed to get chatId for number: {$phoneNumber}");
            return ['success' => false, 'message' => 'Failed to get chatId'];
        }

        $sendResponse = Http::withHeaders([
            'x-api-key' => env('WAPI_KEY')
        ])->post("https://wapi.growthlabs.app/client/sendMessage/{$sessionId}", [
            'chatId' => $chatId,
            'contentType' => 'string',
            'content' => $message
        ]);

        if ($sendResponse->successful()) {
            return ['success' => true];
        } else {
            Log::error("Failed to send message to {$phoneNumber}: " . $sendResponse->body());
            return ['success' => false, 'message' => 'Failed to send message'];
        }
    } catch (\Exception $e) {
        Log::error('Failed to send message via WhatsApp: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

private function getChatId($phoneNumber)
{
    $sessionId = 'ehabwhats';
    $apiKey = env('WAPI_KEY');
    
    $response = Http::withHeaders([
        'x-api-key' => $apiKey
    ])->post("https://wapi.growthlabs.app/client/getNumberId/{$sessionId}", [
        'number' => $phoneNumber
    ]);
   
    return $response->json('result._serialized');
}




}
