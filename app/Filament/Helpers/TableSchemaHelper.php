<?php

namespace App\Filament\Helpers;

use App\Models\Branch;
use App\Models\DeviceAssignment;
use App\Filament\Helpers\FormSchemaHelper;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class TableSchemaHelper
{
    /**
     * Get device assignment table columns
     */
    public static function getDeviceAssignmentColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('assignment_id')
                ->label('ID')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('device.asset_code')
                ->label('Device Asset Code')
                ->searchable()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),

            Tables\Columns\TextColumn::make('device.brand')
                ->label('Brand & Model')
                ->getStateUsing(function ($record) {
                    $brand = $record->device->brand ?? '';
                    $model = $record->device->brand_name ?? '';
                    return trim("{$brand} {$model}");
                })
                ->searchable()
                ->sortable()
                ->toggleable(),

            Tables\Columns\TextColumn::make('user.name')
                ->label('Assigned To')
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

            Tables\Columns\IconColumn::make('is_active')
                ->label('Active')
                ->getStateUsing(fn($record) => is_null($record->returned_date))
                ->boolean()
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->trueColor('success')
                ->falseColor('danger')
                ->toggleable(),
        ];
    }

    /**
     * Get device assignment table filters
     */
    public static function getDeviceAssignmentFilters(): array
    {
        return [
            Tables\Filters\Filter::make('active_assignments')
                ->label('Active Assignments Only')
                ->query(fn(Builder $query): Builder => $query->whereNull('returned_date'))
                ->default(),

            Tables\Filters\Filter::make('returned_assignments')
                ->label('Returned Assignments Only')
                ->query(fn(Builder $query): Builder => $query->whereNotNull('returned_date')),

            Tables\Filters\SelectFilter::make('branch_id')
                ->label('Branch')
                ->options(
                    Branch::all()->mapWithKeys(function ($branch) {
                        return [
                            $branch->branch_id => "{$branch->unit_name} ({$branch->branch_code})"
                        ];
                    })
                ),
        ];
    }

    /**
     * Get device assignment table actions
     */
    public static function getDeviceAssignmentActions(): array
    {
        return [
            Tables\Actions\ActionGroup::make([
                Tables\Actions\ViewAction::make()
                    ->slideOver()
                    ->tooltip('View assignment details')
                    ->form(fn(DeviceAssignment $record) => FormSchemaHelper::getDeviceAssignmentViewSchema($record)),
                Tables\Actions\EditAction::make()
                    ->tooltip('Edit assignment information'),
                Tables\Actions\DeleteAction::make()
                    ->tooltip('Delete this assignment'),
            ])
                ->iconButton()
                ->icon('heroicon-o-ellipsis-horizontal')
                ->tooltip('Assignment Actions'),

            self::getReturnDeviceAction(),
        ];
    }

    /**
     * Get return device action using service layer
     */
    private static function getReturnDeviceAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('return_device')
            ->label('Return Device')
            ->icon('heroicon-o-arrow-left-on-rectangle')
            ->color('warning')
            ->tooltip('Mark device as returned')
            ->visible(fn($record) => is_null($record->returned_date))
            ->requiresConfirmation()
            ->modalHeading('Return Device')
            ->modalDescription('Are you sure you want to mark this device as returned?')
            ->action(function ($record) {
                $assignmentService = app(\App\Services\DeviceAssignmentService::class);
                $assignmentService->returnDevice($record->assignment_id, [
                    'returned_date' => now()->toDateString(),
                ]);
            })
            ->successNotificationTitle('Device returned successfully');
    }

    /**
     * Get device assignment bulk actions
     */
    public static function getDeviceAssignmentBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }
}
