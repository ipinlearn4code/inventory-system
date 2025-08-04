<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Models\Device;
use App\Services\FilamentPermissionService;
use App\Services\DropdownOptionsService;
use App\Filament\Helpers\FormSchemaHelper;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use App\Traits\HasInventoryLogging;

class DeviceResource extends Resource
{
    use HasInventoryLogging;
    
    protected static ?string $model = Device::class;
    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $navigationGroup = 'Device Management';
    protected static ?string $navigationLabel = 'Devices';
    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return FilamentPermissionService::canViewAny();
    }

    public static function canCreate(): bool
    {
        return FilamentPermissionService::canCreate();
    }

    public static function canEdit($record): bool
    {
        return FilamentPermissionService::canEdit($record);
    }

    public static function canDelete($record): bool
    {
        return FilamentPermissionService::canDelete($record);
    }

    public static function form(Form $form): Form
    {
        return $form->schema(FormSchemaHelper::getDeviceFormSchema());
    }

    public static function table(Table $table): Table
    {

        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Optimize query with eager loading
                return $query->with([
                    'bribox:bribox_id,type,type,bribox_category_id',
                    'bribox.category:bribox_category_id,category_name',
                    'currentAssignment:assignment_id,device_id,user_id,returned_date',
                    'currentAssignment.user:user_id,name,pn'
                ]);
            })
            // ->persistTableColumnToggleState(false)   
            ->columns([
                Tables\Columns\TextColumn::make('device_id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('brand')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('brand_name')
                    ->label('Model/Series')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('asset_code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('bribox.type')
                    ->label('Type')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('bribox.category.category_name')
                    ->label('Category')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('condition')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Baik' => 'success',
                        'Rusak' => 'danger',
                        'Perlu Pengecekan' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Digunakan' => 'success',
                        'Tidak Digunakan' => 'gray',
                        'Cadangan' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('currentAssignment.user.name')
                    ->label('Assigned To')
                    ->default('Unassigned')
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand')
                    ->multiple()
                    ->options(DropdownOptionsService::getDeviceBrands()),
                Tables\Filters\SelectFilter::make('condition')
                    ->multiple()
                    ->options(DropdownOptionsService::getDeviceConditions()),
                Tables\Filters\SelectFilter::make('bribox_category')
                    ->options(DropdownOptionsService::getBriboxCategories())
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $value): Builder => $query->whereHas(
                                'bribox',
                                fn(Builder $query): Builder => $query->where('bribox_category_id', $value)
                            )
                        );
                    }),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Bribox Type')
                    ->options(DropdownOptionsService::getBriboxTypes()),
            ])

            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->slideOver()
                        ->tooltip('View device details')
                        ->form(fn(Device $record) => FormSchemaHelper::getDeviceViewSchema($record)),
                    Tables\Actions\Action::make('Print QR Sticker')
                        ->label('Print QR Sticker')
                        ->icon('heroicon-o-qr-code')
                        ->color('info')
                        ->url(fn($record) => route('qr-code.sticker', $record->device_id))
                        ->openUrlInNewTab()
                        ->tooltip('View printable QR code sticker for this device'),
                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit device information'),
                    Tables\Actions\DeleteAction::make()
                        ->before(function ($record, $action) {
                            // Get the original data before deletion and log it
                            $action->record->logDeviceModelChanges($record, 'deleted', $record->toArray());
                        })
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
            // ->heading('Devices')
            
            // ->deferLoading()            
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Device')
                    ->icon('heroicon-o-plus')
                    ->tooltip('Add a new device to the inventory'),
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
            'generate-qr' => Pages\GenerateQRStickers::route('/generate-qr'),
        ];
    }
}
