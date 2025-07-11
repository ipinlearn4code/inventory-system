<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DevicesNeedAttentionWidget extends BaseWidget
{
    protected static ?string $heading = '🚨 Perangkat Memerlukan Perhatian';
    protected static ?int $sort = 6;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Device::query()
                    ->whereIn('condition', ['Rusak', 'Perlu Pengecekan'])
                    ->with(['currentAssignment.user', 'currentAssignment.branch'])
                    ->limit(15)
            )
            ->columns([
                Tables\Columns\TextColumn::make('serial_number')
                    ->label('Serial Number')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),
                    
                Tables\Columns\TextColumn::make('brand_name')
                    ->label('Brand')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('currentAssignment.branch.unit_name')
                    ->label('Lokasi')
                    ->default('Tidak Ditugaskan')
                    ->icon('heroicon-m-map-pin'),
                    
                Tables\Columns\TextColumn::make('currentAssignment.user.name')
                    ->label('Pengguna')
                    ->default('Tidak Ditugaskan')
                    ->icon('heroicon-m-user'),
                    
                Tables\Columns\TextColumn::make('condition')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Baik' => 'success',
                        'Rusak' => 'danger',
                        'Perlu Pengecekan' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Rusak' => 'heroicon-m-x-circle',
                        'Perlu Pengecekan' => 'heroicon-m-exclamation-triangle',
                        default => 'heroicon-m-check-circle',
                    }),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Update')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Device $record): string => \App\Filament\Resources\DeviceResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false)
            ->headerActions([
                Tables\Actions\Action::make('viewAll')
                    ->label('Lihat Semua')
                    ->icon('heroicon-m-arrow-right')
                    ->url(\App\Filament\Resources\DeviceResource::getUrl('index'))
                    ->openUrlInNewTab(),
            ]);
    }
}
