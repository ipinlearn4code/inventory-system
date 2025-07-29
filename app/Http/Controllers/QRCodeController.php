<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class QRCodeController extends Controller
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Generate QR code PNG image for asset code
     *
     * @param string $assetCode
     * @return Response
     */
    public function generateQRCode(string $assetCode): Response
    {
        try {
            $service = $this->qrCodeService;
            $mimeType = $service->getPreferredMimeType();
            $data = $service->getQRCodePngData($assetCode);
            
            $extension = $mimeType === 'image/png' ? 'png' : 'svg';
            
            return response($data, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => "inline; filename=\"qr_code_{$assetCode}.{$extension}\"",
                'Cache-Control' => 'public, max-age=3600',
            ]);
        } catch (\Exception $e) {
            return response('Error generating QR code: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Show sticker preview for device
     *
     * @param string $deviceId
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function showSticker(string $deviceId)
    {
        try {
            $device = Device::with(['bribox.category', 'currentAssignment.user', 'currentAssignment.branch'])
                ->findOrFail($deviceId);
            
            $qrCodeDataUrl = $this->qrCodeService->getQRCodeDataUrl($device->asset_code);
            
            return view('sticker.preview', compact('device', 'qrCodeDataUrl'));
        } catch (\Exception $e) {
            return response('Device not found', 404);
        }
    }

    /**
     * Show sticker preview by asset code
     *
     * @param string $assetCode
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function showStickerByAssetCode(string $assetCode)
    {
        try {
            $device = Device::with(['bribox.category', 'currentAssignment.user', 'currentAssignment.branch'])
                ->where('asset_code', $assetCode)
                ->firstOrFail();
            
            $qrCodeDataUrl = $this->qrCodeService->getQRCodeDataUrl($device->asset_code);
            
            return view('sticker.preview', compact('device', 'qrCodeDataUrl'));
        } catch (\Exception $e) {
            return response('Device not found', 404);
        }
    }

    /**
     * Generate bulk stickers for multiple devices
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\Response
     */
    public function showBulkStickers(Request $request)
    {
        $deviceIds = $request->input('device_ids', []);
        
        if (empty($deviceIds)) {
            return response('No devices selected', 400);
        }
        
        try {
            $devices = Device::with(['bribox.category', 'currentAssignment.user', 'currentAssignment.branch'])
                ->whereIn('device_id', $deviceIds)
                ->get();
            
            if ($devices->isEmpty()) {
                return response('No devices found with the provided IDs', 404);
            }
            
            $stickers = [];
            foreach ($devices as $device) {
                try {
                    $qrCodeDataUrl = $this->qrCodeService->getQRCodeDataUrl($device->asset_code);
                    $stickers[] = [
                        'device' => $device,
                        'qrCodeDataUrl' => $qrCodeDataUrl,
                    ];
                } catch (\Exception $qrError) {
                    // Log the error but continue with other devices
                    \Log::error("Failed to generate QR code for device {$device->device_id}: " . $qrError->getMessage());
                    
                    // Add a fallback entry
                    $stickers[] = [
                        'device' => $device,
                        'qrCodeDataUrl' => 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300"><text x="150" y="150" text-anchor="middle" fill="red">QR Error</text></svg>'),
                    ];
                }
            }
            
            return view('sticker.bulk-preview', compact('stickers'));
        } catch (\Exception $e) {
            \Log::error("Bulk stickers generation failed: " . $e->getMessage());
            return response('Error generating stickers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generate PDF stickers for multiple devices
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generateStickersPDF(Request $request)
    {
        $deviceIds = $request->input('device_ids', []);
        
        if (empty($deviceIds)) {
            return response('No devices selected', 400);
        }
        
        try {
            $devices = Device::with(['bribox.category', 'currentAssignment.user', 'currentAssignment.branch'])
                ->whereIn('device_id', $deviceIds)
                ->get();
            
            if ($devices->isEmpty()) {
                return response('No devices found with the provided IDs', 404);
            }
            
            $stickers = [];
            foreach ($devices as $device) {
                try {
                    $qrCodeDataUrl = $this->qrCodeService->getQRCodeDataUrl($device->asset_code);
                    $stickers[] = [
                        'device' => $device,
                        'qrCodeDataUrl' => $qrCodeDataUrl,
                        'error' => null,
                    ];
                } catch (\Exception $qrError) {
                    // Log the error but continue with other devices
                    \Log::error("Failed to generate QR code for device {$device->device_id}: " . $qrError->getMessage());
                    
                    // Add an error entry
                    $stickers[] = [
                        'device' => $device,
                        'qrCodeDataUrl' => null,
                        'error' => $qrError->getMessage(),
                    ];
                }
            }
            
            // Generate PDF
            $pdf = Pdf::loadView('sticker.pdf-template', compact('stickers'));
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'dpi' => 150,
                'defaultFont' => 'Arial'
            ]);
            
            $filename = 'qr-stickers-' . date('Y-m-d-H-i-s') . '.pdf';
            
            return $pdf->stream($filename);
        } catch (\Exception $e) {
            \Log::error("PDF stickers generation failed: " . $e->getMessage());
            return response('Error generating PDF stickers: ' . $e->getMessage(), 500);
        }
    }
}
