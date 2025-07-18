<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
            $pngData = $this->qrCodeService->getQRCodePngData($assetCode);
            
            return response($pngData, 200, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => "inline; filename=\"qr_code_{$assetCode}.png\"",
                'Cache-Control' => 'public, max-age=3600',
            ]);
        } catch (\Exception $e) {
            return response('Error generating QR code', 500);
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
            
            $stickers = [];
            foreach ($devices as $device) {
                $qrCodeDataUrl = $this->qrCodeService->getQRCodeDataUrl($device->asset_code);
                $stickers[] = [
                    'device' => $device,
                    'qrCodeDataUrl' => $qrCodeDataUrl,
                ];
            }
            
            return view('sticker.bulk-preview', compact('stickers'));
        } catch (\Exception $e) {
            return response('Error generating stickers', 500);
        }
    }
}
