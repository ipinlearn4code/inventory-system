<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\FileUploadTestController;
use App\Http\Controllers\SimpleTestController;
use App\Http\Controllers\AssignmentLetterFileController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect('/login');
});

// Authentication routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout.get');

// // File upload testing routes
// Route::get('/test-upload', function () {
//     return view('file-upload-test');
// });
// Route::get('/simple-test', function () {
//     return view('simple-file-test');
// });

// // Test routes without any middleware
// Route::withoutMiddleware(['web'])->group(function () {
//     Route::post('/test-upload/basic', [FileUploadTestController::class, 'testBasicUpload']);
//     Route::post('/test-upload/minio', [FileUploadTestController::class, 'testMinioUpload']);
// });

// QR Code routes
Route::prefix('qr-code')->group(function () {
    Route::get('/generate/{assetCode}', [App\Http\Controllers\QRCodeController::class, 'generateQRCode'])->name('qr-code.generate');
    Route::get('/sticker/{deviceId}', [App\Http\Controllers\QRCodeController::class, 'showSticker'])->name('qr-code.sticker');
    Route::get('/sticker/asset/{assetCode}', [App\Http\Controllers\QRCodeController::class, 'showStickerByAssetCode'])->name('qr-code.sticker.asset');
    Route::get('/stickers/bulk', [App\Http\Controllers\QRCodeController::class, 'showBulkStickers'])->name('qr-code.stickers.bulk');
});

// QR Code Scanner routes
Route::prefix('qr-scanner')->group(function () {
    Route::get('/', [App\Http\Controllers\QRCodeScannerController::class, 'index'])->name('qr-scanner.index');
    Route::post('/scan', [App\Http\Controllers\QRCodeScannerController::class, 'scan'])->name('qr-scanner.scan');
});

// Assignment Letter File routes (protected)
Route::prefix('assignment-letter')->group(function () {
    Route::get('/{letter}/preview', [AssignmentLetterFileController::class, 'preview'])->name('assignment-letter.preview');
    Route::get('/{letter}/download', [AssignmentLetterFileController::class, 'download'])->name('assignment-letter.download');
});

// // Simple test routes
// Route::get('/simple-test-route', [SimpleTestController::class, 'test']);
// Route::post('/simple-upload-test', [SimpleTestController::class, 'upload']);

// Include debug routes
include __DIR__ . '/test-debug.php';
include __DIR__ . '/test-qr.php';

// Debug route to check session data
// Route::get('/debug-session', function () {
//     $user = session('authenticated_user');
//     return response()->json([
//         'session_data' => $user,
//         'role' => $user['role'] ?? 'not set',
//         'expected_colors' => [
//             'superadmin' => 'from-red-600 to-red-700 ring-red-100 dark:ring-red-900',
//             'admin' => 'from-blue-600 to-blue-700 ring-blue-100 dark:ring-blue-900',
//             'user' => 'from-green-600 to-green-700 ring-green-100 dark:ring-green-900',
//         ]
//     ]);
// });
