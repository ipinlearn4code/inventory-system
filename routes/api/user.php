<?php

use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/home/summary', [UserController::class, 'homeSummary'])->middleware('api.cache');
Route::get('/devices', [UserController::class, 'devices'])->middleware('api.cache');
Route::get('/devices/{id}', [UserController::class, 'deviceDetails']);
Route::post('/devices/{id}/report-issue', [UserController::class, 'reportIssue']);
Route::get('/profile', [UserController::class, 'profile'])->middleware('api.cache');
Route::get('/history', [UserController::class, 'history']);
