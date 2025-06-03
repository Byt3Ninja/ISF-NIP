<?php

namespace App\Services;

use App\Settings;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Client\RequestException;
class WhatsAppService
{
//     protected $client;
//     protected $instanceId;
//     protected $accessToken;
//  protected string $baseUrl;
//     protected ?string $apiKey; // Nullable in case it's missing

    public function __construct()
    {
        // $settings = Settings::where('id', 1)->first();
        // $this->client = new Client([
        //     'base_uri' => $settings->whatsApp_URL_callback ?? 'https://app.wawp.net/api/',
        // ]);

        // $this->instanceId = $settings->whatsApp_INSTANCE_ID ?? '';
        // $this->accessToken = $settings->whatsApp_ACCESS_TOKEN ?? '';
         // Load config values in the constructor
        // $this->baseUrl = rtrim(config('whatsapp.base_url'), '/'); // Ensure no trailing slash
        // $this->apiKey = config('whatsapp.api_key');

        // // Basic check if config is set
        // if (empty($this->baseUrl) || empty($this->apiKey)) {
        //     // You might throw an exception or handle this more gracefully
        //     // depending on your application's needs.
        //      Log::error('WhatsApp API Base URL or API Key is not configured.');
        // }
    }

    /**
     * Prepare HTTP client with common headers.
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
     protected function prepareHttpClient()
    {
        $settings = Settings::where('id', 1)->first();
        if($settings->whatsApp_URL_callback == null){
            return response()->json([
                'success' => false,
                'error' => 'URL is not Found in Database.',
            ], 500);
        }
        $baseUrl = $settings->whatsApp_URL_callback;
        $apiKey = $settings->whatsapp_api_key;
       
        if (!$apiKey) {
            // Handle case where API key is missing
            throw new \Exception("WhatsApp API Key is not configured.");
        }
       
        return Http::withHeaders([
            'Accept' => 'application/json',
            'x-api-key' => $apiKey,
        ])->baseUrl($baseUrl) // Set base URL for convenience
          ->timeout(30); // Set a reasonable timeout (in seconds)
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
$client = new Client();
       $response = $client->request('GET', 'https://wapi.growthlabs.app');
        if($response->getStatusCode() == 200) {

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
    } else {

    return ['success' => false, 'message' => __("admin.URLNotWorking")];
    }
    } catch (\Exception $e) {
        Log::error('Failed to send OTP via WhatsApp: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
  /**
     * Send a message through WhatsApp.
     * Corresponds to: POST /client/sendMessage/{sessionId}
     *
     * @param string $sessionId
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
public function sendMessage($phoneNumber, $message)
{
    $settings = Settings::where('id', 1)->first();
         if($settings->whatsApp_Status_Session == 1){    
            $sessionId = $settings->whatsApp_SESSION_ID;
        }else{
            return response()->json([
                'success' => false,
                'error' => 'Session is not connected.',
                'api_status' => 500,
                'api_error' => 'Session is not connected.'
            ], 500);
        }
        $endpoint = "/client/sendMessage/{$sessionId}";
        try {
            $response = $this->prepareHttpClient()->post($endpoint, [
                'chatId' => $phoneNumber.'@c.us',
                'contentType' => 'string',
                'content' => $message
            ]);
            $response->throw();
            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully.',
                'api_response' => $response->json()
            ]);
        } catch (RequestException $e) {
            Log::error("WhatsApp API error sending message from session {$sessionId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to send message.',
                'api_status' => $e->response?->status(),
                'api_error' => $e->response?->json()['error'] ?? $e->getMessage()
            ], $e->response?->status() ?? 500);
        } catch (\Exception $e) {
            Log::error("General error sending WhatsApp message from session {$sessionId}: " . $e->getMessage());
          
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred while sending the message.'
            ], 500);
        }
}

// private function getChatId($phoneNumber)
// {
//     $sessionId = 'ehabwhats';
//     $apiKey = env('WAPI_KEY');
    
//     $response = Http::withHeaders([
//         'x-api-key' => $apiKey
//     ])->post("https://wapi.growthlabs.app/client/getNumberId/{$sessionId}", [
//         'number' => $phoneNumber
//     ]);
   
//     return $response->json('result._serialized');
// }



  



}
