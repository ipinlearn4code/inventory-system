<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Models\Branch;
use App\Services\FilamentPermissionService;
use App\Filament\Helpers\FormSchemaHelper;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Master Data';

    public static function canViewAny(): bool
    {
        return FilamentPermissionService::isSuperAdmin();
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
        return $form->schema(FormSchemaHelper::getBranchFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch_id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('branch_code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('unit_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('mainBranch.main_branch_name')
                    ->label('Main Branch')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('mainBranch.main_branch_code')
                    ->label('Main Branch Code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->slideOver()
                        ->tooltip('View branch details'),
                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit branch information'),
                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Delete this branch'),
                ])
                ->iconButton()
                ->icon('heroicon-o-ellipsis-horizontal')
                ->tooltip('Branch Actions'),
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
            'index' => Pages\ListBranches::route('/'),
            'create' => Pages\CreateBranch::route('/create'),
            'edit' => Pages\EditBranch::route('/{record}/edit'),
        ];
    }
}
