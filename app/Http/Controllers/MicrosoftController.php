<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use League\OAuth2\Client\Provider\GenericProvider;

class MicrosoftController extends Controller
{
    public function redirect()
    {
        $provider = new GenericProvider([
            'clientId'                => env('MICROSOFT_CLIENT_ID'),
            'clientSecret'            => env('MICROSOFT_CLIENT_SECRET_VALUE'),
            'redirectUri'             => env('MICROSOFT_REDIRECT_URI'),
            'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => '',
            'scopes'                  => 'openid profile User.Read OnlineMeetings.ReadWrite'
        ]);

        $authorizationUrl = $provider->getAuthorizationUrl();
        session(['oauth2state' => $provider->getState()]);

        return redirect()->away($authorizationUrl);
    }

    public function callback(Request $request)
    {
        $provider = new GenericProvider([
            'clientId'                => env('MICROSOFT_CLIENT_ID'),
            'clientSecret'            => env('MICROSOFT_CLIENT_SECRET_VALUE'),
            'redirectUri'             => env('MICROSOFT_REDIRECT_URI'),
            'urlAuthorize'            => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'urlAccessToken'          => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => '',
            'scopes'                  => 'openid profile User.Read OnlineMeetings.ReadWrite'
        ]);

        // Validate the OAuth state
        $state = $request->session()->pull('oauth2state');
        if (empty($request->input('state')) || ($request->input('state') !== $state)) {
            abort(403, 'Invalid state');
        }

        // Get the access token using the authorization code grant
        try {
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $request->input('code')
            ]);
            // Use the access token to interact with Microsoft Graph API
            $graph = new Graph();
            $graph->setAccessToken($accessToken->getToken());

            $user = $graph->createRequest('GET', '/me')
                ->setReturnType(Model\User::class)
                ->execute();

            $meeting = [
                'subject' => 'Team Meeting',
                'startDateTime' =>'2024-05-18T14:30:34.2444915-07:00',
                'endDateTime' => '2024-05-18T14:40:34.2444915-07:00',
            ];
                $meeting= $graph->createRequest("POST", "/me/onlineMeetings")
                    ->attachBody($meeting)
                    ->setReturnType(Model\OnlineMeeting::class)
                    ->execute();

            // You can now use $user object to get user details and store them in your database
            dd($user);

        } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            // Failed to get the access token or user details
            abort(500, $e->getMessage());
        }
    }
}

