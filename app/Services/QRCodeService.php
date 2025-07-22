<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
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
        
        try {
            // Try PNG first (requires GD extension)
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            return 'data:image/png;base64,' . base64_encode($result->getString());
        } catch (\Exception $e) {
            // Fallback to SVG (doesn't require GD extension)
            $writer = new SvgWriter();
            $result = $writer->write($qrCode);
            return 'data:image/svg+xml;base64,' . base64_encode($result->getString());
        }
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
        
        try {
            // Try PNG first (requires GD extension)
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            return $result->getString();
        } catch (\Exception $e) {
            // Fallback to SVG (doesn't require GD extension)
            $writer = new SvgWriter();
            $result = $writer->write($qrCode);
            return $result->getString();
        }
    }
    
    /**
     * Check if GD extension is available
     *
     * @return bool
     */
    public function isGdAvailable(): bool
    {
        return extension_loaded('gd');
    }
    
    /**
     * Get the preferred MIME type based on available extensions
     *
     * @return string
     */
    public function getPreferredMimeType(): string
    {
        return $this->isGdAvailable() ? 'image/png' : 'image/svg+xml';
    }

    /**
     * Process scanned QR code data and return device information
     *
     * @param string $qrData
     * @return array
     */
    public function processScannedCode(string $qrData): array
    {
        // Extract asset code from QR data
        if (strpos($qrData, 'briven-') === 0) {
            $assetCode = substr($qrData, 7); // Remove 'briven-' prefix
        } else {
            return [
                'success' => false,
                'error' => 'Invalid QR code format. Expected format: briven-{asset_code}',
                'device' => null
            ];
        }

        // Find the device
        $device = \App\Models\Device::with(['bribox.category', 'currentAssignment.user', 'currentAssignment.branch'])
            ->where('asset_code', $assetCode)
            ->first();

        if (!$device) {
            return [
                'success' => false,
                'error' => "Device with asset code '{$assetCode}' not found",
                'device' => null
            ];
        }

        return [
            'success' => true,
            'error' => null,
            'device' => $device
        ];
    }
}