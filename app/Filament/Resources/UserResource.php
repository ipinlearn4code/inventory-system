<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Department;
use App\Services\FilamentPermissionService;
use App\Filament\Helpers\FormSchemaHelper;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';

    public static function canViewAny(): bool
    {
        return FilamentPermissionService::canViewAny();
    }

    public static function canCreate(): bool
    {
        return FilamentPermissionService::isSuperAdmin();
    }

    public static function canEdit($record): bool
    {
        return FilamentPermissionService::isSuperAdmin();
    }

    public static function canDelete($record): bool
    {
        return FilamentPermissionService::isSuperAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form->schema(FormSchemaHelper::getUserFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pn')
                    ->label('PN')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('branch.unit_name')
                    ->label('Branch')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('position')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('active_assignments_count')
                    ->label('Devices Assigned')
                    ->getStateUsing(function ($record) {
                        return $record->deviceAssignments()->whereNull('returned_date')->count();
                    })
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state <= 2 => 'success',
                        $state <= 5 => 'warning',
                        default => 'danger',
                    })
                    ->suffix(' devices')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(Department::all()->pluck('name', 'department_id')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->slideOver()
                        ->tooltip('View user details')
                        ->form(fn(User $record) => FormSchemaHelper::getUserViewSchema($record)),
                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit user information'),
                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Delete this user'),
                ])
                ->iconButton()
                ->icon('heroicon-o-ellipsis-horizontal')
                ->tooltip('User Actions'),
            ])
            ->recordUrl(null)
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
