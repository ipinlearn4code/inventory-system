<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Device;
use App\Services\QRCodeService;

class QrScanner extends Component
{
    /** @var Device|null */
    public ?Device $scannedDevice = null;
    public bool $isScanning = false;
    public ?string $errorMessage = null;
    public ?string $lastScanTime = null;
    public string $mode = 'full'; // 'full', 'modal', 'inline'
    public bool $autoStart = false;
    public ?string $targetInput = null; // For form integration
    
    // Events to emit when device is scanned
    public array $emitEvents = [];

    protected $listeners = [
        'startScanning' => 'startScanning',
        'stopScanning' => 'stopScanning',
        'resetScanner' => 'resetScanner'
    ];

    public function mount($mode = 'full', $autoStart = false, $targetInput = null, $emitEvents = [])
    {
        $this->mode = $mode;
        $this->autoStart = $autoStart;
        $this->targetInput = $targetInput;
        $this->emitEvents = $emitEvents;
        $this->resetScanner();
    }

    public function render()
    {
        return view('livewire.qr-scanner');
    }

    public function startScanning()
    {
        $this->isScanning = true;
        $this->errorMessage = null;
        $this->dispatch('scanner-start');
    }

    public function stopScanning()
    {
        $this->isScanning = false;
        $this->dispatch('scanner-stop');
    }

    public function resetScanner()
    {
        $this->scannedDevice = null;
        $this->isScanning = false;
        $this->errorMessage = null;
        $this->lastScanTime = null;
        $this->dispatch('scanner-reset');
    }

    #[On('qr-code-scanned')]
    public function handleQRCodeScanned($data)
    {
        $qrData = $data['qrData'] ?? $data ?? null;
        
        if (!$qrData) {
            $this->errorMessage = 'No QR code data received';
            return;
        }

        try {
            $result = app(QRCodeService::class)->processScannedCode($qrData);
            
            if ($result['success']) {
                $this->scannedDevice = $result['device'];
                $this->errorMessage = null;
                $this->lastScanTime = now()->format('H:i:s');
                $this->isScanning = false;

                // Emit custom events if specified
                foreach ($this->emitEvents as $event) {
                    $this->dispatch($event, [
                        'device' => $this->scannedDevice,
                        'assetCode' => $this->scannedDevice['asset_code']
                    ]);
                }

                // For form integration
                if ($this->targetInput) {
                    $this->dispatch('device-selected', [
                        'deviceId' => $this->scannedDevice['device_id'],
                        'assetCode' => $this->scannedDevice['asset_code'],
                        'targetInput' => $this->targetInput
                    ]);
                }

                $this->dispatch('scanner-success', [
                    'device' => $this->scannedDevice,
                    'message' => 'Device scanned successfully!'
                ]);

            } else {
                $this->errorMessage = $result['error'];
                $this->dispatch('scanner-error', ['message' => $result['error']]);
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Error processing QR code: ' . $e->getMessage();
            $this->dispatch('scanner-error', ['message' => $this->errorMessage]);
        }
    }

    public function printSticker()
    {
        if (!$this->scannedDevice) {
            return;
        }

        return redirect()->route('qr-code.sticker', $this->scannedDevice['device_id']);
    }

    public function getDeviceStatusProperty()
    {
        if (!$this->scannedDevice) {
            return null;
        }

        return $this->scannedDevice->currentAssignment ? 'assigned' : 'available';
    }

    public function getDeviceCategoryProperty()
    {
        if (!$this->scannedDevice) {
            return 'N/A';
        }

        return $this->scannedDevice->bribox->category->category_name ?? 'N/A';
    }

    public function getAssignedUserProperty()
    {
        if (!$this->scannedDevice || !$this->scannedDevice->currentAssignment) {
            return null;
        }

        return $this->scannedDevice->currentAssignment->user;
    }

    public function getAssignedBranchProperty()
    {
        if (!$this->scannedDevice || !$this->scannedDevice->currentAssignment) {
            return null;
        }

        return $this->scannedDevice->currentAssignment->branch;
    }
}
