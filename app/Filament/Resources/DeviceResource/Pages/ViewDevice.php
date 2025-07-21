<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use App\Services\QRCodeService;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;

class ViewDevice extends ViewRecord
{
    protected static string $resource = DeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('printQRSticker')
                ->label('Print QR Sticker')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn () => route('qr-code.sticker', $this->record->device_id))
                ->openUrlInNewTab(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Device Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('device_id')
                                    ->label('Device ID'),
                                TextEntry::make('asset_code')
                                    ->label('Asset Code')
                                    ->copyable()
                                    ->copyMessage('Asset code copied!')
                                    ->copyMessageDuration(1500),
                                TextEntry::make('brand_name')
                                    ->label('Brand Name'),
                                TextEntry::make('serial_number')
                                    ->label('Serial Number')
                                    ->copyable(),
                                TextEntry::make('bribox.type')
                                    ->label('Type'),
                                TextEntry::make('bribox.category.category_name')
                                    ->label('Category'),
                                TextEntry::make('condition')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Baik' => 'success',
                                        'Rusak' => 'danger',
                                        'Perlu Pengecekan' => 'warning',
                                        default => 'gray',
                                    }),
                                TextEntry::make('dev_date')
                                    ->label('Development Date')
                                    ->date(),
                            ]),
                    ]),

                Section::make('QR Code Sticker')
                    ->description('QR code for this device containing: briven-' . $this->record->asset_code)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ImageEntry::make('qr_code')
                                    ->label('')
                                    ->state(function () {
                                        try {
                                            $qrCodeService = app(QRCodeService::class);
                                            return $qrCodeService->getQRCodeDataUrl($this->record->asset_code);
                                        } catch (\Exception $e) {
                                            return null;
                                        }
                                    })
                                    ->height(200)
                                    ->width(200),
                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('qr_data')
                                            ->label('QR Code Data')
                                            ->state('briven-' . $this->record->asset_code)
                                            ->copyable()
                                            ->copyMessage('QR data copied!')
                                            ->icon('heroicon-o-qr-code'),
                                        TextEntry::make('qr_instructions')
                                            ->label('Instructions')
                                            ->state('Use any QR scanner app to scan this code and view device information')
                                            ->color('gray'),
                                    ]),
                            ]),
                    ]),

                Section::make('Specifications')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('spec1')
                                    ->label('Specification 1')
                                    ->placeholder('Not specified'),
                                TextEntry::make('spec2')
                                    ->label('Specification 2')
                                    ->placeholder('Not specified'),
                                TextEntry::make('spec3')
                                    ->label('Specification 3')
                                    ->placeholder('Not specified'),
                                TextEntry::make('spec4')
                                    ->label('Specification 4')
                                    ->placeholder('Not specified'),
                                TextEntry::make('spec5')
                                    ->label('Specification 5')
                                    ->placeholder('Not specified'),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Assignment Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('currentAssignment.user.name')
                                    ->label('Assigned To')
                                    ->placeholder('Not assigned')
                                    ->icon('heroicon-o-user'),
                                TextEntry::make('currentAssignment.branch.branch_name')
                                    ->label('Branch')
                                    ->placeholder('No branch')
                                    ->icon('heroicon-o-building-office'),
                                TextEntry::make('currentAssignment.assigned_date')
                                    ->label('Assignment Date')
                                    ->date()
                                    ->placeholder('Not assigned'),
                                TextEntry::make('currentAssignment.notes')
                                    ->label('Assignment Notes')
                                    ->placeholder('No notes'),
                            ]),
                    ])
                    ->visible(fn () => $this->record->currentAssignment !== null),

                Section::make('Audit Trail')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_by')
                                    ->label('Created By'),
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime(),
                                TextEntry::make('updated_by')
                                    ->label('Updated By'),
                                TextEntry::make('updated_at')
                                    ->label('Updated At')
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
