<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class QRCodeScannerController extends Controller
{
    /**
     * Show the QR code scanner page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('qr-scanner.index');
    }

    /**
     * Handle QR code scan result
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function scan(Request $request)
    {
        $qrData = $request->input('qr_data');
        
        if (!$qrData) {
            return response()->json(['error' => 'No QR data provided'], 400);
        }
        
        // Extract asset code from QR data
        if (strpos($qrData, 'briven-') === 0) {
            $assetCode = substr($qrData, 7); // Remove 'briven-' prefix
        } else {
            return response()->json(['error' => 'Invalid QR code format'], 400);
        }
        
        // Find the device
        $device = Device::with(['bribox.category', 'currentAssignment.user', 'currentAssignment.branch'])
            ->where('asset_code', $assetCode)
            ->first();
        
        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }
        
        // Return device information
        return response()->json([
            'success' => true,
            'device' => [
                'asset_code' => $device->asset_code,
                'brand' => $device->brand,
                'brand_name' => $device->brand_name,
                'serial_number' => $device->serial_number,
                'condition' => $device->condition,
                'category' => $device->bribox->category->category_name ?? 'Unknown',
                'type' => $device->bribox->type ?? 'Unknown',
                'specs' => [
                    $device->spec1,
                    $device->spec2,
                    $device->spec3,
                ],
                'assignment' => $device->currentAssignment ? [
                    'user_name' => $device->currentAssignment->user->name,
                    'branch_name' => $device->currentAssignment->branch->unit_name,
                    'assigned_date' => $device->currentAssignment->assigned_date,
                ] : null,
                'status' => $device->status,
            ],
        ]);
    }
}
