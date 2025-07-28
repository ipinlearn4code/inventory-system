<?php

use Illuminate\Support\Facades\Route;

// API Status endpoint (public)
Route::get('/status', function () {
    return response()->json([
        'data' => [
            'status' => 'operational',
            'version' => '1.1',
            'timestamp' => now()->toISOString(),
            'features' => [
                'authentication' => 'Laravel Sanctum',
                'rate_limiting' => '100 req/min/user',
                'timeout' => '30 seconds',
                'file_storage' => 'MinIO S3 Compatible',
                'qr_code' => 'Endroid QR Code',
            ]
        ]
    ]);
});

// Health check endpoint (public)
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString()
    ]);
});
