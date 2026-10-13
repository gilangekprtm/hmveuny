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

    'github' => [
        'api_url' => env('GITHUB_API_URL', 'https://api.github.com'),
        'data_repository' => env('GITHUB_DATA_REPOSITORY'),
        'data_token' => env('GITHUB_DATA_TOKEN'),
        'data_branch' => env('GITHUB_DATA_BRANCH', 'main'),
        'data_directory' => env('GITHUB_DATA_DIRECTORY', 'data'),
    ],

    'vercel_blob' => [
        'internal_token' => env('VERCEL_BLOB_INTERNAL_TOKEN'),
        'deployment_url' => env('VERCEL_URL', env('APP_URL')),
    ],

];
