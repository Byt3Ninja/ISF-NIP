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
    protected string $baseUrl;
    protected ?string $apiKey; // Nullable in case it's missing

    public function __construct()
    {
        // $settings = Settings::where('id', 1)->first();
        // $this->client = new Client([
        //     'base_uri' => $settings->whatsApp_URL_callback ?? 'https://app.wawp.net/api/',
        // ]);

        // $this->instanceId = $settings->whatsApp_INSTANCE_ID ?? '';
        // $this->accessToken = $settings->whatsApp_ACCESS_TOKEN ?? '';
        // Load config values in the constructor
        $this->baseUrl = rtrim(config('whatsapp.base_url'), '/'); // Ensure no trailing slash
        $this->apiKey = config('whatsapp.api_key');

        // Basic check if config is set
        if (empty($this->baseUrl) || empty($this->apiKey)) {
            // You might throw an exception or handle this more gracefully
            // depending on your application's needs.
            Log::error('WhatsApp API Base URL or API Key is not configured.');
        }
    }

    /**
     * Prepare HTTP client with common headers.
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function prepareHttpClient()
    {
        // $settings = Settings::where('id', 1)->first();
        // if($settings->whatsApp_URL_callback == null){
        //     return response()->json([
        //         'success' => false,
        //         'error' => 'URL is not Found in Database.',
        //     ], 500);
        // }
        $baseUrl = $this->baseUrl;
        $apiKey = $this->apiKey;

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
            if ($response->getStatusCode() == 200) {

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
     * @return array{success: bool} Same shape as sendOTP(); callers only log it.
     */
    public function sendMessage($phoneNumber, $message, $userOrganization = null)
    {

        if ($userOrganization && $userOrganization->org_whatsApp_Status_Session == 1) {
            $sessionId = $userOrganization->org_whatsApp_SESSION_ID;
        } else {
            $settings = Settings::find(1);
            if ($settings && $settings->whatsApp_Status_Session == 1) {
                $sessionId = $settings->whatsApp_SESSION_ID;
            } else {
                return [
                    'success' => false,
                    'error' => 'Session is not connectedddd.',
                    'api_status' => 500,
                    'api_error' => 'Session is not connectedddd.'
                ];
            }
        }
        // $sessionId = 147;
        $endpoint = "/client/sendMessage/{$sessionId}";
        try {
            $response = $this->prepareHttpClient()->post($endpoint, [
                'chatId' => $phoneNumber . '@c.us',
                'contentType' => 'string',
                'content' => $message
            ]);
            $response->throw();
            return [
                'success' => true,
                'message' => 'Message sent successfully.',
                'api_response' => $response->json()
            ];
        } catch (RequestException $e) {
            Log::error("WhatsApp API error sending message from session {$sessionId}: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to send message.',
                'api_status' => $e->response?->status() ?? 500,
                'api_error' => $e->response?->json()['error'] ?? $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error("General error sending WhatsApp message from session {$sessionId}: " . $e->getMessage());

            return [
                'success' => false,
                'error' => 'An unexpected error occurred while sending the message.'
            ];
        }
    }

}