<?php

namespace App\Services;

use App\Exceptions\ResendApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Thin client for the Resend REST calls behind the email configuration
 * connect flow. Every call takes the API key explicitly: the key being
 * connected is not saved until a test email has gone through.
 *
 * @see https://resend.com/docs/api-reference/introduction
 */
class ResendApi
{
    private const BASE_URL = 'https://api.resend.com';

    /**
     * List the account's domains. Doubles as the API key check: Resend
     * rejects unknown keys and sending-only keys on this endpoint.
     *
     * @return array<int, array{id: string, name: string, status: string}>
     */
    public function listDomains(string $apiKey): array
    {
        $data = $this->call(fn () => $this->client($apiKey)->get('/domains', ['limit' => 100]));

        return collect($data['data'] ?? [])
            ->map(fn (array $domain) => $this->domain($domain))
            ->values()
            ->all();
    }

    /**
     * Add a domain to the account. It starts unverified ("not_started").
     *
     * @return array{id: string, name: string, status: string}
     */
    public function createDomain(string $apiKey, string $name): array
    {
        $data = $this->call(fn () => $this->client($apiKey)->post('/domains', ['name' => $name]));

        return $this->domain($data);
    }

    /**
     * Send one email and return its Resend id.
     */
    public function sendEmail(string $apiKey, array $payload): ?string
    {
        $data = $this->call(fn () => $this->client($apiKey)->post('/emails', $payload));

        return $data['id'] ?? null;
    }

    private function client(string $apiKey): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->withToken($apiKey)
            ->acceptJson()
            ->timeout(15);
    }

    /**
     * @param  callable(): Response  $request
     */
    private function call(callable $request): array
    {
        try {
            $response = $request();
        } catch (ConnectionException $e) {
            throw ResendApiException::network($e);
        }

        if ($response->failed()) {
            throw ResendApiException::fromResponse($response);
        }

        return $response->json() ?? [];
    }

    private function domain(array $domain): array
    {
        return [
            'id'     => (string) ($domain['id'] ?? ''),
            'name'   => (string) ($domain['name'] ?? ''),
            'status' => (string) ($domain['status'] ?? 'not_started'),
        ];
    }
}
