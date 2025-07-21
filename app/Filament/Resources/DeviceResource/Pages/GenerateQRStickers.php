<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use App\Models\Device;
use App\Services\QRCodeService;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Illuminate\Support\Collection;

class GenerateQRStickers extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = DeviceResource::class;
    protected static string $view = 'filament.resources.device-resource.pages.generate-qr-stickers';

    public ?array $data = [];
    public Collection $selectedDevices;
    public Collection $previewStickers;
    public bool $showPreview = false;

    public function mount(): void
    {
        $this->selectedDevices = collect();
        $this->previewStickers = collect();
        
        // Check if devices are pre-selected from bulk action
        $preSelectedDevices = request('devices', []);
        if (!empty($preSelectedDevices)) {
            $this->data = ['devices' => $preSelectedDevices];
            // Auto-generate preview for pre-selected devices
            $this->generatePreview();
        } else {
            $this->data = [];
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)
                    ->schema([
                        Select::make('devices')
                            ->label('Select Devices')
                            ->multiple()
                            ->searchable()
                            ->options(function () {
                                return Device::with(['bribox.category', 'currentAssignment.user'])
                                    ->get()
                                    ->mapWithKeys(function ($device) {
                                        $assignedTo = $device->currentAssignment 
                                            ? " (Assigned to: {$device->currentAssignment->user->name})"
                                            : " (Available)";
                                        $category = $device->bribox->category->category_name ?? 'Unknown';
                                        
                                        return [
                                            $device->device_id => "{$device->asset_code} - {$device->brand_name} - {$category}{$assignedTo}"
                                        ];
                                    });
                            })
                            ->placeholder('Search and select devices...')
                            ->helperText('Start typing to search for devices by asset code, brand, or category')
                            ->columnSpan(2),

                        Checkbox::make('include_unassigned')
                            ->label('Include unassigned devices only')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state) {
                                    $unassignedDevices = Device::whereDoesntHave('currentAssignment')->pluck('device_id')->toArray();
                                    $set('devices', $unassignedDevices);
                                }
                            }),

                        Checkbox::make('include_assigned')
                            ->label('Include assigned devices only')
                            ->reactive()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state) {
                                    $assignedDevices = Device::whereHas('currentAssignment')->pluck('device_id')->toArray();
                                    $set('devices', $assignedDevices);
                                }
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generatePreview')
                ->label('Generate Preview')
                ->icon('heroicon-o-eye')
                ->color('primary')
                ->action('generatePreview')
                ->disabled(fn () => empty($this->data['devices'] ?? [])),

            Action::make('printStickers')
                ->label('Print All Stickers')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(function () {
                    if (empty($this->data['devices'] ?? [])) {
                        return null;
                    }
                    return route('qr-code.stickers.bulk', ['device_ids' => $this->data['devices']]);
                })
                ->openUrlInNewTab()
                ->disabled(fn () => empty($this->data['devices'] ?? [])),

            Action::make('clearSelection')
                ->label('Clear Selection')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->action('clearSelection')
                ->visible(fn () => !empty($this->data['devices'] ?? [])),
        ];
    }

    public function generatePreview(): void
    {
        $deviceIds = $this->data['devices'] ?? [];
        
        if (empty($deviceIds)) {
            $this->addError('devices', 'Please select at least one device');
            return;
        }

        $this->selectedDevices = Device::with(['bribox.category', 'currentAssignment.user', 'currentAssignment.branch'])
            ->whereIn('device_id', $deviceIds)
            ->get();

        $qrCodeService = app(QRCodeService::class);
        
        $this->previewStickers = $this->selectedDevices->map(function ($device) use ($qrCodeService) {
            try {
                return [
                    'device' => $device,
                    'qrCodeDataUrl' => $qrCodeService->getQRCodeDataUrl($device->asset_code),
                    'error' => null,
                ];
            } catch (\Exception $e) {
                return [
                    'device' => $device,
                    'qrCodeDataUrl' => null,
                    'error' => $e->getMessage(),
                ];
            }
        });

        $this->showPreview = true;
    }

    public function clearSelection(): void
    {
        $this->data = [];
        $this->selectedDevices = collect();
        $this->previewStickers = collect();
        $this->showPreview = false;
    }

    public function printIndividualSticker($deviceId): void
    {
        $this->redirect(route('qr-code.sticker', $deviceId), navigate: false);
    }

    public function getTitle(): string
    {
        return 'Generate QR Stickers';
    }

    public function getHeading(): string
    {
        return 'Generate QR Stickers';
    }

    public function getSubheading(): ?string
    {
        return 'Generate and print QR code stickers for multiple devices';
    }
}
