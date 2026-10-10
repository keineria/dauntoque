<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Bio con IA del editor. El alias "latest" evita que un modelo retirado devuelva 404.
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('DAUNTOQUE_AI_MODEL', env('GEMINI_MODEL', 'gemini-flash-latest')),
        'timeout' => 15,
    ],

    // Pagos en Colombia (y en EE. UU. mientras se abre Stripe). Llaves en el panel de Bold > Integraciones.
    'bold' => [
        'api_key' => env('BOLD_API_KEY'),
        // Llave secreta: firma los webhooks. En modo de pruebas Bold firma con una llave vacía.
        'secret' => env('BOLD_SECRET_KEY', ''),
        'url' => env('BOLD_API_URL', 'https://integrations.api.bold.co'),
        'timeout' => 15,
    ],

];
