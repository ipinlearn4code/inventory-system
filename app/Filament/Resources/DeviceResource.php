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
    protected static ?string $navigationGroup = 'Device Management';
    protected static ?string $navigationLabel = 'Devices';
    protected static ?int $navigationSort = 4;

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
                        Forms\Components\Select::make('brand')
                            ->label('Brand')
                            ->required()
                            ->options(Device::distinct()->pluck('brand', 'brand')->toArray())
                            ->searchable()
                            ->searchPrompt('Search or click + to add')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('new_brand')
                                    ->label('New Brand Name')
                                    ->maxLength(50)
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return $data['new_brand'];
                            }),

                        Forms\Components\Select::make('brand_name')
                            ->label('Brand Name (Model/Series)')
                            ->required()
                            ->options(Device::distinct()->pluck('brand_name', 'brand_name')->toArray())
                            ->searchable()
                            ->searchPrompt('Search or click + to add')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('new_brand_name')
                                    ->label('New Brand Name (Model/Series)')
                                    ->maxLength(50)
                                    ->required(),
                            ])
                            ->helperText('e.g., OptiPlex 7090, ThinkPad X1 Carbon')
                            ->createOptionUsing(function (array $data) {
                                return $data['new_brand_name'];
                            }),

                        Forms\Components\TextInput::make('serial_number')
                            ->label('Serial Number')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->autocomplete(null),

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

                        Forms\Components\Select::make('status')
                            ->options([
                                'Digunakan' => 'Digunakan',
                                'Tidak Digunakan' => 'Tidak Digunakan',
                                'Cadangan' => 'Cadangan',
                            ])
                            ->default('Tidak Digunakan')
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

                Tables\Columns\TextColumn::make('brand')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('brand_name')
                    ->label('Model/Series')
                    ->searchable()
                    ->sortable(),
                // ->toggleable(),

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

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Digunakan' => 'success',
                        'Tidak Digunakan' => 'gray',
                        'Cadangan' => 'warning',
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
                Tables\Filters\SelectFilter::make('brand')
                    ->multiple()
                    ->options(Device::distinct()->pluck('brand', 'brand')->toArray()),
                Tables\Filters\SelectFilter::make('condition')
                    ->multiple()
                    ->options([
                        'Baik' => 'Baik',
                        'Rusak' => 'Rusak',
                        'Perlu Pengecekan' => 'Perlu Pengecekan',
                    ]),
                Tables\Filters\SelectFilter::make('bribox_category')
                    // ->label('Category')
                    // ->multiple()
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
                        ->tooltip('View device details')
                        ->form(function (Device $record) {
                            $qrCodeService = app(\App\Services\QRCodeService::class);
                            $qrCodeDataUrl = null;
                            try {
                                $qrCodeDataUrl = $qrCodeService->getQRCodeDataUrl($record->asset_code);
                            } catch (\Exception $e) {
                                // Handle QR code generation error
                            }

                            return [
                                Forms\Components\Section::make('Device Information')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('device_id')
                                                    ->label('Device ID')
                                                    ->disabled(),
                                                Forms\Components\TextInput::make('asset_code')
                                                    ->label('Asset Code')
                                                    ->disabled()
                                                    ->suffixAction(
                                                        Forms\Components\Actions\Action::make('copy')
                                                            ->icon('heroicon-m-clipboard')
                                                            ->tooltip('Copy Asset Code')
                                                    ),
                                                Forms\Components\TextInput::make('brand')
                                                    ->label('Brand')
                                                    ->disabled(),
                                                Forms\Components\TextInput::make('brand_name')
                                                    ->label('Model/Series')
                                                    ->disabled(),
                                                Forms\Components\TextInput::make('serial_number')
                                                    ->label('Serial Number')
                                                    ->disabled(),
                                                Forms\Components\TextInput::make('bribox.type')
                                                    ->label('Type')
                                                    ->disabled(),
                                                Forms\Components\TextInput::make('bribox.category.category_name')
                                                    ->label('Category')
                                                    ->disabled(),
                                                Forms\Components\TextInput::make('condition')
                                                    ->label('Condition')
                                                    ->disabled(),
                                                Forms\Components\DatePicker::make('dev_date')
                                                    ->label('Development Date')
                                                    ->disabled(),
                                            ]),
                                    ]),

                                Forms\Components\Section::make('QR Code')
                                    ->description('QR code for this device containing: briven-' . $record->asset_code)
                                    ->schema([
                                        Forms\Components\Grid::make(1)
                                            ->schema([
                                                Forms\Components\ViewField::make('qr_code_preview')
                                                    ->label('')
                                                    ->view('filament.components.qr-code-preview', [
                                                        'qrCodeDataUrl' => $qrCodeDataUrl,
                                                        'assetCode' => $record->asset_code,
                                                        'deviceId' => $record->device_id,
                                                    ])
                                                    ->extraAttributes(['style' => 'text-align: center;']),
                                            ]),
                                    ]),

                                Forms\Components\Section::make('Specifications')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('spec1')
                                                    ->label('Specification 1')
                                                    ->disabled(),
                                                Forms\Components\TextInput::make('spec2')
                                                    ->label('Specification 2')
                                                    ->disabled(),
                                                Forms\Components\TextInput::make('spec3')
                                                    ->label('Specification 3')
                                                    ->disabled(),
                                                Forms\Components\TextInput::make('spec4')
                                                    ->label('Specification 4')
                                                    ->disabled(),
                                                Forms\Components\TextInput::make('spec5')
                                                    ->label('Specification 5')
                                                    ->disabled(),
                                            ]),
                                    ])
                                    ->collapsible(),

                                Forms\Components\Section::make('Assignment Information')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('currentAssignment.user.name')
                                                    ->label('Assigned To')
                                                    ->disabled(),
                                                Forms\Components\TextInput::make('currentAssignment.branch.branch_name')
                                                    ->label('Branch')
                                                    ->disabled(),
                                                Forms\Components\DatePicker::make('currentAssignment.assigned_date')
                                                    ->label('Assignment Date')
                                                    ->disabled(),
                                                Forms\Components\Textarea::make('currentAssignment.notes')
                                                    ->label('Assignment Notes')
                                                    ->disabled(),
                                            ]),
                                    ])
                                    ->visible(fn() => $record->currentAssignment !== null),

                                Forms\Components\Section::make('Audit Trail')
                                    ->schema([
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('created_by')
                                                    ->label('Created By')
                                                    ->disabled(),
                                                Forms\Components\DateTimePicker::make('created_at')
                                                    ->label('Created At')
                                                    ->disabled(),
                                                Forms\Components\TextInput::make('updated_by')
                                                    ->label('Updated By')
                                                    ->disabled(),
                                                Forms\Components\DateTimePicker::make('updated_at')
                                                    ->label('Updated At')
                                                    ->disabled(),
                                            ]),
                                    ])
                                    ->collapsible(),
                            ];
                        }),
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
                    Tables\Actions\BulkAction::make('printQRStickers')
                        ->label('Print QR Stickers')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->action(function ($records) {
                            // Validate that records exist and are not empty
                            if (!$records || $records->isEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('No devices selected')
                                    ->body('Please select at least one device to generate QR stickers.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $deviceIds = $records->pluck('device_id')->toArray();

                            // Redirect to the nested page within the admin panel
                            return redirect()->to('/admin/devices/generate-qr?' . http_build_query(['devices' => $deviceIds]));
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Generate QR Stickers')
                        ->modalDescription('This will take you to the QR sticker generation page with the selected devices.')
                        ->modalSubmitActionLabel('Continue')
                        ->deselectRecordsAfterCompletion()
                        ->tooltip('Generate printable QR stickers for selected devices'),
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
            'generate-qr' => Pages\GenerateQRStickers::route('/generate-qr'),
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
