<?php

return [
    /*
    |--------------------------------------------------------------------------
    | eBook Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the free eBook download system.
    |
    */

    /**
     * Path to the eBook file relative to public directory
     */
    'file_path' => env('EBOOK_FILE_PATH', 'ebooks/free-ebook.pdf'),

    /**
     * Download link type: 'direct' or 'secure'
     * - 'direct': Direct public link to the file
     * - 'secure': Token-based secure download link
     */
    'download_type' => env('EBOOK_DOWNLOAD_TYPE', 'direct'),

    /**
     * Email settings
     */
    'email' => [
        'from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'from_name' => env('MAIL_FROM_NAME', 'The Strengths Toolbox'),
    ],

    /**
     * Subscriber source identifier
     */
    'subscriber_source' => 'ebook-signup',
];
