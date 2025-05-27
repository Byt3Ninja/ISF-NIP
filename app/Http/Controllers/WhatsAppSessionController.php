<?php

namespace App\Http\Controllers;

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
       
       
        if (!$this->apiKey) {
            // Handle case where API key is missing
            throw new \Exception("WhatsApp API Key is not configured.");
        }

       
        return Http::withHeaders([
            'Accept' => 'application/json',
            'x-api-key' => $this->apiKey,
        ])->baseUrl($this->baseUrl) // Set base URL for convenience
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
        
        $endpoint = "/session/start/{$sessionId}";

        try {
            $response = $this->prepareHttpClient()->get($endpoint);

            // Throw an exception for client (4xx) or server (5xx) errors
            $response->throw();

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
        $endpoint = "/session/status/{$sessionId}";

        try {
            $response = $this->prepareHttpClient()->get($endpoint);
            $response->throw();

            // Return the successful JSON response from the API directly
            return response()->json($response->json());

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
        $endpoint = "/session/qr/{$sessionId}/image";
      
        
        try {
             
            // Don't expect JSON here, expect image/png
            $response = $this->prepareHttpClient()
                             ->withHeaders(['Accept' => 'image/png']) // Be explicit
                             ->get($endpoint);
            $response->throw(); // Check for 4xx/5xx errors
            dd($response);
            // Check if the response looks like an image
            $contentType = $response->header('Content-Type');
            if (strpos($contentType, 'image/') === 0) {
                // Return the image directly with correct headers
                return response($response->body())
                       ->header('Content-Type', $contentType);
            } else {
                // API returned 200 OK but not an image
                 Log::warning("WhatsApp API returned non-image content for QR Image endpoint for session {$sessionId}. Content-Type: {$contentType}");
                 return response()->json([
                    'success' => false,
                    'error' => 'API did not return a valid image for the QR code.'
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
}
