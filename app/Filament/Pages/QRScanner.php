<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Livewire\Attributes\On;
use App\Models\Device;

class QRScanner extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static string $view = 'filament.pages.qr-scanner';
    protected static ?string $navigationGroup = 'Device Management';
    protected static ?string $navigationLabel = 'QR Scanner';
    protected static ?int $navigationSort = 9;

    public ?Device $scannedDevice = null;
    public bool $showScanner = true;
    public ?string $lastScanTime = null;
    public ?string $errorMessage = null;

    public static function canAccess(): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) return false;
        
        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin') || $authModel->hasRole('user'));
    }

    public function mount(): void
    {
        $this->resetScanner();
    }

    public function resetScanner(): void
    {
        $this->scannedDevice = null;
        $this->showScanner = true;
        $this->errorMessage = null;
        $this->lastScanTime = null;
    }

    #[On('qr-code-scanned')]
    public function handleQRCodeScanned($qrData): void
    {
        if (!$qrData) {
            $this->errorMessage = 'No QR code data received';
            return;
        }

        // Extract asset code from QR data
        if (strpos($qrData, 'briven-') === 0) {
            $assetCode = substr($qrData, 7); // Remove 'briven-' prefix
        } else {
            $this->errorMessage = 'Invalid QR code format. Expected format: briven-{asset_code}';
            return;
        }

        // Find the device
        $device = Device::with(['bribox.category', 'currentAssignment.user', 'currentAssignment.branch'])
            ->where('asset_code', $assetCode)
            ->first();

        if (!$device) {
            $this->errorMessage = "Device with asset code '{$assetCode}' not found";
            return;
        }

        $this->scannedDevice = $device;
        $this->errorMessage = null;
        $this->lastScanTime = now()->format('H:i:s');
        $this->showScanner = false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetScanner')
                ->label('Scan Another')
                ->icon('heroicon-o-camera')
                ->color('primary')
                ->action('resetScanner')
                ->visible(fn () => $this->scannedDevice !== null),

            Action::make('printSticker')
                ->label('Print Sticker')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => $this->scannedDevice ? route('qr-code.sticker', $this->scannedDevice->device_id) : null)
                ->openUrlInNewTab()
                ->visible(fn () => $this->scannedDevice !== null),
        ];
    }

    public function getTitle(): string
    {
        return 'QR Code Scanner';
    }

    public function getHeading(): string
    {
        return 'QR Code Scanner';
    }

    public function getSubheading(): ?string
    {
        return 'Scan QR codes to view device information instantly';
    }
}
