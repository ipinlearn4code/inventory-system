<?php

use App\Services\QRCodeService;

Route::get('/test-qr', function () {
    try {
        $service = new QRCodeService();
        $qrCode = $service->generateQRCode('TEST001');
        
        return response()->json([
            'success' => true,
            'message' => 'QR code generated successfully',
            'qr_code_length' => strlen($qrCode),
            'qr_code_preview' => substr($qrCode, 0, 100) . '...'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});
