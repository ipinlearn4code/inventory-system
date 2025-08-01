<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\DeviceAssignmentController;
use App\Http\Controllers\Api\v1\FormOptionsController;
use App\Http\Controllers\Api\v1\TestController;

// Simple test routes
Route::get('/ping', [TestController::class, 'ping']);
Route::get('/test-assignments-count', [TestController::class, 'assignments']);

Route::prefix('/form-options')->group(function () {
    Route::get('/device', [FormOptionsController::class, 'deviceFormOptions'])->name('form-options.device');
    Route::get('/device-assignment', [FormOptionsController::class, 'deviceAssignmentFormOptions'])->name('form-options.device-assignment');
    Route::get('/device/validation-rules', [FormOptionsController::class, 'deviceValidationRules'])->name('form-options.device.validation-rules');
    Route::get('/device-assignment/validation-rules', [FormOptionsController::class, 'deviceAssignmentValidationRules'])->name('form-options.device-assignment.validation-rules');
    Route::get('/field-options', [FormOptionsController::class, 'getFieldOptions'])->name('form-options.field-options');
});

Route::prefix('/device-assignments')->group(function () {
    Route::get('/', [DeviceAssignmentController::class, 'index'])->name('device-assignments.index');
    Route::get('/{id}', [DeviceAssignmentController::class, 'show'])->name('device-assignments.show');
    Route::post('/', [DeviceAssignmentController::class, 'store'])->name('device-assignments.store');
    Route::patch('/{id}', [DeviceAssignmentController::class, 'update'])->name('device-assignments.update');
    Route::post('/{id}/return', [DeviceAssignmentController::class, 'returnDevice'])->name('device-assignments.return');
});

// Alternative test routes for easier testing
Route::prefix('/test-assignments')->group(function () {
    Route::post('/', [DeviceAssignmentController::class, 'store'])->name('test-assignments.store');
    Route::patch('/{id}', [DeviceAssignmentController::class, 'update'])->name('test-assignments.update');
});