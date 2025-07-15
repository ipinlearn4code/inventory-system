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

    public static function canViewAny(): bool
    {
        $auth = session('authenticated_user');
        if (!$auth)
            return false;

        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin'));
    }

    public static function canCreate(): bool
    {
        $auth = session('authenticated_user');
        if (!$auth)
            return false;

        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin'));
    }

    public static function canEdit($record): bool
    {
        $auth = session('authenticated_user');
        if (!$auth)
            return false;

        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin'));
    }

    public static function canDelete($record): bool
    {
        $auth = session('authenticated_user');
        if (!$auth)
            return false;

        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin'));
    }

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
                            ->disabled(function ($livewire) {
                                // Only apply in edit mode and if record exists
                                if (!($livewire instanceof \Filament\Resources\Pages\EditRecord) || empty($livewire->record)) {
                                    return false;
                                }
                                $isAssigned = !$livewire->record?->currentAssignment?->returned_date;
                                // Get authenticated user and check role
                                if (session('authenticated_user.role') === 'superadmin') {
                                    return false;
                                }
                                if ($isAssigned) {
                                    return true; // Disable if there is an active assignment
                                }
                                return false;
                            })
                            ->hint(function ($livewire) {
                                // Show hint if disabled due to active assignment and if record exists
                                if (!($livewire instanceof \Filament\Resources\Pages\EditRecord) || empty($livewire->record)) {
                                    return null;
                                }
                                $isAssigned = !$livewire->record?->currentAssignment?->returned_date;
                                if (session('authenticated_user.role') === 'superadmin' && $isAssigned) {
                                    return 'Be careful on this field, you are editing an active assigned device';
                                }
                                if (session('authenticated_user.role') !== 'superadmin' && $isAssigned) {
                                    return 'You cannot edit asset code because of active assignment';
                                }
                                return null;
                            })
                            ->label('Asset Code')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('generate')
                                    ->icon('heroicon-m-arrow-path')
                                    ->tooltip('Generate Asset Code')
                                    ->action(function (Forms\Set $set) {
                                        $assetCode = self::generateAssetCode();
                                        $set('asset_code', $assetCode);
                                    })
                                    ->visible(function ($livewire) {
                                        // Show on create
                                        if ($livewire instanceof \Filament\Resources\Pages\CreateRecord)
                                            return true;
                                        // Show on edit if not assigned
                                        if ($livewire instanceof \Filament\Resources\Pages\EditRecord && ($livewire->record?->currentAssignment === null))
                                            return true;
                                        return false;
                                    })
                            ),

                        Forms\Components\Select::make('bribox_id')
                            ->label('Bribox Category')
                            ->options(Bribox::with('category')->get()->mapWithKeys(function ($bribox) {
                                $categoryName = $bribox->category ? $bribox->category->category_name : 'No Category';
                                return [$bribox->bribox_id => "{$bribox->bribox_id} - {$bribox->type} ({$categoryName})"];
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
                            ->label('Specification 2')
                            ->maxLength(100),

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
                            ->disabled()
                            // ->dehydrated(fn ($state, $context) => $context === 'create'),
                            ->dehydrated(),

                        Forms\Components\TextInput::make('updated_by')
                            ->label('Updated By')
                            ->default(auth()->user()?->pn ?? session('authenticated_user.pn'))
                            ->maxLength(7)
                            ->disabled()
                            ->dehydrated(),
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
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('brand_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('asset_code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('bribox.type')
                    ->label('Type')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('bribox.category.category_name')
                    ->label('Category')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('condition')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Baik' => 'success',
                        'Rusak' => 'danger',
                        'Perlu Pengecekan' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('currentAssignment.user.name')
                    ->label('Assigned To')
                    ->default('Unassigned')
                    ->toggleable(),

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
                Tables\Filters\SelectFilter::make('bribox_category')
                    ->label('Category')
                    ->options(\App\Models\BriboxesCategory::all()->pluck('category_name', 'bribox_category_id'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $value): Builder => $query->whereHas(
                                'bribox',
                                fn(Builder $query): Builder => $query->where('bribox_category_id', $value)
                            )
                        );
                    }),
                Tables\Filters\SelectFilter::make('bribox.type')
                    ->label('Type')
                    ->options(Bribox::all()->pluck('type', 'bribox_id')),
            ])

            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->slideOver()
                        ->tooltip('View device details'),
                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit device information'),
                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Delete this device'),
                ])
                ->iconButton()
                ->icon('heroicon-o-ellipsis-horizontal')
                ->tooltip('Device Actions'),
            ])

            ->recordUrl(null)

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),

            ])
        ;
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

    /**
     * Generate a unique 15-character alphanumeric asset code
     */
    public static function generateAssetCode(): string
    {
        do {
            // Generate a 15-character alphanumeric code
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $assetCode = '';

            for ($i = 0; $i < 15; $i++) {
                $assetCode .= $characters[rand(0, strlen($characters) - 1)];
            }

            // Check if this code already exists
            $exists = Device::where('asset_code', $assetCode)->exists();
        } while ($exists);

        return $assetCode;
    }
}
