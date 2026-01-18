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

    'tinymce' => [
        'api_key' => env('TINYMCE_API_KEY'),
    ],

    'calendly' => [
        'enabled' => env('CALENDLY_ENABLED', false),
        'url' => env('CALENDLY_URL', ''),
    ],

    'google' => [
        'pagespeed_api_key' => env('GOOGLE_PAGESPEED_API_KEY'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_uri' => env('OPENAI_BASE_URI', 'https://api.openai.com/v1/'),
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'), // Default to gpt-3.5-turbo which is widely available
        'max_tokens' => env('OPENAI_MAX_TOKENS', 500),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
        'timeout' => env('OPENAI_TIMEOUT', 30),
    ],

    'custom_ai' => [
        'api_url' => env('CUSTOM_AI_API_URL', ''),
        'api_key' => env('CUSTOM_AI_API_KEY'),
        'model' => env('CUSTOM_AI_MODEL', ''),
        'max_tokens' => env('CUSTOM_AI_MAX_TOKENS', 500),
        'temperature' => env('CUSTOM_AI_TEMPERATURE', 0.7),
        'timeout' => env('CUSTOM_AI_TIMEOUT', 30),
        'endpoint' => env('CUSTOM_AI_ENDPOINT', '/chat/completions'),
        'request_format' => env('CUSTOM_AI_REQUEST_FORMAT', 'openai'), // 'openai', 'anthropic', 'custom'
        'auth_header' => env('CUSTOM_AI_AUTH_HEADER', 'Authorization'),
        'auth_prefix' => env('CUSTOM_AI_AUTH_PREFIX', 'Bearer '),
        'headers' => [], // Additional headers as key-value pairs
    ],

];
