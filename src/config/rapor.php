<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Rapor Distribution Configuration
     |--------------------------------------------------------------------------
     |
     | Configuration for the Rapor Distribution System
     |
     */

    // Maximum retry attempts for failed distributions
    'max_retries' => env('RAPOR_MAX_RETRIES', 3),

    // Queue name for distribution jobs
    'queue' => env('RAPOR_QUEUE', 'default'),

    // PDF storage disk
    'storage_disk' => env('RAPOR_STORAGE_DISK', 'local'),

    // Path for storing distributed PDFs
    'pdf_path' => 'rapor-distribution',

    // PDF link expiration (in hours)
    'pdf_link_expiration' => env('RAPOR_PDF_LINK_EXPIRATION', 168), // 7 days

    // WhatsApp templates
    'whatsapp' => [
        'template_name' => 'rapor_distribution',
        'language' => 'id',
    ],

    // Email configuration
    'email' => [
        'from_name' => env('APP_NAME', 'ProductSchool'),
        'reply_to' => env('RAPOR_REPLY_EMAIL', 'noreply@productschool.id'),
    ],
];
