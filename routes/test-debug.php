<?php

// Simple test route to debug
Route::get('/debug-test', function () {
    return response()->json([
        'message' => 'Debug route is working',
        'timestamp' => now(),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version()
    ]);
});

Route::post('/debug-upload', function (Illuminate\Http\Request $request) {
    return response()->json([
        'message' => 'POST route is working',
        'has_file' => $request->hasFile('test_file'),
        'method' => $request->method(),
        'content_type' => $request->header('Content-Type'),
        'all_data' => $request->all(),
        'csrf_token' => $request->input('_token'),
        'session_token' => session()->token()
    ]);
});

// Test file upload without CSRF
Route::post('/debug-file-upload', function (Illuminate\Http\Request $request) {
    try {
        return response()->json([
            'success' => true,
            'message' => 'File upload endpoint reached',
            'has_file' => $request->hasFile('test_file'),
            'files' => $request->allFiles(),
            'method' => $request->method(),
            'headers' => $request->headers->all()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
