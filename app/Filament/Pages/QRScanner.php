<?php

namespace App\Filament\Pages;

use App\Filament\Forms\Components\QrCodeScanner;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Fadlee\FilamentQrCodeField\Forms\Components\QrCodeInput;
use App\Models\Device;

class QRScanner extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static string $view = 'filament.pages.qr-scanner';
    protected static ?string $navigationGroup = 'Device Management';
    protected static ?string $navigationLabel = 'QR Scanner';
    protected static ?int $navigationSort = 9;

    public ?array $data = [];
    public ?Device $scannedDevice = null;

    public static function canAccess(): bool
    {
        $auth = session('authenticated_user');
        if (!$auth)
            return false;

        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin') || $authModel->hasRole('user'));
    }

    public function mount(): void
    {
        $this->data = [];
    }

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                Section::make('QR Code Scanner')
                    ->description('Scan a QR code to retrieve device information')
                    ->schema([
                        QrCodeScanner::make('scanned_code')
                            ->label('')
                            ->asButton('📱 Scan QR Code', 'primary', 'md')
                            ->withIcon('heroicon-o-qr-code')
                            ->live()
                            ->afterStateUpdated(function (string $state = null) {
                                if ($state) {
                                    // dd($state);
                                    $this->processScannedCode($state);
                                }
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    public function processScannedCode(string $qrData): void
    {
        // Extract asset code from QR data
        if (strpos($qrData, 'briven-') === 0) {
            $assetCode = substr($qrData, 7); // Remove 'briven-' prefix
        } else {
            $assetCode = $qrData; // Use as-is if no prefix
        }

        // Find the device
        $device = Device::with(['bribox.category', 'currentAssignment.user', 'currentAssignment.branch'])
            ->where('asset_code', $assetCode)
            ->first();

        if ($device) {
            $this->scannedDevice = $device;

            // Show success notification
            \Filament\Notifications\Notification::make()
                ->title('Device Found!')
                ->body("Successfully scanned device: {$device['asset_code']}")
                ->success()
                ->send();
        } else {
            $this->scannedDevice = null;

            // Show error notification
            \Filament\Notifications\Notification::make()
                ->title('Device Not Found')
                ->body("No device found with asset code: {$assetCode}")
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetScanner')
                ->label('Clear Results')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(function () {
                    $this->scannedDevice = null;
                    $this->data['scanned_code'] = null;
                })
                ->visible(fn() => $this->scannedDevice !== null),

            Action::make('printSticker')
                ->label('Print Sticker')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn() => $this->scannedDevice ? route('qr-code.sticker', $this->scannedDevice['device_id']) : null)
                ->openUrlInNewTab()
                ->visible(fn() => $this->scannedDevice !== null),
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
