<?php

use App\Http\Controllers\Api\v1\{
    DashboardController,
    DeviceController,
    DeviceAssignmentController,
    FormOptionsController,
    MetadataController
};
use App\Http\Controllers\{
    AssignmentLetterFileController,
    Api\StorageController
};
use Illuminate\Support\Facades\Route;

// Dashboard
Route::prefix('dashboard')->group(function () {
    Route::get('/kpis', [DashboardController::class, 'kpis']);
    Route::get('/charts', [DashboardController::class, 'charts']);
});

// Form Options
Route::prefix('form-options')->group(function () {
    Route::get('/fields', [FormOptionsController::class, 'getFieldOptions']);
    Route::get('/validation/devices', [FormOptionsController::class, 'deviceValidationRules']);
    Route::get('/validation/device-assignments', [FormOptionsController::class, 'deviceAssignmentValidationRules']);
});

// Device Management
Route::prefix('devices')->group(function () {
    Route::get('/form-options', [FormOptionsController::class, 'deviceFormOptions']);
    Route::get('/', [DeviceController::class, 'index']);
    Route::get('/{id}', [DeviceController::class, 'show']);
    Route::post('/', [DeviceController::class, 'store']);
    Route::put('/{id}', [DeviceController::class, 'update']);
    Route::post('/{id}', [DeviceController::class, 'update']);
    Route::delete('/{id}', [DeviceController::class, 'destroy']);
});

// Device Assignment Management
Route::prefix('device-assignments')->group(function () {
    Route::get('/form-options', [FormOptionsController::class, 'deviceAssignmentFormOptions']);
    Route::get('/', [DeviceAssignmentController::class, 'index']);
    Route::get('/{id}', [DeviceAssignmentController::class, 'show']);
    Route::post('/', [DeviceAssignmentController::class, 'store']);
    Route::patch('/{id}', [DeviceAssignmentController::class, 'update']);
    Route::post('/{id}/return', [DeviceAssignmentController::class, 'returnDevice']);
});

// Assignment Letters
Route::prefix('assignment-letters')->group(function () {
    Route::get('/', [AssignmentLetterFileController::class, 'getAssignmentLetterData']);
    Route::get('/{id}', [AssignmentLetterFileController::class, 'getAssignmentLetterById']);
});

// Master Data
Route::get('/users', [MetadataController::class, 'users']);
Route::get('/branches', [MetadataController::class, 'branches']);
Route::get('/categories', [MetadataController::class, 'categories']);

// File Management (MinIO)
Route::prefix('files')->group(function () {
    Route::post('/assignment-letters', [StorageController::class, 'uploadAssignmentLetter']);
    Route::get('/assignment-letters/{letterId}/download', [StorageController::class, 'downloadAssignmentLetter'])
        ->name('api.minio.download.assignment-letter');
    Route::get('/assignment-letters/{letterId}/url', [StorageController::class, 'getAssignmentLetterUrl']);
    Route::post('/upload', [StorageController::class, 'uploadFile']);
    Route::post('/download', [StorageController::class, 'downloadFile']);
    Route::delete('/delete', [StorageController::class, 'deleteFile']);
    Route::get('/health', [StorageController::class, 'healthCheck']);
});
