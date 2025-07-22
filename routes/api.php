<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\StorageController;
use App\Http\Controllers\Api\v1\DeviceController as V1DeviceController;
use App\Http\Controllers\Api\v1\DeviceAssignmentController as V1DeviceAssignmentController;
use App\Http\Controllers\Api\v1\DashboardController as V1DashboardController;
use App\Http\Controllers\Api\v1\UserController as V1UserController;
use App\Http\Controllers\Api\v1\MetadataController as V1MetadataController;
use App\Http\Controllers\Api\v1\FormOptionsController as V1FormOptionsController;

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
Route::prefix('test')->group(function () {
    Route::get('/device-assignment-form-options', [V1FormOptionsController::class, 'deviceAssignmentFormOptions']);
    Route::get('/device-form-options', [V1FormOptionsController::class, 'deviceFormOptions']);
});
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
            Route::get('/home/summary', [V1UserController::class, 'homeSummary'])->middleware('api.cache');
            Route::get('/devices', [V1UserController::class, 'devices'])->middleware('api.cache');
            Route::get('/devices/{id}', [V1UserController::class, 'deviceDetails']);
            Route::post('/devices/{id}/report-issue', [V1UserController::class, 'reportIssue']);
            Route::get('/profile', [V1UserController::class, 'profile'])->middleware('api.cache');
            Route::get('/history', [V1UserController::class, 'history']);
        });

        // Admin routes (both admin and superadmin can access)
        Route::prefix('admin')->middleware('role:admin,superadmin')->group(function () {
            Route::get('/dashboard/kpis', [V1DashboardController::class, 'kpis']);
            Route::get('/dashboard/charts', [V1DashboardController::class, 'charts']);
            
            Route::get('/form-options/fields', [V1FormOptionsController::class, 'getFieldOptions']);
            // Device management
            Route::prefix('devices')->group(function () {
                Route::get('/form-options', [V1FormOptionsController::class, 'deviceFormOptions']);
                Route::get('/', [V1DeviceController::class, 'index']);
                Route::get('/{id}', [V1DeviceController::class, 'show']);
                Route::post('/', [V1DeviceController::class, 'store']);
                Route::put('/{id}', [V1DeviceController::class, 'update']);
                Route::delete('/{id}', [V1DeviceController::class, 'destroy']);
            });
            
            // Device assignment management
            Route::prefix('device-assignments')->group(function () {
                Route::get('/form-options', [V1FormOptionsController::class, 'deviceAssignmentFormOptions']);
                Route::get('/', [V1DeviceAssignmentController::class, 'index']);
                Route::get('/{id}', [V1DeviceAssignmentController::class, 'show']);
                Route::post('/', [V1DeviceAssignmentController::class, 'store']);
                Route::put('/{id}', [V1DeviceAssignmentController::class, 'update']);
                Route::post('/{id}/return', [V1DeviceAssignmentController::class, 'returnDevice']);
            });
            
            // User management
            Route::get('/users', [V1MetadataController::class, 'users']);
            
            // Master data
            Route::get('/branches', [V1MetadataController::class, 'branches']);
            Route::get('/categories', [V1MetadataController::class, 'categories']);
            
            // Form options for external apps
            Route::prefix('form-options')->group(function () {
                Route::get('/validation/devices', [V1FormOptionsController::class, 'deviceValidationRules']);
                Route::get('/validation/device-assignments', [V1FormOptionsController::class, 'deviceAssignmentValidationRules']);
            });
            
            // File management (MinIO)
            Route::prefix('files')->group(function () {
                Route::post('/assignment-letters', [StorageController::class, 'uploadAssignmentLetter']);
                Route::get('/assignment-letters/{letterId}/download', [StorageController::class, 'downloadAssignmentLetter'])->name('api.minio.download.assignment-letter');
                Route::get('/assignment-letters/{letterId}/url', [StorageController::class, 'getAssignmentLetterUrl']);
                Route::post('/upload', [StorageController::class, 'uploadFile']);
                Route::post('/download', [StorageController::class, 'downloadFile']);
                Route::delete('/delete', [StorageController::class, 'deleteFile']);
                Route::get('/health', [StorageController::class, 'healthCheck']);
            });
        });
    });
    
    // Internal API routes for admin panel (no auth middleware for simplicity)
    Route::prefix('internal')->group(function () {
        Route::get('/devices/find-by-asset-code/{assetCode}', function ($assetCode) {
            $device = \App\Models\Device::where('asset_code', $assetCode)
                ->whereDoesntHave('currentAssignment') // Only available devices
                ->first();
            
            if (!$device) {
                return response()->json(['error' => 'Device not found or not available'], 404);
            }
            
            return response()->json([
                'device' => [
                    'id' => $device->device_id,
                    'device_id' => $device->device_id,
                    'asset_code' => $device->asset_code,
                    'brand_name' => $device->brand_name,
                    'serial_number' => $device->serial_number,
                    'available' => true
                ]
            ]);
        });

        // QR Scanner endpoint - returns full device info regardless of assignment status
        Route::get('/devices/qr-scan/{assetCode}', function ($assetCode) {
            $device = \App\Models\Device::with(['bribox.category', 'currentAssignment.user', 'currentAssignment.branch'])
                ->where('asset_code', $assetCode)
                ->first();
            
            if (!$device) {
                return response()->json([
                    'success' => false,
                    'error' => "Device with asset code '{$assetCode}' not found",
                    'device' => null
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'error' => null,
                'device' => $device
            ]);
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
