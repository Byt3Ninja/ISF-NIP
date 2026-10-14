<?php

return [

    'default' => env('MAIL_MAILER', 'smtp'),

    'mailers' => [

        'smtp' => [
            'transport'  => 'smtp',
            'host'       => env('MAIL_HOST', 'smtp.gmail.com'),
            'port'       => env('MAIL_PORT', 465),
            'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
            'username'   => env('MAIL_USERNAME'),
            'password'   => env('MAIL_PASSWORD'),
            'timeout'    => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', 'localhost'),
            'stream' => [
                'ssl' => [
                    'verify_peer'       => true,
                    'verify_peer_name'  => true,
                    'allow_self_signed' => false,
                    'cafile'            => '/etc/ssl/certs/ca-certificates.crt',
                    'crypto_method'     => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                                        | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT,
                ],
            ],
        ],
        'resend' => [
            'transport' => 'resend',
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'mailgun' => [
            'transport' => 'mailgun',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path'      => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel'   => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport'  => 'failover',
            'mailers'    => ['smtp', 'log'],
        ],

    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name'    => env('MAIL_FROM_NAME', 'Example'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Embed Logos
    |--------------------------------------------------------------------------
    |
    | Attach email logos inline (CID) instead of linking them by URL. Enable on
    | local/staging hosts that mail clients such as Gmail cannot reach.
    |
    */

    'embed_logos' => env('MAIL_EMBED_LOGOS', false),

    'markdown' => [
        'theme' => 'default',
        'paths' => [
            resource_path('views/vendor/mail'),
        ],
    ],

];