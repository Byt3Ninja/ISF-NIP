<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResendApiKeyValidator
{
    /**
     * Validate a Resend API key by calling the Resend API.
     */
    public static function validate(string $apiKey): bool
    {
        try {
            $response = Http::withToken($apiKey)
                ->timeout(10)
                ->get('https://api.resend.com/domains');

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Resend API key validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
