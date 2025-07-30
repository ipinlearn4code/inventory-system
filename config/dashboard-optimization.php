<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dashboard Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for optimizing dashboard performance and reducing
    | duplicate JavaScript loading.
    |
    */

    'assets' => [
        
        /*
        |--------------------------------------------------------------------------
        | Script Deduplication
        |--------------------------------------------------------------------------
        |
        | Enable script deduplication to prevent the same JavaScript files
        | from being loaded multiple times.
        |
        */
        'deduplicate_scripts' => true,
        
        /*
        |--------------------------------------------------------------------------
        | Lazy Loading
        |--------------------------------------------------------------------------
        |
        | Enable lazy loading for non-critical components to improve
        | initial page load time.
        |
        */
        'lazy_loading' => [
            'charts' => true,
            'qr_scanner' => true,
            'file_uploads' => true,
            'rich_editors' => true,
        ],
        
        /*
        |--------------------------------------------------------------------------
        | CDN Assets
        |--------------------------------------------------------------------------
        |
        | External CDN assets that should be loaded only once globally.
        |
        */
        'cdn_assets' => [
            'chart_js' => 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js',
            'html5_qrcode' => 'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js',
        ],
        
    ],

    /*
    |--------------------------------------------------------------------------
    | Widget Optimization
    |--------------------------------------------------------------------------
    |
    | Settings for optimizing dashboard widgets.
    |
    */
    'widgets' => [
        
        /*
        |--------------------------------------------------------------------------
        | Chart Widgets
        |--------------------------------------------------------------------------
        |
        | Configuration for chart widgets to prevent duplicate chart.js loading.
        |
        */
        'charts' => [
            'use_global_chartjs' => true,
            'defer_initialization' => true,
            'max_height' => '300px',
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Polling Intervals
        |--------------------------------------------------------------------------
        |
        | Configure polling intervals for real-time widgets.
        |
        */
        'polling' => [
            'activity_log' => '30s',
            'storage_status' => '60s',
            'device_stats' => '120s',
        ],
        
    ],

    /*
    |--------------------------------------------------------------------------
    | Component Optimization
    |--------------------------------------------------------------------------
    |
    | Settings for optimizing individual components.
    |
    */
    'components' => [
        
        /*
        |--------------------------------------------------------------------------
        | QR Scanner
        |--------------------------------------------------------------------------
        |
        | QR scanner component optimization settings.
        |
        */
        'qr_scanner' => [
            'global_modal' => true,
            'singleton_instance' => true,
            'preload_camera' => false,
        ],
        
        /*
        |--------------------------------------------------------------------------
        | File Upload
        |--------------------------------------------------------------------------
        |
        | File upload component optimization settings.
        |
        */
        'file_upload' => [
            'chunk_size' => '1MB',
            'concurrent_uploads' => 3,
            'preview_thumbnails' => true,
        ],
        
    ],

];
