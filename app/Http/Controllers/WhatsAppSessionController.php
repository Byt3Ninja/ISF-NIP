<?php

namespace App\Http\Controllers;

use App\Organization;
use App\Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse; // For type hinting
use Illuminate\Http\Client\RequestException; // To catch HTTP errors

class WhatsAppSessionController extends Controller
{
    protected string $baseUrl;
    protected ?string $apiKey; // Nullable in case it's missing

    public function __construct()
    {
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
        

        $settings = Settings::where('id', 1)->first();
        if($settings->whatsApp_URL_callback == null){
            return response()->json([
                'success' => false,
                'error' => 'URL is not Found in Database.',
            ], 500);
        }
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

    /**
     * Start a new WhatsApp session.
     * Corresponds to: GET /session/start/{sessionId}
     *
     * @param string $sessionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function start(string $sessionId): JsonResponse
    {
        $this->authorizeSession($sessionId);
        $organization = auth()->user()->organization;

        
        // Check if session already exists in database and is not connected
        
        // if ($settings && 
        //     $settings->whatsApp_SESSION_ID === $sessionId && 
        //     $settings->whatsApp_Status_Session == '0') {
            
        //     // Session exists but is not connected, return success without API call
        //     return response()->json([
        //         'success' => true,
        //         'message' => "Session '{$sessionId}' already exists and is ready to connect.",
        //         'session_exists' => true,
        //         'status' => 'not_connected'
        //     ]);
        // }
        
        $endpoint = "/session/start/{$sessionId}";

        try {
            $response = $this->prepareHttpClient()->get($endpoint);

            // Throw an exception for client (4xx) or server (5xx) errors
            $response->throw();

           $organization = auth()->user()->organization;
            if($organization){
                $organization->org_whatsApp_SESSION_ID = $sessionId;
                $organization->save();
            }else{
                // Update settings with new session ID
                $settings = Settings::where('id', 1)->first();
                $settings->whatsApp_SESSION_ID = $sessionId;
                $settings->save();
            }
            // Assuming success returns JSON based on Swagger
            return response()->json([
                'success' => true,
                'message' => "Request to start session '{$sessionId}' sent successfully.",
                'api_response' => $response->json() // Include actual API response
            ]);

        } catch (RequestException $e) {
            // Handle specific HTTP errors from the API
            Log::error("WhatsApp API error starting session {$sessionId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to start session.',
                'api_status' => $e->response?->status(),
                'api_error' => $e->response?->json()['error'] ?? $e->getMessage() // Try to get specific error
            ], $e->response?->status() ?? 500); // Use API error status or default 500

        } catch (\Exception $e) {
            // Handle other errors (network, config issues, etc.)
            Log::error("General error starting WhatsApp session {$sessionId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred while starting the session.'
            ], 500);
        }
    }

    /**
     * Get the status of a WhatsApp session.
     * Corresponds to: GET /session/status/{sessionId}
     *
     * @param string $sessionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(string $sessionId): JsonResponse
    {
        $this->authorizeSession($sessionId);
        $endpoint = "/session/status/{$sessionId}";

        try {
            $response = $this->prepareHttpClient()->get($endpoint);
            $response->throw();

            $apiResponse = $response->json();
                $organization = auth()->user()->organization;
                if($organization){
                   
                    if($apiResponse['success'] == false) {
                        $organization->org_whatsApp_SESSION_ID = $sessionId;
                        $organization->org_whatsApp_Status_Session = '0';
                        $organization->save();
                    }else{
                        $organization->org_whatsApp_SESSION_ID = $sessionId;
                        $organization->org_whatsApp_Status_Session = '1';
                        $organization->save();
                    }

                }else{
                    
                    if($apiResponse['success'] == false) {
                        $settings = Settings::where('id', 1)->first();
                        $settings->whatsApp_SESSION_ID = $sessionId;
                        $settings->whatsApp_Status_Session = '0';
                        $settings->save();
                        
                    }else{
                        $settings = Settings::where('id', 1)->first();
                        $settings->whatsApp_SESSION_ID = $sessionId;
                        $settings->whatsApp_Status_Session = '1';
                        $settings->save();
                    }
                }
                
            // Normalize the response to have consistent structure
            return response()->json([
                'success' => $apiResponse['success'] ?? true,
                'status' => $apiResponse['state'] ?? $apiResponse['message'] ?? 'unknown',
                'message' => $apiResponse['message'] ?? 'Status retrieved successfully',
                'raw_response' => $apiResponse // Include raw API response for debugging
            ]);

        } catch (RequestException $e) {
            Log::error("WhatsApp API error getting status for session {$sessionId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to get session status.',
                'api_status' => $e->response?->status(),
                'api_error' => $e->response?->json()['error'] ?? $e->getMessage()
            ], $e->response?->status() ?? 500);

        } catch (\Exception $e) {
            Log::error("General error getting WhatsApp session status {$sessionId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred while getting session status.'
            ], 500);
        }
    }

    /**
     * Get the QR code data (likely base64/text) for a session.
     * Corresponds to: GET /session/qr/{sessionId}
     *
     * @param string $sessionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQrData(string $sessionId): JsonResponse
    {
        $this->authorizeSession($sessionId);
        $endpoint = "/session/qr/{$sessionId}";

        try {
             // IMPORTANT: The Swagger for this endpoint just says "200 OK".
             // We are *assuming* it returns JSON containing the QR data.
             // If it returns raw text/base64 directly, you'd use $response->body()
             // and adjust the success JSON response structure below.
            $response = $this->prepareHttpClient()->get($endpoint);
            $response->throw();

            // Assuming the QR data is within the JSON response
            // Adjust the key 'qr_data' if the actual API response uses a different key
            $qrData = $response->json(); // Or $response->json()['some_key_containing_qr']

            if (empty($qrData)) {
                 // Handle case where API returns 200 OK but empty body or unexpected structure
                 return response()->json([
                    'success' => false,
                    'error' => "Received empty or unexpected QR data from API for session '{$sessionId}'."
                ], 502); // Bad Gateway or other appropriate error
            }

            return response()->json([
                'success' => true,
                'message' => "QR data retrieved for session '{$sessionId}'.",
                'qr_data' => $qrData // Assuming the entire JSON is the QR data, or adjust as needed
            ]);

        } catch (RequestException $e) {
            Log::error("WhatsApp API error getting QR data for session {$sessionId}: " . $e->getMessage());
             // Check for 422 specifically, which might mean session is not in QR state
            $errorMessage = 'Failed to get session QR data.';
            if($e->response?->status() === 422) {
                $errorMessage = $e->response?->json()['error'] ?? "Cannot get QR code. Session '{$sessionId}' might not be in the correct state (e.g., already connected) or ID is invalid.";
            } else {
                 $errorMessage = $e->response?->json()['error'] ?? $e->getMessage();
            }

            return response()->json([
                'success' => false,
                'error' => $errorMessage,
                'api_status' => $e->response?->status(),
            ], $e->response?->status() ?? 500);

        } catch (\Exception $e) {
            Log::error("General error getting WhatsApp session QR data {$sessionId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred while getting session QR data.'
            ], 500);
        }
    }

     /**
     * Get the QR code as an image.
     * Corresponds to: GET /session/qr/{sessionId}/image
     *
     * @param string $sessionId
     * @return \Symfony\Component\HttpFoundation\Response|\Illuminate\Http\JsonResponse
     */
    public function getQrImage(string $sessionId) // Return type is broader here
    {
        $this->authorizeSession($sessionId);
        $endpoint = "/session/qr/{$sessionId}/image";
      
        
        try {
             
            // Don't expect JSON here, expect image/png
            $response = $this->prepareHttpClient()
                             ->withHeaders(['Accept' => 'image/png']) // Be explicit
                             ->get($endpoint);
            $response->throw(); // Check for 4xx/5xx errors
            
            // Check if the response looks like an image
            $contentType = $response->header('Content-Type');
            if (strpos($contentType, 'image/') === 0) {
                // Return the image directly with correct headers
                return response($response->body())
                       ->header('Content-Type', $contentType);
            } else {
                // API returned JSON instead of image - check if it's an error or QR not ready
                $responseBody = $response->body();
                $jsonResponse = null;
                
                try {
                    $jsonResponse = json_decode($responseBody, true);
                } catch (\Exception $e) {
                    // Not JSON either
                }
                
                if ($jsonResponse && isset($jsonResponse['message'])) {
                    $message = $jsonResponse['message'];
                    if (strpos($message, 'qr code not ready') !== false) {
                        return response()->json([
                            'success' => false,
                            'error' => 'QR code not ready yet. Please wait a moment and try again.',
                            'retry' => true
                        ], 202); // 202 Accepted - processing but not ready
                    }
                    if (strpos($message, 'already scanned') !== false) {
                        return response()->json([
                            'success' => false,
                            'error' => 'QR code was already scanned. Please check your connection status.',
                            'retry' => false
                        ], 410); // 410 Gone - resource no longer available
                    }
                }
                
                // API returned 200 OK but not an image and not a known error
                Log::warning("WhatsApp API returned non-image content for QR Image endpoint for session {$sessionId}. Content-Type: {$contentType}. Body: {$responseBody}");
                return response()->json([
                    'success' => false,
                    'error' => 'API did not return a valid image for the QR code.',
                    'debug_info' => [
                        'content_type' => $contentType,
                        'response_preview' => substr($responseBody, 0, 200)
                    ]
                ], 502); // Bad Gateway - upstream API error
            }

        } catch (RequestException $e) {
            Log::error("WhatsApp API error getting QR image for session {$sessionId}: " . $e->getMessage());
             $errorMessage = $e->response?->json()['error'] ?? 'Failed to get session QR image.';
             if($e->response?->status() === 422) {
                 $errorMessage = $e->response?->json()['error'] ?? "Cannot get QR image. Session '{$sessionId}' might not be in the correct state or ID is invalid.";
             }

            return response()->json([
                'success' => false,
                'error' => $errorMessage,
                'api_status' => $e->response?->status(),
            ], $e->response?->status() ?? 500);

        } catch (\Exception $e) {
            Log::error("General error getting WhatsApp session QR image {$sessionId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred while getting session QR image.',
                'E' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Terminate a WhatsApp session.
     * Corresponds to: DELETE /session/logout/{sessionId}
     *
     * @param string $sessionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function terminate(string $sessionId)
    {
        $this->authorizeSession($sessionId);
        $endpoint = "/session/terminate/{$sessionId}";

        try {
            $response = $this->prepareHttpClient()->delete($endpoint);
          
            $response->throw();
                $organization = auth()->user()->organization;
                if($organization){
                    $organization->org_whatsApp_SESSION_ID = null;
                    $organization->org_whatsApp_Status_Session = '0';
                    $organization->save();
                }else{
                    $settings = Settings::where('id', 1)->first();
                    $settings->whatsApp_SESSION_ID = null;
                    $settings->whatsApp_Status_Session = '0';
                    $settings->save();
                }
            return response()->json([
                'success' => true,
                'message' => "Session '{$sessionId}' terminated successfully.",
                'api_response' => $response->json()
            ]);

        } catch (RequestException $e) {
            Log::error("WhatsApp API error terminating session {$sessionId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to terminate session.',
                'api_status' => $e->response?->status(),
                'api_error' => $e->response?->json()['error'] ?? $e->getMessage()
            ], $e->response?->status() ?? 500);

        } catch (\Exception $e) {
            Log::error("General error terminating WhatsApp session {$sessionId}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred while terminating the session.'
            ], 500);
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
    public function sendMessage(string $sessionId, Request $request): JsonResponse
    {
        $request->validate([
            'to' => 'required|string',
            'message' => 'required|string'
        ]);

        $endpoint = "/client/sendMessage/{$sessionId}";

        try {
            $response = $this->prepareHttpClient()->post($endpoint, [
                'chatId' => $request->input('to'),
                'contentType' => 'string',
                'content' => $request->input('message')
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

    public function getWASessionID()
    {
        $organizationID = auth()->user()->organization;

        if($organizationID){
            $organiztion = Organization::where('id', $organizationID->id)->first();
            return response()->json([
                'success' => true,
                'sessionId' => $organiztion->org_whatsApp_SESSION_ID,
                'status' => $organiztion->org_whatsApp_Status_Session == 1 ? 'connected' : 'not_connected',
            ]);
        }else{
            $settings = Settings::where('id', 1)->first();
            
            return response()->json([
                'success' => true,
                'sessionId' => $settings->whatsApp_SESSION_ID,
                'status' => $settings->whatsApp_Status_Session == 1 ? 'connected' : 'not_connected',
            ]);
        }
    }


    /**
     * H2: a session id belongs to the organization whose org_whatsApp_SESSION_ID it is, or to the
     * platform (settings). The caller may only act on an unclaimed id or one owned by its own
     * owner (its organization, or the platform when it has none), never another tenant's.
     */
    private function authorizeSession(string $sessionId): void
    {
        $organizationId = auth()->user()->organization?->id;
        $ownerOrganizationId = Organization::where('org_whatsApp_SESSION_ID', $sessionId)->value('id');
        $platformOwned = (string) Settings::where('id', 1)->value('whatsApp_SESSION_ID') === $sessionId;

        $foreign = $organizationId
            ? ($platformOwned || ($ownerOrganizationId && (int) $ownerOrganizationId !== (int) $organizationId))
            : (bool) $ownerOrganizationId;

        abort_if($foreign, 403);
    }
}
