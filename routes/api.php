<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| This file serves as the central API route registry. All routes are
| organized by feature and loaded from separate files in the /routes/api
| directory for better maintainability.
|
*/

Route::prefix('v1')->group(function () {
    // Public routes
    require __DIR__ . '/api/system.php';
    // require __DIR__ . '/api/test.php';

    // Authentication routes
    Route::prefix('auth')->group(function () {
        require __DIR__ . '/api/auth.php';
    });

    // Protected routes
    Route::middleware(['auth:sanctum', 'api.timeout', 'throttle:100,1'])->group(function () {
        // User routes
        Route::prefix('user')->group(function () {
            require __DIR__ . '/api/user.php';
        });

        // Admin routes  
        Route::prefix('admin')->middleware('role:admin,superadmin')->group(function () {
            require __DIR__ . '/api/admin.php';
        });
    });
});
