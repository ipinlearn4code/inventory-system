<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BriboxResource\Pages;
use App\Models\Bribox;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BriboxResource extends Resource
{
    protected static ?string $model = Bribox::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
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
                Forms\Components\TextInput::make('bribox_id')
                    ->label('Bribox ID')
                    ->required()
                    ->maxLength(2)
                    ->unique(ignoreRecord: true)
                    ->placeholder('e.g., PC'),
                
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(25),
                
                Forms\Components\TextInput::make('category')
                    ->required()
                    ->maxLength(25),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bribox_id')
                    ->label('Bribox ID')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('category')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('devices_count')
                    ->label('Devices Count')
                    ->counts('devices')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListBriboxes::route('/'),
            'create' => Pages\CreateBribox::route('/create'),
            'edit' => Pages\EditBribox::route('/{record}/edit'),
        ];
    }
}
