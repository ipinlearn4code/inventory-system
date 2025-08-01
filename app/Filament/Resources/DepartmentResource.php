<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use App\Filament\Helpers\FormSchemaHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Master Data';

    public static function canViewAny(): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) return false;
        
        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && $authModel->hasRole('superadmin'); // Only superadmin can manage master data
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
                Forms\Components\TextInput::make('department_id')
                    ->label('Department ID')
                    ->required()
                    ->maxLength(4)
                    ->unique(ignoreRecord: true)
                    ->placeholder('e.g., IT01'),
                
                Forms\Components\TextInput::make('name')
                    ->label('Department Name')
                    ->required()
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('department_id')
                    ->label('Department ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('name')
                    ->label('Department Name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Users Count')
                    ->counts('users')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->slideOver()
                        ->tooltip('View department details')
                        ->form(fn(Department $record) => FormSchemaHelper::getDepartmentViewSchema($record)),
                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit department information'),
                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Delete this department'),
                ])
                ->iconButton()
                ->icon('heroicon-o-ellipsis-horizontal')
                ->tooltip('Department Actions'),
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
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
