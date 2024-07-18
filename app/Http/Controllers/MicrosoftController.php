<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\OAuth2\Client\Provider\GenericProvider;
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
//    public function redirect($mentorship_id)
//    {
//        $provider = new GenericProvider([
//            'clientId'                => env('MICROSOFT_CLIENT_ID'),
//            'clientSecret'            => env('MICROSOFT_CLIENT_SECRET_VALUE'),
//            'redirectUri'             => env('MICROSOFT_REDIRECT_URI'),
//            'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
//            'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
//            'urlResourceOwnerDetails' => '',
//            'scopes'                  => 'openid profile User.Read OnlineMeetings.ReadWrite'
//        ]);
//
//        $authorizationUrl = $provider->getAuthorizationUrl();
//        session(['oauth2state' => $provider->getState()]);
//        session(['mentorship_id' => $mentorship_id]);
//
//        return redirect()->away($authorizationUrl);
//    }
//
//    public function callback(Request $request)
//    {
//        dd($request->session()->pull('mentorship_id'));
//        $provider = new GenericProvider([
//            'clientId'                => env('MICROSOFT_CLIENT_ID'),
//            'clientSecret'            => env('MICROSOFT_CLIENT_SECRET_VALUE'),
//            'redirectUri'             => env('MICROSOFT_REDIRECT_URI'),
//            'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
//            'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
//            'urlResourceOwnerDetails' => '',
//            'scopes'                  => 'openid profile User.Read OnlineMeetings.ReadWrite'
//        ]);
//
//        // Validate the OAuth state
//        $state = $request->session()->pull('oauth2state');
//        if (empty($request->input('state')) || ($request->input('state') !== $state)) {
//            abort(403, 'Invalid state');
//        }
//
//        // Get the access token using the authorization code grant
//        try {
//            $accessToken = $provider->getAccessToken('authorization_code', [
//                'code' => $request->input('code')
//            ]);
//            // Use the access token to interact with Microsoft Graph API
//            $graph = new Graph();
//            $graph->setAccessToken($accessToken->getToken());
//
//            $user = $graph->createRequest('GET', '/me')
//                ->setReturnType(Model\User::class)
//                ->execute();
//
//            $meeting = [
//                'subject' => 'Team Meeting',
//                'startDateTime' =>'2024-05-18T14:30:34.2444915-07:00',
//                'endDateTime' => '2024-05-18T14:40:34.2444915-07:00',
//            ];
//            $meeting= $graph->createRequest("POST", "/me/onlineMeetings")
//                ->attachBody($meeting)
//                ->setReturnType(Model\OnlineMeeting::class)
//                ->execute();
//
//            // You can now use $user object to get user details and store them in your database
//            dd($user);
//
//        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
//            // Failed to get the access token or user details
//            abort(500, $e->getMessage());
//        }
//    }

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
