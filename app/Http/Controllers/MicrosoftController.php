<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use GuzzleHttp\Client;

class MicrosoftController extends Controller
{
    protected $client;
    protected $graph;

    public function __construct()
    {
        $this->client = new Client();
        $this->graph = new Graph();
        $this->graph->setAccessToken($this->getAccessToken());
    }

    protected function getAccessToken()
    {
        $response = $this->client->post('https://login.microsoftonline.com/' . env('MICROSOFT_TENANT_ID') . '/oauth2/v2.0/token', [
            'form_params' => [
                'client_id' => env('MICROSOFT_CLIENT_ID'),
                'client_secret' => env('MICROSOFT_CLIENT_SECRET_VALUE'),
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ],
        ]);

        $body = json_decode($response->getBody(), true);
        return $body['access_token'];
    }

    public function getUserIdByEmail($email)
    {
        $user = $this->graph->createRequest("GET", "/users?\$filter=mail eq '{$email}'")
            ->setReturnType(Model\User::class)
            ->execute();

        return $user[0]->getId(); // Get the user ID

    }

    public function createMeeting(Request $request)
    {
        $userId = $this->getUserIdByEmail('see@arweqah.sa');

        $meeting = [
            'subject' => 'Team Meeting 1',
            'startDateTime' =>'2024-07-04T20:00:48.9565231Z',
            'endDateTime' => '2024-07-04T20:23:48.9565231Z',
            'participants' => [
                "organizer"=>[
                    'upn'=>'afeisal@growthlabs.app',
                    "role" => "presenter"
                ],
                'attendees' => [
                    [
                        'upn'=>'shams.tic5@msn.com',
                    ],
                    [
                        'upn'=>'afeisal@growthlabs.app',
                        "role" => "presenter"
                    ],
                ]
            ],
            "lobbyBypassSettings"=> [
                "scope"=> "everyone",
                "isDialInBypassEnabled"=> true
            ]

        ];


        $response = $this->graph->createRequest('POST', "/users/{$userId}/onlineMeetings")
            ->attachBody($meeting)
            ->setReturnType(Model\OnlineMeeting::class)
            ->execute();

        return response()->json($response);
    }
}
