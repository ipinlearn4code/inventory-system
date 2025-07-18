<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;

class QRCodeService
{
    /**
     * Generate QR code for asset code with briven prefix
     *
     * @param string $assetCode
     * @return string Base64 encoded QR code image
     */
    public function generateQRCode(string $assetCode): string
    {
        $qrData = "briven-{$assetCode}";
        
        $qrCode = new QrCode(
            data: $qrData,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 300,
            margin: 10
        );
        
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        return 'data:image/png;base64,' . base64_encode($result->getString());
    }
    
    /**
     * Generate QR code data URL for asset code
     *
     * @param string $assetCode
     * @return string
     */
    public function getQRCodeDataUrl(string $assetCode): string
    {
        return $this->generateQRCode($assetCode);
    }
    
    /**
     * Generate QR code PNG binary data
     *
     * @param string $assetCode
     * @return string
     */
    public function getQRCodePngData(string $assetCode): string
    {
        $qrData = "briven-{$assetCode}";
        
        $qrCode = new QrCode(
            data: $qrData,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 300,
            margin: 10
        );
        
        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        
        return $result->getString();
    }
}
