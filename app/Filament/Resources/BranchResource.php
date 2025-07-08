<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BranchResource\Pages;
use App\Filament\Resources\BranchResource\RelationManagers;
use App\Models\Branch;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('branch_code')
                    ->label('Branch Code')
                    ->required()
                    ->maxLength(8)
                    ->unique(ignoreRecord: true),
                
                Forms\Components\TextInput::make('unit_name')
                    ->label('Unit Name')
                    ->required()
                    ->maxLength(50),
                
                Forms\Components\TextInput::make('main_branch_code')
                    ->label('Main Branch Code')
                    ->required()
                    ->maxLength(4)
                    ->unique(ignoreRecord: true),
                
                Forms\Components\TextInput::make('main_branch_name')
                    ->label('Main Branch Name')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('branch_id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('branch_code')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('unit_name')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('main_branch_code')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('main_branch_name')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
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
