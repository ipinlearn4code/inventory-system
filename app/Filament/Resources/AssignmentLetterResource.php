<?php

namespace App\Filament\Resources;

use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\ViewField;

use App\Filament\Resources\AssignmentLetterResource\Pages;
use App\Filament\Resources\AssignmentLetterResource\RelationManagers;
use App\Models\AssignmentLetter;
use App\Services\StorageHealthService;
use App\Services\PdfPreviewService;
use App\Services\AssignmentLetterFormBuilder;
use App\Filament\Helpers\FormSchemaHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
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
        return $form
            ->schema(app(AssignmentLetterFormBuilder::class)->buildFormSchema());
    }

    public static function table(Table $table): Table
    {
        // Check storage health before displaying table
        $healthService = app(StorageHealthService::class);
        $storageStatus = $healthService->checkMinioHealth();

        return $table
            ->headerActions([
                Tables\Actions\Action::make('storage_status')
                    ->label(function () use ($storageStatus) {
                        return match ($storageStatus['status']) {
                            'healthy' => 'Storage: Healthy',
                            'warning' => 'Storage: Warning',
                            'error' => 'Storage: Error',
                            default => 'Storage: Unknown',
                        };
                    })
                    ->color(function () use ($storageStatus) {
                        return StorageHealthService::getStatusColor($storageStatus['status']);
                    })
                    ->icon(function () use ($storageStatus) {
                        return StorageHealthService::getStatusIcon($storageStatus['status']);
                    })
                    ->action(function () use ($healthService) {
                        $refreshedStatus = $healthService->refreshStorageHealth();
                        $minioStatus = $refreshedStatus['minio'];

                        Notification::make()
                            ->title('Storage Status Refreshed')
                            ->body($minioStatus['message'])
                            ->color(StorageHealthService::getStatusColor($minioStatus['status']))
                            ->icon(StorageHealthService::getStatusIcon($minioStatus['status']))
                            ->send();
                    })
                    ->tooltip(function () use ($storageStatus) {
                        $details = '';
                        if (isset($storageStatus['details']) && is_array($storageStatus['details'])) {
                            $details = "\nEndpoint: " . ($storageStatus['details']['endpoint'] ?? 'N/A');
                            $details .= "\nBucket: " . ($storageStatus['details']['bucket'] ?? 'N/A');
                            if (isset($storageStatus['details']['response_time_ms'])) {
                                $details .= "\nResponse: " . $storageStatus['details']['response_time_ms'] . "ms";
                            }
                        }
                        return $storageStatus['message'] . $details;
                    }),

                Tables\Actions\Action::make('storage_info')
                    ->label('Storage Info')
                    ->icon('heroicon-o-information-circle')
                    ->color('info')
                    ->modalHeading('Storage System Information')
                    ->modalDescription('Current status of all storage systems')
                    ->modalContent(function () use ($healthService) {
                        $allStatus = $healthService->checkAllStorageHealth();
                        return view('filament.modals.storage-info-modal', ['storageStatus' => $allStatus]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
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
                    ->label('Assigned To')
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->slideOver()
                        ->tooltip('View assignment letter details')
                        ->icon('heroicon-o-eye')
                        ->color('gray') // Neutral color for a clean look
                        ->modalHeading(fn(AssignmentLetter $record) => "Assignment Letter #{$record->letter_number}")
                        ->form(function (AssignmentLetter $record) {
                            $pdfPreviewService = app(PdfPreviewService::class);
                            $previewData = $pdfPreviewService->getPreviewData($record);
                            // dd($record);
                            return [
                                // Main Details Section
                                Section::make('Assignment Letter Details')
                                    ->collapsible()
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        Grid::make(2) // 3-column grid for better spacing
                                            ->schema([
                                                Placeholder::make('letter_number')
                                                    ->label('Letter Number')
                                                    ->content($record->letter_number ?? 'N/A')
                                                    ->inlineLabel(),
                                                Placeholder::make('letter_type')
                                                    ->label('Letter Type')
                                                    ->content($record->letter_type ?? 'N/A')
                                                    ->inlineLabel(),
                                                Placeholder::make('letter_date')
                                                    ->label('Letter Date')
                                                    ->content($record->letter_date ? $record->letter_date->format('d M Y') : 'N/A')
                                                    ->inlineLabel(),
                                                Placeholder::make('user')
                                                    ->label('Assigned To')
                                                    ->content($record->assignment?->user->name ?? 'N/A')
                                                    ->inlineLabel(),
                                                Placeholder::make('device')
                                                    ->label('Device')
                                                    ->content(optional($record->assignment?->device, fn($device) => "{$device->brand} {$device->brand_name}") ?? 'N/A')
                                                    ->inlineLabel(),
                                                Placeholder::make('approver')
                                                    ->label('Approver')
                                                    ->content($record->approver?->name ?? 'N/A')
                                                    ->inlineLabel(),
                                            ]),
                                    ]),

                                // PDF Preview Section
                                Section::make('PDF Preview')
                                    ->collapsible()
                                    ->icon('heroicon-o-document-magnifying-glass')
                                    ->schema([
                                        ViewField::make('pdf_preview')
                                            ->view('filament.components.pdf-preview', ['previewData' => $previewData])
                                            ->label(''),
                                    ])
                                    ->visible($previewData['hasFile']),

                                // File Information Section (Fallback)
                                Section::make('File Information')
                                    ->collapsible()
                                    ->icon('heroicon-o-exclamation-circle')
                                    ->schema([
                                        Placeholder::make('no_file')
                                            ->label('')
                                            ->content('No PDF file uploaded for this assignment letter.')
                                            ->extraAttributes(['class' => 'text-warning-600']),
                                    ])
                                    ->visible(!$previewData['hasFile']),
                            ];
                        }),
                    Tables\Actions\EditAction::make()
                        ->tooltip('Edit assignment letter'),
                    Tables\Actions\Action::make('download')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->tooltip('Download assignment letter file')
                        ->url(fn(AssignmentLetter $record): string => $record->getFileUrl() ?? '#')
                        ->openUrlInNewTab()
                        ->visible(fn(AssignmentLetter $record): bool => $record->hasFile()),
                    Tables\Actions\DeleteAction::make()
                        ->tooltip('Delete this assignment letter'),
                ])
                    ->iconButton()
                    ->icon('heroicon-o-ellipsis-horizontal')
                    ->tooltip('Assignment Letter Actions'),
            ])
            ->recordUrl(null)
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
