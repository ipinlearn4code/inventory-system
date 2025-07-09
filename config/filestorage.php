<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Assignment Letter File Storage
    |--------------------------------------------------------------------------
    |
    | This configuration determines the storage backend for assignment letter
    | files. Supported drivers are "local" and "minio".
    |
    */

    'assignment_letters' => [
        'driver' => env('ASSIGNMENT_LETTER_STORAGE_DRIVER', 'local'),
        
        'drivers' => [
            'local' => [
                'root' => storage_path('app/assignment-letters'),
                'url' => env('APP_URL').'/storage/assignment-letters',
                'visibility' => 'private',
            ],
            
            'minio' => [
                'driver' => 's3',
                'key' => env('MINIO_ACCESS_KEY'),
                'secret' => env('MINIO_SECRET_KEY'),
                'region' => env('MINIO_REGION', 'us-east-1'),
                'bucket' => env('MINIO_BUCKET', 'assignment-letters'),
                'endpoint' => env('MINIO_ENDPOINT'),
                'use_path_style_endpoint' => env('MINIO_USE_PATH_STYLE_ENDPOINT', true),
                'throw' => false,
            ],
        ],
    ],
];
