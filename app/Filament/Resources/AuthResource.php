<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuthResource\Pages;
use App\Models\Auth;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AuthResource extends Resource
{
    protected static ?string $model = Auth::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?string $navigationLabel = 'Authentication';

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
        
        // Superadmin can edit all, admin can only edit regular users
        if ($authModel->hasRole('superadmin')) {
            return true;
        }
        
        if ($authModel->hasRole('admin')) {
            return $record->role === 'user'; // Admin can only edit regular users
        }
        
        return false;
    }

    public static function canDelete($record): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) return false;
        
        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        
        // Superadmin can delete all, admin can only delete regular users
        if ($authModel->hasRole('superadmin')) {
            return true;
        }
        
        if ($authModel->hasRole('admin')) {
            return $record->role === 'user'; // Admin can only delete regular users
        }
        
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('pn')
                    ->label('Personnel Number')
                    ->options(User::all()->pluck('name', 'pn')->map(function ($name, $pn) {
                        return "$pn - $name";
                    }))
                    ->required()
                    ->searchable()
                    ->unique(ignoreRecord: true),
                
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(50),
                
                Forms\Components\Select::make('role')
                    ->options(function () {
                        $auth = session('authenticated_user');
                        if (!$auth) return [];
                        
                        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
                        
                        if ($authModel && $authModel->hasRole('superadmin')) {
                            // Superadmin can assign any role
                            return [
                                'user' => 'User',
                                'admin' => 'Admin',
                                'superadmin' => 'Super Admin',
                            ];
                        } elseif ($authModel && $authModel->hasRole('admin')) {
                            // Admin can only assign user role
                            return [
                                'user' => 'User',
                            ];
                        }
                        
                        return [];
                    })
                    ->required(),
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
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('user.department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'superadmin' => 'danger',
                        'admin' => 'warning',
                        'user' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'user' => 'User',
                        'admin' => 'Admin', 
                        'superadmin' => 'Super Admin',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->slideOver()
                        ->tooltip('View auth details'),
                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit auth information'),
                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Delete this auth'),
                ])
                ->iconButton()
                ->icon('heroicon-o-ellipsis-horizontal')
                ->tooltip('Auth Actions'),
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
            'index' => Pages\ListAuths::route('/'),
            'create' => Pages\CreateAuth::route('/create'),
            'edit' => Pages\EditAuth::route('/{record}/edit'),
        ];
    }
}
