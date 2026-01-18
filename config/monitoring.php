<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for application monitoring and alerting
    |
    */

    'enabled' => env('MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Health Check Configuration
    |--------------------------------------------------------------------------
    */

    'health_check' => [
        'enabled' => env('HEALTH_CHECK_ENABLED', true),
        'interval' => env('HEALTH_CHECK_INTERVAL', 60), // seconds
        'timeout' => env('HEALTH_CHECK_TIMEOUT', 5), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    */

    'performance' => [
        'enabled' => env('PERFORMANCE_MONITORING_ENABLED', true),
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 1000), // milliseconds
        'log_slow_queries' => env('LOG_SLOW_QUERIES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Tracking
    |--------------------------------------------------------------------------
    */

    'error_tracking' => [
        'enabled' => env('ERROR_TRACKING_ENABLED', false),
        'service' => env('ERROR_TRACKING_SERVICE', 'sentry'), // sentry, bugsnag, etc.
        'dsn' => env('ERROR_TRACKING_DSN'),
        'environment' => env('APP_ENV', 'production'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Uptime Monitoring
    |--------------------------------------------------------------------------
    */

    'uptime' => [
        'enabled' => env('UPTIME_MONITORING_ENABLED', true),
        'endpoints' => [
            'basic' => '/health',
            'detailed' => '/health/detailed',
        ],
        'check_interval' => env('UPTIME_CHECK_INTERVAL', 300), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerting
    |--------------------------------------------------------------------------
    */

    'alerts' => [
        'enabled' => env('ALERTS_ENABLED', true),
        'channels' => [
            'email' => [
                'enabled' => env('ALERT_EMAIL_ENABLED', true),
                'to' => env('ALERT_EMAIL_TO', config('mail.from.address')),
            ],
            'slack' => [
                'enabled' => env('ALERT_SLACK_ENABLED', false),
                'webhook' => env('SLACK_WEBHOOK_URL'),
            ],
        ],
        'thresholds' => [
            'error_rate' => env('ALERT_ERROR_RATE_THRESHOLD', 10), // errors per minute
            'response_time' => env('ALERT_RESPONSE_TIME_THRESHOLD', 5000), // milliseconds
            'uptime_failures' => env('ALERT_UPTIME_FAILURES', 3), // consecutive failures
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics Collection
    |--------------------------------------------------------------------------
    */

    'metrics' => [
        'enabled' => env('METRICS_ENABLED', true),
        'collect' => [
            'response_times' => true,
            'error_rates' => true,
            'database_queries' => true,
            'cache_hit_rates' => true,
            'memory_usage' => true,
        ],
    ],

];
