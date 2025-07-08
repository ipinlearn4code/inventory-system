<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Models\Device;
use App\Models\Bribox;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $navigationGroup = 'Inventory Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Device Information')
                    ->schema([
                        Forms\Components\TextInput::make('brand_name')
                            ->label('Brand Name')
                            ->required()
                            ->maxLength(50),
                        
                        Forms\Components\TextInput::make('serial_number')
                            ->label('Serial Number')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),
                        
                        Forms\Components\TextInput::make('asset_code')
                            ->label('Asset Code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        
                        Forms\Components\Select::make('bribox_id')
                            ->label('Bribox Category')
                            ->options(Bribox::all()->mapWithKeys(function ($bribox) {
                                return [$bribox->bribox_id => "{$bribox->bribox_id} - {$bribox->type} ({$bribox->category})"];
                            }))
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\Select::make('condition')
                            ->options([
                                'Baik' => 'Baik',
                                'Rusak' => 'Rusak',
                                'Perlu Pengecekan' => 'Perlu Pengecekan',
                            ])
                            ->required(),
                        
                        Forms\Components\DatePicker::make('dev_date')
                            ->label('Development Date'),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Specifications')
                    ->schema([
                        Forms\Components\TextInput::make('spec1')
                            ->label('Specification 1')
                            ->maxLength(100),
                        
                        Forms\Components\TextInput::make('spec2')
                            ->label('Specification 2 (Numeric)')
                            ->numeric(),
                        
                        Forms\Components\TextInput::make('spec3')
                            ->label('Specification 3')
                            ->maxLength(100),
                        
                        Forms\Components\TextInput::make('spec4')
                            ->label('Specification 4')
                            ->maxLength(100),
                        
                        Forms\Components\TextInput::make('spec5')
                            ->label('Specification 5')
                            ->maxLength(100),
                    ])
                    ->columns(2),
                
                Forms\Components\Section::make('Audit Information')
                    ->schema([
                        Forms\Components\TextInput::make('created_by')
                            ->label('Created By')
                            ->default(auth()->user()?->pn ?? session('authenticated_user.pn'))
                            ->maxLength(7)
                            ->required(),
                        
                        Forms\Components\TextInput::make('updated_by')
                            ->label('Updated By')
                            ->default(auth()->user()?->pn ?? session('authenticated_user.pn'))
                            ->maxLength(7),
                    ])
                    ->columns(2)
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('device_id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('brand_name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('asset_code')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('bribox.type')
                    ->label('Type')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('bribox.category')
                    ->label('Category')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('condition')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Baik' => 'success',
                        'Rusak' => 'danger',
                        'Perlu Pengecekan' => 'warning',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('currentAssignment.user.name')
                    ->label('Assigned To')
                    ->default('Unassigned'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('condition')
                    ->options([
                        'Baik' => 'Baik',
                        'Rusak' => 'Rusak',
                        'Perlu Pengecekan' => 'Perlu Pengecekan',
                    ]),
                
                Tables\Filters\SelectFilter::make('bribox_id')
                    ->label('Category')
                    ->options(Bribox::all()->pluck('category', 'bribox_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }
}
