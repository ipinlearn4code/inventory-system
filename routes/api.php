<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/refresh', [AuthController::class, 'refresh'])->middleware(['auth:sanctum', 'api.timeout']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum', 'api.timeout']);
        Route::post('/push/register', [AuthController::class, 'registerPush'])->middleware(['auth:sanctum', 'api.timeout']);
    });

    // Protected routes
    Route::middleware(['auth:sanctum', 'api.timeout', 'throttle:100,1'])->group(function () {
        
        // User routes
        Route::prefix('user')->middleware('role:user')->group(function () {
            Route::get('/home/summary', [UserController::class, 'homeSummary'])->middleware('api.cache');
            Route::get('/devices', [UserController::class, 'devices'])->middleware('api.cache');
            Route::get('/devices/{id}', [UserController::class, 'deviceDetails']);
            Route::post('/devices/{id}/report', [UserController::class, 'reportIssue']);
            Route::get('/profile', [UserController::class, 'profile'])->middleware('api.cache');
            Route::get('/history', [UserController::class, 'history']);
        });

        // Admin routes (both admin and superadmin can access)
        Route::prefix('admin')->middleware('role:admin,superadmin')->group(function () {
            Route::get('/dashboard/kpis', [AdminController::class, 'dashboardKpis']);
            Route::get('/dashboard/charts', [AdminController::class, 'dashboardCharts']);
            Route::get('/devices', [AdminController::class, 'devices']);
        });
    });

    // Changelog endpoint (public)
    Route::get('/changelog', function () {
        return response()->json([
            'data' => [
                'version' => '1.1',
                'lastUpdated' => '2025-07-10',
                'changes' => [
                    'Added standardized response format',
                    'Implemented rate limiting (100 req/min)',
                    'Added offline support for key endpoints',
                    'Enhanced error handling with error codes'
                ]
            ]
        ]);
    });

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
                    'offline_support' => 'Available for key endpoints',
                    'monitoring' => 'Laravel Telescope'
                ]
            ]
        ]);
    });
});

// Test upload route in API (no web middleware)
Route::post('/test-upload-api', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'API upload test working',
        'has_file' => $request->hasFile('test_file'),
        'method' => $request->method()
    ]);
});
