<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BriboxResource\Pages;
use App\Models\Bribox;
use App\Models\BriboxesCategory;
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
                    ->placeholder('e.g., A1'),
                
                Forms\Components\TextInput::make('type')
                    ->required()
                    ->maxLength(25),
                
                Forms\Components\Select::make('bribox_category_id')
                    ->label('Category')
                    ->options(BriboxesCategory::all()->pluck('category_name', 'bribox_category_id'))
                    ->required()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bribox_id')
                    ->label('Bribox ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('category.category_name')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('devices_count')
                    ->label('Devices')
                    ->counts('devices')
                    ->badge()
                    ->color('info')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->slideOver()
                        ->tooltip('View bribox details'),
                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit bribox information'),
                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Delete this bribox'),
                ])
                ->iconButton()
                ->icon('heroicon-o-ellipsis-horizontal')
                ->tooltip('Bribox Actions'),
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
            'index' => Pages\ListBriboxes::route('/'),
            'create' => Pages\CreateBribox::route('/create'),
            'edit' => Pages\EditBribox::route('/{record}/edit'),
        ];
    }
}
