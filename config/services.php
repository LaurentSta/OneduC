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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'captcha' => [
        'sitekey' => env('NOCAPTCHA_SITEKEY'),
        'secret' => env('NOCAPTCHA_SECRET'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', rtrim(env('APP_URL', 'http://localhost'), '/').'/auth/google/callback'),
    ],

    'discord' => [
        'support_invite_url' => env('DISCORD_SUPPORT_INVITE_URL', ''),
        'support_webhook_url' => env('DISCORD_SUPPORT_WEBHOOK_URL', ''),
        'server_id' => env('DISCORD_SERVER_ID', ''),
    ],

    'hedgedoc' => [
        'base_url' => env('HEDGEDOC_BASE_URL', ''),
        'new_path' => env('HEDGEDOC_NEW_PATH', '/new'),
    ],

    'mistral' => [
        'api_key' => env('MISTRAL_API_KEY', ''),
        'model' => env('MISTRAL_MODEL', 'mistral-large-latest'),
        'monthly_token_limit' => env('MISTRAL_MONTHLY_TOKEN_LIMIT', 500000),
        'admin_monthly_token_limit' => env('MISTRAL_ADMIN_MONTHLY_TOKEN_LIMIT', 2000000),
        'admin_daily_generation_limit' => env('MISTRAL_ADMIN_DAILY_GENERATION_LIMIT', 20),
    ],

    'piper' => [
        'binary' => env('PIPER_BINARY_PATH', storage_path('app/piper/piper/piper')),
        'model' => env('PIPER_MODEL_PATH', storage_path('app/piper/voices/fr_FR-siwis-medium.onnx')),
    ],

    'slides' => [
        'soffice_binary' => env('SLIDES_SOFFICE_BINARY', 'soffice'),
        'pdftocairo_binary' => env('SLIDES_PDFTOCAIRO_BINARY', 'pdftocairo'),
    ],

];
