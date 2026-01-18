<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chatbot Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the AI chatbot service
    |
    */

    'max_context_messages' => env('CHATBOT_MAX_CONTEXT_MESSAGES', 10),
    'max_message_length' => env('CHATBOT_MAX_MESSAGE_LENGTH', 1000),
    'cache_ttl' => env('CHATBOT_CACHE_TTL', 3600), // 1 hour

    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Set to 'openai' to use OpenAI, or 'custom' to use a custom AI provider
    |
    */

    'ai_provider' => env('CHATBOT_AI_PROVIDER', 'openai'), // 'openai' or 'custom'

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'), // Default to gpt-3.5-turbo which is widely available
        'max_tokens' => env('OPENAI_MAX_TOKENS', 500),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
        'timeout' => env('OPENAI_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Context Configuration
    |--------------------------------------------------------------------------
    */

    'context' => [
        'max_messages' => env('CHATBOT_MAX_CONTEXT_MESSAGES', 10),
        'max_tokens' => env('CHATBOT_MAX_CONTEXT_TOKENS', 2000),
        'include_system_prompt' => env('CHATBOT_INCLUDE_SYSTEM_PROMPT', true),
        'truncate_old_messages' => env('CHATBOT_TRUNCATE_OLD_MESSAGES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | System Prompt Configuration
    |--------------------------------------------------------------------------
    */

    'system_prompt' => [
        'enabled' => env('CHATBOT_SYSTEM_PROMPT_ENABLED', true),
        'template' => env('CHATBOT_SYSTEM_PROMPT_TEMPLATE', 'default'),
        'custom' => env('CHATBOT_SYSTEM_PROMPT_CUSTOM', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */

    'rate_limiting' => [
        'enabled' => env('CHATBOT_RATE_LIMIT_ENABLED', true),
        'per_minute' => env('CHATBOT_RATE_LIMIT_PER_MINUTE', 10),
        'per_hour' => env('CHATBOT_RATE_LIMIT_PER_HOUR', 60),
        'per_day' => env('CHATBOT_RATE_LIMIT_PER_DAY', 200),
        'per_conversation_per_minute' => env('CHATBOT_RATE_LIMIT_CONVERSATION_PER_MINUTE', 20),
        'per_user_per_hour' => env('CHATBOT_RATE_LIMIT_USER_PER_HOUR', 100),
    ],
];
