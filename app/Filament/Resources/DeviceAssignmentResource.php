<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceAssignmentResource\Pages;
use App\Models\DeviceAssignment;
use App\Services\FilamentPermissionService;
use App\Filament\Helpers\FormSchemaHelper;
use App\Filament\Helpers\TableSchemaHelper;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
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
        return $form->schema(FormSchemaHelper::getDeviceAssignmentSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(TableSchemaHelper::getDeviceAssignmentColumns())
            ->modifyQueryUsing(
                fn($query) => $query->with([
                    'device',
                    'user',
                    'branch',
                    'branch.mainBranch',
                ])
            )
            ->filters(TableSchemaHelper::getDeviceAssignmentFilters())
            ->actions(TableSchemaHelper::getDeviceAssignmentActions())
            ->recordUrl(null)
            ->bulkActions(TableSchemaHelper::getDeviceAssignmentBulkActions())
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
