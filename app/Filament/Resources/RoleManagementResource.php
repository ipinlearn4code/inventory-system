<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleManagementResource\Pages;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoleManagementResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Permission Management';
    protected static ?string $navigationLabel = 'Roles';
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return false; // Disabled - use Permission Matrix page instead
    }

    public static function canCreate(): bool
    {
        return self::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return self::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return self::canViewAny();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Role Name')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('e.g., editor, manager'),
                        
                        Forms\Components\TextInput::make('guard_name')
                            ->label('Guard Name')
                            ->default('web')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),
                
                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->options(Permission::all()->pluck('name', 'id'))
                            ->searchable()
                            ->bulkToggleable()
                            ->gridDirection('row')
                            ->columns(3)
                            ->descriptions(self::getPermissionDescriptions()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Role Name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'superadmin' => 'danger',
                        'admin' => 'warning',
                        'user' => 'success',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->counts('permissions')
                    ->badge()
                    ->color('info'),
                
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Users')
                    ->getStateUsing(function ($record): int {
                        return \App\Models\Auth::whereHas('roles', function ($query) use ($record) {
                            $query->where('roles.id', $record->id);
                        })->count();
                    })
                    ->badge()
                    ->color('primary'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('guard_name')
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        // Prevent deletion of core roles
                        if (in_array($record->name, ['superadmin', 'admin', 'user'])) {
                            \Filament\Notifications\Notification::make()
                                ->title('Cannot delete core role')
                                ->body('Core system roles cannot be deleted.')
                                ->danger()
                                ->send();
                            return;
                        }
                        $record->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    protected static function getPermissionDescriptions(): array
    {
        return [
            'view own assignments' => 'View devices assigned to user',
            'make requests' => 'Make requests for devices',
            'manage devices' => 'Create, edit, delete devices',
            'manage assignments' => 'Create, edit, delete assignments',
            'manage regular users' => 'Manage users with user role',
            'manage regular auth' => 'Manage auth for regular users',
            'setup master data' => 'Manage departments, branches, briboxes',
            'manage all users' => 'Manage any user',
            'manage all auth' => 'Manage any authentication record',
            'view audit logs' => 'View system audit logs',
            'export data' => 'Export system data',
            'manage departments' => 'Manage department master data',
            'manage branches' => 'Manage branch master data',
            'manage briboxes' => 'Manage bribox categories',
        ];
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
            'index' => Pages\ListRoleManagement::route('/'),
            'create' => Pages\CreateRoleManagement::route('/create'),
            'edit' => Pages\EditRoleManagement::route('/{record}/edit'),
        ];
    }
}
