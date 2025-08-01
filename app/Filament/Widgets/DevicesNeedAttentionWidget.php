<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DevicesNeedAttentionWidget extends BaseWidget
{
    protected static ?string $heading = '🚨 Devices Needing Attention';    
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = [
        'default' => 'full',  // Full width on mobile
        'sm' => 'full',       // Full width on small screens  
        'md' => 'full',       // Full width on medium screens (important system status)
        'lg' => 2,            // 2 out of 4 columns on large screens
        'xl' => 2,            // 2 out of 4 columns on XL screens (maintain consistency)
        '2xl' => 4,           // 3 out of 6 columns on ultra-wide screens
    ];

    protected static ?string $maxHeight = '100px';

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
                    
                Tables\Columns\TextColumn::make('brand')
                    ->label('Brand')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('brand_name')
                    ->label('Model/Series')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('currentAssignment.branch.unit_name')
                    ->label('Location')
                    ->default('Not Assigned')
                    ->icon('heroicon-m-map-pin'),
                    
                Tables\Columns\TextColumn::make('currentAssignment.user.name')
                    ->label('User')
                    ->default('Not Assigned')
                    ->icon('heroicon-m-user'),
                    
                Tables\Columns\TextColumn::make('condition')
                    ->label('Condition')
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
                    ->label('Last Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->url(fn (Device $record): string => \App\Filament\Resources\DeviceResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(),
            ])
            ->paginated(false)
            ->headerActions([
                Tables\Actions\Action::make('viewAll')
                    ->label('View All')
                    ->icon('heroicon-m-arrow-right')
                    ->url(\App\Filament\Resources\DeviceResource::getUrl('index').'?tableFilters[condition][value]=Rusak')
                    ->openUrlInNewTab(false),

            ]);
    }
}
