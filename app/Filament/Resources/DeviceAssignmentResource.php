<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceAssignmentResource\Pages;
use App\Models\DeviceAssignment;
use App\Models\Device;
use App\Models\User;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;

class DeviceAssignmentResource extends Resource
{
    protected static ?string $model = DeviceAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Inventory Management';
    protected static ?string $navigationLabel = 'Device Assignments';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) return false;
        
        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin'));
    }

    public static function canCreate(): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) return false;
        
        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin'));
    }

    public static function canEdit($record): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) return false;
        
        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && ($authModel->hasRole('superadmin') || $authModel->hasRole('admin'));
    }

    public static function canDelete($record): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) return false;
        
        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && $authModel->hasRole('superadmin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('device_id')
                    ->label('Device')
                    ->options(function () {
                        return Device::whereDoesntHave('currentAssignment')
                            ->get()
                            ->mapWithKeys(function ($device) {
                                return [$device->device_id => "{$device->asset_code} - {$device->brand_name} ({$device->serial_number})"];
                            });
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Only available devices (not currently assigned) are shown'),

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
                    ->preload(),

                Select::make('branch_id')
                    ->label('Branch')
                    ->options(Branch::with('mainBranch')->get()->mapWithKeys(function ($branch) {
                        return [$branch->branch_id => $branch->unit_name . ' (' . $branch->mainBranch->main_branch_name . ')'];
                    }))
                    ->required()
                    ->searchable()
                    ->preload(),

                DatePicker::make('assigned_date')
                    ->label('Assignment Date')
                    ->required()
                    ->default(now())
                    ->maxDate(now()),

                DatePicker::make('returned_date')
                    ->label('Return Date')
                    ->helperText('Leave empty for active assignments'),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'Digunakan' => 'Digunakan (In Use)',
                        'Tidak Digunakan' => 'Tidak Digunakan (Not Used)',
                        'Cadangan' => 'Cadangan (Backup)',
                    ])
                    ->required()
                    ->default('Digunakan'),

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
                    ->sortable(),

                Tables\Columns\TextColumn::make('device.asset_code')
                    ->label('Device Asset Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('device.brand_name')
                    ->label('Device Brand')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Assigned to')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.pn')
                    ->label('PN')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('branch.unit_name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('branch.mainBranch.main_branch_name')
                    ->label('Main Branch')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('assigned_date')
                    ->label('Assigned Date')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('returned_date')
                    ->label('Return Date')
                    ->date('d/m/Y')
                    ->placeholder('Active')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Digunakan' => 'success',
                        'Tidak Digunakan' => 'warning',
                        'Cadangan' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->getStateUsing(fn ($record) => is_null($record->returned_date))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Digunakan' => 'Digunakan (In Use)',
                        'Tidak Digunakan' => 'Tidak Digunakan (Not Used)',
                        'Cadangan' => 'Cadangan (Backup)',
                    ]),

                Tables\Filters\Filter::make('active_assignments')
                    ->label('Active Assignments Only')
                    ->query(fn (Builder $query): Builder => $query->whereNull('returned_date'))
                    ->default(),

                Tables\Filters\Filter::make('returned_assignments')
                    ->label('Returned Assignments Only')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('returned_date')),

                Tables\Filters\SelectFilter::make('branch_code')
                    ->label('Branch')
                    ->options(Branch::all()->pluck('unit_name', 'branch_code')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                
                Tables\Actions\Action::make('return_device')
                    ->label('Return Device')
                    ->icon('heroicon-o-arrow-left-on-rectangle')
                    ->color('warning')
                    ->visible(fn ($record) => is_null($record->returned_date))
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
