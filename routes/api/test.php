<?php

use App\Http\Controllers\Api\v1\{
    DashboardController,
    DeviceController,
    DeviceAssignmentController,
    FormOptionsController
};
use Illuminate\Support\Facades\Route;

// Test routes for development and staging only
Route::prefix('test')->group(function () {
    Route::prefix('device-assignments')->group(function () {
        Route::get('/form-options', [FormOptionsController::class, 'deviceAssignmentFormOptions']);
        Route::get('/', [DeviceAssignmentController::class, 'index']);
        Route::get('/{id}', [DeviceAssignmentController::class, 'show']);
        Route::post('/', [DeviceAssignmentController::class, 'store']);
        Route::put('/{id}', [DeviceAssignmentController::class, 'update']);
        Route::post('/{id}/return', [DeviceAssignmentController::class, 'returnDevice']);
    });
    Route::get('/assignment/{id}', [DeviceAssignmentController::class, 'show']);
    Route::get('/dashboard', [DashboardController::class, 'kpis']);
    Route::get('/devices', [DeviceController::class, 'index'])->middleware('api.cache');
    Route::get('/device-assignment-form-options', [FormOptionsController::class, 'deviceAssignmentFormOptions']);
    Route::get('/device-form-options', [FormOptionsController::class, 'deviceFormOptions']);
});
