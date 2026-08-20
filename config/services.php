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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'deepl' => [
        'auth_key' => env('DEEPL_AUTH_KEY', env('DEEPL_API_KEY')),
        'plan' => env('DEEPL_API_PLAN', 'pro'), // pro|free
        'pro_url' => env('DEEPL_PRO_URL', 'https://api.deepl.com'),
        'free_url' => env('DEEPL_FREE_URL', 'https://api-free.deepl.com'),
        'timeout' => (int) env('DEEPL_TIMEOUT', 20),
    ],

    'flutterwave' => [
        'secret_key' => env('FLUTTERWAVE_SECRET_KEY', env('FLW_SECRET_KEY')),
        'public_key' => env('FLUTTERWAVE_PUBLIC_KEY', env('FLW_PUBLIC_KEY')),
        'secret_hash' => env('FLUTTERWAVE_SECRET_HASH', env('FLW_SECRET_HASH')),
    ],

    'vote' => [
        'url' => env('VOTE_URL', 'https://jukanye.online/apk/eVoting.apk'),
    ],

];
