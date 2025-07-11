<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignmentLetterResource\Pages;
use App\Filament\Resources\AssignmentLetterResource\RelationManagers;
use App\Models\AssignmentLetter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AssignmentLetterResource extends Resource
{
    protected static ?string $model = AssignmentLetter::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Device Management';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        // dd(session('authenticated_user'));
        return $form
            ->schema([
                Forms\Components\Select::make('assignment_id')
                    ->label('Device Assignment')
                    ->options(function () {
                        return \App\Models\DeviceAssignment::with(['user', 'device'])
                            ->get()
                            ->mapWithKeys(function ($assignment) {
                                return [
                                    $assignment->assignment_id =>
                                        $assignment->user->name . ' - ' . $assignment->device->asset_code
                                ];
                            });
                    })
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('letter_type')
                    ->label('Letter Type')
                    ->options([
                        'assignment' => 'Assignment Letter',
                        'return' => 'Return Letter',
                        'transfer' => 'Transfer Letter',
                        'maintenance' => 'Maintenance Letter',
                    ])
                    ->required()
                    ->native(false) // Use custom dropdown instead of native HTML select
                    ->placeholder('Select a letter type')
                    ->selectablePlaceholder(false) // This prevents placeholder selection
                    ->validationAttribute('letter type')
                    ->default(function ($livewire, $record) {
                        // Only set default for existing records, not on creation
                        return $record ? $record->letter_type : null;
                    }),

                Forms\Components\TextInput::make('letter_number')
                    ->label('Letter Number')
                    ->placeholder('Input Letter Number')
                    ->required()
                    ->maxLength(50),

                Forms\Components\DatePicker::make('letter_date')
                    ->label('Letter Date')
                    ->required()
                    ->default(now()),

                Forms\Components\Toggle::make('is_approver')
                    ->label('Are you the approver?')
                    ->default(true)
                    ->live()
                    ->dehydrated(false) // This ensures the field won't be sent to the model
                    ->disabled(fn ($record) => filled($record)) // Disable in edit mode
                    ->hint(fn ($record) => filled($record) ? 'Cannot change approver in edit mode.' : null)
                    ->afterStateUpdated(function ($set, $state) {
                        if ($state) {
                            // When toggled on, set the current user as approver
                            $auth = session('authenticated_user');
                            if ($auth) {
                                $user = \App\Models\User::where('pn', $auth['pn'])->first();
                                if ($user) {
                                    $set('approver_id', $user->user_id);
                                }
                            }
                        }
                        // Note: Don't set approver_id to null when toggled off, 
                        // let the user select someone else from the dropdown
                    }),

                Forms\Components\Select::make('approver_id')
                    ->label('Approver')
                    ->options(function () {
                        return \App\Models\User::pluck('name', 'user_id')->toArray();
                    })
                    ->disabled(fn ($get) => $get('is_approver'))
                    ->native(false)
                    ->placeholder('Select an approver')
                    ->required()
                    ->selectablePlaceholder(false)
                    ->searchable()
                    ->preload()
                    ->reactive() // Make this field reactive to state changes
                    ->default(function () {
                        // Set default to current user if they are the approver
                        $auth = session('authenticated_user');
                        if ($auth) {
                            $user = \App\Models\User::where('pn', $auth['pn'])->first();
                            if ($user) {
                                return $user->user_id;
                            }
                        }
                        return null;
                    }),

                Forms\Components\FileUpload::make('file_path')
                    ->label('Assignment Letter File')
                    ->disk('public')
                    ->directory('assignment-letters')
                    ->acceptedFileTypes(['application/pdf'])
                    ->maxSize(5120),

                Forms\Components\Hidden::make('created_by')
                    ->default(function () {
                        $auth = session('authenticated_user');
                        if ($auth) {
                            $user = \App\Models\User::where('pn', $auth['pn'])->first();
                            if ($user) {
                                return $user->user_id;
                            }
                        }
                        return null;
                    }),

                Forms\Components\Hidden::make('created_at')
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('letter_number')
                    ->label('Letter Number')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('letter_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'assignment' => 'success',
                        'return' => 'warning',
                        'transfer' => 'info',
                        'maintenance' => 'danger',
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('assignment.user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('assignment.device.asset_code')
                    ->label('Asset Code')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('letter_date')
                    ->label('Letter Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('approver.name')
                    ->label('Approver')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('file_path')
                    ->label('Has File')
                    ->boolean()
                    ->trueIcon('heroicon-o-document')
                    ->falseIcon('heroicon-o-x-mark')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('letter_type')
                    ->options([
                        'assignment' => 'Assignment Letter',
                        'return' => 'Return Letter',
                        'transfer' => 'Transfer Letter',
                        'maintenance' => 'Maintenance Letter',
                    ]),

                Tables\Filters\Filter::make('has_file')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('file_path'))
                    ->label('Has File'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(AssignmentLetter $record): string => $record->getFileUrl() ?? '#')
                    ->openUrlInNewTab()
                    ->visible(fn(AssignmentLetter $record): bool => $record->hasFile()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListAssignmentLetters::route('/'),
            'create' => Pages\CreateAssignmentLetter::route('/create'),
            'edit' => Pages\EditAssignmentLetter::route('/{record}/edit'),
        ];
    }
}
