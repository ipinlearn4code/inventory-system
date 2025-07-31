<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\DeviceAssignmentController;



Route::prefix('/device-assignments')->group(function () {
    Route::get('/', [DeviceAssignmentController::class, 'index'])->name('device-assignments.index');
    Route::get('/{id}', [DeviceAssignmentController::class, 'show'])->name('device-assignments.show');
    Route::post('/', [DeviceAssignmentController::class, 'store'])->name('device-assignments.store');
    Route::put('/{id}', [DeviceAssignmentController::class, 'update'])->name('device-assignments.update');
    Route::post('/{id}/return', [DeviceAssignmentController::class, 'returnDevice'])->name('device-assignments.return');
});