<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, SparkPost and others. This file provides a sane default
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT'),
    ], 

    'google' => [
        'client_id' => config('services.google.client_id'),
        'client_secret' => config('services.google.client_secret'),
        'redirect' => config('services.google.redirect'),
    ],
    'microsoft' => [
        'client_id' => config('broadcasting.connections.pusher.MICROSOFT_CLIENT_ID'),
        'client_secret' => config('broadcasting.connections.pusher.MICROSOFT_CLIENT_SECRET'),
        'redirect' => config('broadcasting.connections.pusher.MICROSOFT_REDIRECT_URI'),
    ],
    'linkedin' => [
    'client_id' => config('services.linkedin.client_id'),
    'client_secret' => config('services.linkedin.client_secret'),
    'redirect' => config('services.linkedin.redirect'),
    ],

    'keygen' => [
    'token' => env('KEYGEN_TOKEN'),
    'account' => env('KEYGEN_ACCOUNT'),
   ],

];
