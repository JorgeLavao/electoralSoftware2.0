<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/auth/google/callback'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'maps' => [
        'web_api'   => env('GOOGLE_MAPS_BROWSER_KEY'),
        'back_api'  => env('GOOGLE_MAPS_API_KEY'),
        'libraries' => 'places',
        'region'    => 'CO',
        'language'  => 'es'
    ],

    'clientes_mas' => [
        'enabled' => env('CLIENTES_MAS_MAIL_ENABLED', (bool) env('CLIENTES_MAS_API_KEY')),
        'base_url' => env('CLIENTES_MAS_BASE_URL', 'https://app.clientesmas.com/api/messaging'),
        'api_key' => env('CLIENTES_MAS_API_KEY'),
        'email_provider' => env('CLIENTES_MAS_EMAIL_PROVIDER', 'aws_ses'),
        'timeout' => (int) env('CLIENTES_MAS_TIMEOUT', 15),
        'retries' => (int) env('CLIENTES_MAS_RETRIES', 2),
        'retry_sleep' => (int) env('CLIENTES_MAS_RETRY_SLEEP', 500),
        'from_name' => env('CLIENTES_MAS_FROM_NAME', env('MAIL_FROM_NAME', env('APP_NAME', 'SmartElect'))),
        'from_address' => env('CLIENTES_MAS_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
        'mox' => [
            'from_name' => env('CLIENTES_MAS_MOX_FROM_NAME', env('CLIENTES_MAS_FROM_NAME', env('MAIL_FROM_NAME', env('APP_NAME', 'SmartElect')))),
            'from_address' => env('CLIENTES_MAS_MOX_FROM_ADDRESS', env('CLIENTES_MAS_FROM_ADDRESS', env('MAIL_FROM_ADDRESS'))),
            'auth_user' => env('CLIENTES_MAS_MOX_AUTH_USER'),
            'auth_password' => env('CLIENTES_MAS_MOX_AUTH_PASSWORD'),
        ],
    ]
];
