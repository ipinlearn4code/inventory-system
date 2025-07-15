<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';

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
        return $authModel && $authModel->hasRole('superadmin');
    }

    public static function canEdit($record): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) return false;
        
        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && $authModel->hasRole('superadmin');
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
                Forms\Components\TextInput::make('pn')
                    ->label('Personnel Number')
                    ->required()
                    ->maxLength(8)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('branch_id')
                    ->label('Branch')
                    ->options(\App\Models\Branch::all()->pluck('unit_name', 'branch_id'))
                    ->required()
                    ->searchable(),
                
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(50),
                
                Forms\Components\Select::make('department_id')
                    ->label('Department')
                    ->options(Department::all()->pluck('name', 'department_id'))
                    ->required()
                    ->searchable(),
                
                Forms\Components\TextInput::make('position')
                    ->label('Position')
                    ->datalist(
                        \App\Models\User::query()
                            ->whereNotNull('position')
                            ->distinct()
                            ->orderBy('position')
                            ->pluck('position')
                            ->toArray()
                    )
                    ->autocomplete(false)
                    ->required(),
                
                Forms\Components\Toggle::make('create_auth')
                    ->label('Add Authentication')
                    ->helperText('Create authentication credentials for this user')
                    ->default(false)
                    ->live()
                    ->visible(fn ($livewire) => $livewire instanceof \App\Filament\Resources\UserResource\Pages\CreateUser),
                
                Forms\Components\Section::make('Authentication Details')
                    ->schema([
                        Forms\Components\TextInput::make('auth.password')
                            ->label('Password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser && $livewire->data['create_auth'])
                            ->confirmed()
                            ->minLength(8)
                            ->helperText('Leave blank to keep the same password when editing'),
                        
                        Forms\Components\TextInput::make('auth.password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser && $livewire->data['create_auth'])
                            ->dehydrated(false)
                            ->minLength(8),
                        
                        Forms\Components\Select::make('auth.role')
                            ->label('Role')
                            ->options([
                                'user' => 'Regular User',
                                'admin' => 'Administrator',
                                'superadmin' => 'Super Administrator'
                            ])
                            ->required(fn (Forms\Get $get): bool => $get('create_auth'))
                            ->default('user')
                            ->helperText('Determines what actions the user can perform'),
                    ])
                    ->visible(fn (Forms\Get $get): bool => $get('create_auth'))
                    ->columns(2),
            ]);
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
                        ->tooltip('View user details'),
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
