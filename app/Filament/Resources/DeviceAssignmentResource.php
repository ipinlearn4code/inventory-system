<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceAssignmentResource\Pages;
use App\Models\DeviceAssignment;
use App\Models\Device;
use App\Models\User;
use App\Models\Branch;
use Fadlee\FilamentQrCodeField\Forms\Components\QrCodeInput;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use App\Filament\Forms\Components\QrCodeScanner;
use App\Traits\HasInventoryLogging;

class DeviceAssignmentResource extends Resource
{
    use HasInventoryLogging;
    protected static ?string $model = DeviceAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Device Management';
    protected static ?string $navigationLabel = 'Device Assignments';
    protected static ?int $navigationSort = 5;
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
        return $authModel && $authModel->hasRole('superadmin');
    }

    public static function form(Form $form): Form
    {
        // dd($form->getRecord());
        return $form
            // ->relationship('device', 'asset_code')
            ->schema([
                // QR Scanner for Device Selection - Custom Button
                QrCodeScanner::make('qr_scanner')
                    ->label('Scan Device QR Code')
                    ->asButton('📱 Scan QR Code', 'primary', 'md')
                    ->withIcon('heroicon-o-qr-code')
                    ->live()
                    ->visibleOn(['create', 'edit'])
                    ->afterStateUpdated(function (string $state = null, Forms\Set $set) {
                        if ($state) {
                            // Extract asset code from QR data
                            if (strpos($state, 'briven-') === 0) {
                                $assetCode = substr($state, 7); // Remove 'briven-' prefix
                            } else {
                                $assetCode = $state; // Use as-is if no prefix
                            }

                            // Find the device
                            $device = Device::available()
                                ->where('asset_code', $assetCode)
                                ->first();

                            if ($device) {
                                $set('device_id', $device['device_id']);

                                // Show success notification
                                \Filament\Notifications\Notification::make()
                                    ->title('Device Found!')
                                    ->body("Successfully scanned device: {$device['asset_code']}")
                                    ->success()
                                    ->send();
                            } else {
                                // Show error notification
                                \Filament\Notifications\Notification::make()
                                    ->title('Device Not Found')
                                    ->body("No available device found with asset code: {$assetCode}")
                                    ->danger()
                                    ->send();
                            }
                        }
                    })
                    ->columnSpanFull(),

                Select::make('device_id')
                    ->label('Device')
                    ->relationship(
                        name: 'device',
                        titleAttribute: 'asset_code_with_type' // pakai accessor kamu
                    )
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->asset_code_with_type)
                    ->options(function () {
                        return Device::available()->get()->pluck('asset_code_with_type', 'device_id');
                    })
                    ->required()
                    ->searchable()
                    ->lazy()
                    ->helperText('Only available devices (not currently assigned) are shown. Use QR scanner above for quick selection.'),
                Select::make('user_id')
                    ->label('Assign to User')
                    ->options(function () {
                        return User::with('department')
                            ->get()
                            ->mapWithKeys(function ($user) {
                                $deptName = isset($user->department) ? $user->department->name : 'No Dept';
                                return [$user->user_id => $user->pn . ' - ' . $user->name . ' (' . $deptName . ')'];
                            });
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        if ($state) {
                            $user = User::find($state);
                            if ($user && $user->branch_id) {
                                $set('branch_id', $user->branch_id);

                                // Show notification that branch was auto-filled
                                \Filament\Notifications\Notification::make()
                                    ->title('Branch Auto-filled')
                                    ->body("Branch automatically set based on user's branch")
                                    ->success()
                                    ->send();
                            }
                        }
                    }),

                Select::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::with('mainBranch')->get()->mapWithKeys(function ($branch) {
                        return [$branch->branch_id => $branch->unit_name . ' (' . $branch->mainBranch->main_branch_name . ')'];
                    }))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('This will be auto-filled when you select a user'),

                DatePicker::make('assigned_date')
                    ->label('Assignment Date')
                    ->required()
                    ->default(now())
                    ->maxDate(now()),

                DatePicker::make('returned_date')
                    ->label('Return Date')
                    ->helperText('Leave empty for active assignments'),

                Textarea::make('notes')
                    ->label('Notes')
                    ->maxLength(500)
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('assignment_id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('device.asset_code')
                    ->label('Device Asset Code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('device.brand')
                    ->label('Brand')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('device.brand_name')
                    ->label('Model/Series')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Assigned to')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.pn')
                    ->label('PN')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('branch.unit_name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('branch.mainBranch.main_branch_name')
                    ->label('Main Branch')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('assigned_date')
                    ->label('Assigned Date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('returned_date')
                    ->label('Return Date')
                    ->date('d/m/Y')
                    ->placeholder('Active')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('device.status')
                    ->label('Device Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Digunakan' => 'success',
                        'Tidak Digunakan' => 'warning',
                        'Cadangan' => 'info',
                        default => 'gray',
                    })
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->getStateUsing(fn($record) => is_null($record->returned_date))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('device.status')
                    ->label('Device Status')
                    ->relationship('device', 'status')
                    ->options([
                        'Digunakan' => 'Digunakan (In Use)',
                        'Tidak Digunakan' => 'Tidak Digunakan (Not Used)',
                        'Cadangan' => 'Cadangan (Backup)',
                    ]),

                Tables\Filters\Filter::make('active_assignments')
                    ->label('Active Assignments Only')
                    ->query(fn(Builder $query): Builder => $query->whereNull('returned_date'))
                    ->default(),

                Tables\Filters\Filter::make('returned_assignments')
                    ->label('Returned Assignments Only')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('returned_date')),

                Tables\Filters\SelectFilter::make('branch_code')
                    ->label('Branch')
                    ->options(Branch::all()->pluck('unit_name', 'branch_code')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->slideOver()
                        ->tooltip('View assignment details'),
                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit assignment information'),

                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Delete this assignment'),
                ])
                    ->iconButton()
                    ->icon('heroicon-o-ellipsis-horizontal')
                    ->tooltip('Assignment Actions'),

                Tables\Actions\Action::make('return_device')
                    ->label('Return Device')
                    ->icon('heroicon-o-arrow-left-on-rectangle')
                    ->color('warning')
                    ->tooltip('Mark device as returned')
                    ->visible(fn($record) => is_null($record->returned_date))
                    ->requiresConfirmation()
                    ->modalHeading('Return Device')
                    ->modalDescription('Are you sure you want to mark this device as returned?')
                    ->action(function ($record) {
                        $record->update([
                            'returned_date' => now()->toDateString(),
                            'updated_at' => now(),
                            'updated_by' => session('authenticated_user')['pn'] ?? 'system',
                        ]);
                    })
                    ->successNotificationTitle('Device returned successfully'),
            ])
            ->recordUrl(null)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('assigned_date', 'desc');
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
            'index' => Pages\ListDeviceAssignments::route('/'),
            'create' => Pages\CreateDeviceAssignment::route('/create'),
            'edit' => Pages\EditDeviceAssignment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNull('returned_date')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }
}
