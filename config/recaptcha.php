<?php

return [

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA v3
    |--------------------------------------------------------------------------
    |
    | Site key (front-end) and secret key (back-end) from Google reCAPTCHA.
    | When enabled is false or keys are empty, verification is skipped (e.g. tests/local).
    |
    */

    'enabled' => env('RECAPTCHA_ENABLED', true),

    'site_key' => env('RECAPTCHA_SITE_KEY', ''),

    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),

    'min_score' => env('RECAPTCHA_MIN_SCORE', 0.5),

];
