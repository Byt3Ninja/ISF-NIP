<?php
namespace App\Services;

use Microsoft\Graph\Graph;

class MicrosoftGraphService
{
    protected $graph;
    protected $tenantId;
    protected $clientId;
    protected $clientSecret;
    protected $userId;

    protected $access_token;

    public function __construct()
    {
        $this->tenantId = env('MICROSOFT_TENANT_ID');
        $this->clientId = env('MICROSOFT_CLIENT_ID');
        $this->userId = env('MICROSOFT_USER_ID');
        $this->clientSecret = env('MICROSOFT_CLIENT_SECRET_VALUE');
        $this->graph = new Graph();
        $this->authenticate();
    }

    protected function authenticate()
    {
        $url = "https://login.microsoftonline.com/{$this->tenantId}/oauth2/v2.0/token";

        $postData = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => 'https://graph.microsoft.com/.default',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception('cURL error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            throw new \Exception('Error: HTTP Code ' . $httpCode . ' Response: ' . $response);
        }

        curl_close($ch);

        $body = json_decode($response, true);
        dd($body);
        if (isset($body['error'])) {
            throw new \Exception($body['error_description']);
        }

        $token = $body['access_token'];
        $this->graph->setAccessToken($token);
    }


    public function createTeamsMeeting($subject, $startDateTime, $endDateTime)
    {

        $meeting = [
            "subject" => $subject,
            "startDateTime" =>  $startDateTime,
            "endDateTime" => $endDateTime,
        ];

        return $this->graph->createRequest("POST", "/me/onlineMeetings")
            ->attachBody(json_encode($meeting))
            ->setReturnType(\Microsoft\Graph\Model\OnlineMeeting::class)
            ->execute();
    }
}
