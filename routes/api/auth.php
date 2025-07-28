<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh'])->middleware(['auth:sanctum', 'api.timeout']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum', 'api.timeout']);
Route::post('/push/register', [AuthController::class, 'registerPush'])->middleware(['auth:sanctum', 'api.timeout']);
