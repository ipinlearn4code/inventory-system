<?php

namespace App\Services;

use App\Models\AssignmentLetter;
use App\Models\DeviceAssignment;
use App\Models\User;
use App\Services\AuthenticationService;
use App\Services\PdfPreviewService;
use App\Services\StorageHealthService;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\ViewField;

class AssignmentLetterFormBuilder
{
    public function __construct(
        private readonly AuthenticationService $authService,
        private readonly StorageHealthService $storageHealthService,
        private readonly PdfPreviewService $pdfPreviewService
    ) {
    }

    /**
     * Build the complete form schema
     */
    public function buildFormSchema(): array
    {
        return [
            $this->buildLetterTypeSelect(),
            $this->buildDeviceAssignmentSelect(),
            $this->buildLetterNumberInput(),
            $this->buildLetterDatePicker(),
            $this->buildApproverSection(),
            $this->buildFileUploadSection(),
            ...$this->buildHiddenFields(),
        ];
    }
    /**
     * Build letter type select field
     */

    private function buildLetterTypeSelect(): Forms\Components\Select
    {
        return Forms\Components\Select::make('letter_type')
            ->label('Letter Type')
            
            ->options([
                'assignment' => 'Assignment Letter',
                'return' => 'Return Letter',
                'transfer' => 'Transfer Letter',
                'maintenance' => 'Maintenance Letter',
            ])
            ->required()
            ->native(false)
            ->placeholder('Select a letter type')
            ->selectablePlaceholder(false)
            ->validationAttribute('letter type')
            ->default(function ($livewire, $record) {
                return $record ? $record->letter_type : null;
            });
    }

    /**
     * Build device assignment select field
     */
    private function buildDeviceAssignmentSelect(): Forms\Components\Select
    {
        return Forms\Components\Select::make('assignment_id')
            ->label('Device Assignment')
            ->options(function () {
                return DeviceAssignment::with(['user', 'device'])
                    ->get()
                    ->mapWithKeys(function ($assignment) {
                        return [
                            $assignment->assignment_id =>
                                $assignment->user->name . ' - ' . $assignment->device->asset_code
                        ];
                    });
            })
            ->searchable()
            ->required();
    }


    /**
     * Build letter number input field
     */
    private function buildLetterNumberInput(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('letter_number')
            ->label('Letter Number')
            ->placeholder('Input Letter Number')
            ->required()
            ->maxLength(50);
    }

    /**
     * Build letter date picker
     */
    private function buildLetterDatePicker(): Forms\Components\DatePicker
    {
        return Forms\Components\DatePicker::make('letter_date')
            ->label('Letter Date')
            ->required()
            ->default(now());
    }

    /**
     * Build approver section
     */
    private function buildApproverSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Approver Information')
            ->description('Select who will approve this assignment letter')
            ->schema([
                $this->buildApproverToggle(),
                $this->buildApproverSelect(),
            ]);
    }

    /**
     * Build approver toggle
     */
    private function buildApproverToggle(): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make('is_approver')
            ->label('Are you the approver?')
            ->default(true)
            ->live()
            ->dehydrated(false)
            ->disabled(fn($record) => filled($record))
            ->hint(fn($record) => filled($record) ? 'Cannot change approver in edit mode.' : null)
            ->afterStateUpdated(function ($set, $state) {
                if ($state) {
                    $currentUser = $this->authService->getCurrentUser();
                    if ($currentUser) {
                        $set('approver_id', $currentUser->user_id);
                    }
                }
            });
    }

    /**
     * Build approver select
     */
    private function buildApproverSelect(): Forms\Components\Select
    {
        return Forms\Components\Select::make('approver_id')
            ->label('Approver')
            ->options(function () {
                return User::pluck('name', 'user_id')->toArray();
            })
            ->disabled(fn($get) => $get('is_approver'))
            ->native(false)
            ->placeholder('Select an approver')
            ->required()
            ->selectablePlaceholder(false)
            ->searchable()
            ->preload()
            ->reactive()
            ->default(function () {
                $currentUser = $this->authService->getCurrentUser();
                return $currentUser?->user_id;
            });
    }

    /**
     * Build file upload section with preview
     */
    private function buildFileUploadSection(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Assignment Letter File')
            ->description('Upload and preview the assignment letter PDF')
            ->schema([
                $this->buildFileUploadField(),
                $this->buildFilePreviewField(),
            ]);
    }

    /**
     * Build file upload field
     */
    private function buildFileUploadField(): Forms\Components\FileUpload
    {
        return Forms\Components\FileUpload::make('file_path')
            ->label('PDF File')
            ->required()
            ->disk('public')
            ->directory('assignment-letters')
            ->acceptedFileTypes(['application/pdf'])
            ->maxSize(5120)
            ->helperText($this->getStorageHelperText())
            ->hint($this->getStorageHint())
            ->hiddenLabel(fn($record) => filled($record) && $record->hasFile())
            ->preserveFilenames() // This preserves the original filename
            ->getUploadedFileNameForStorageUsing(function ($file) {
                // Return the original filename without modification
                return $file->getClientOriginalName();
            });
    }

    /**
     * Build file preview field
     */
    private function buildFilePreviewField(): ViewField
    {
        return ViewField::make('file_preview')
            ->label('File Preview')
            ->view('filament.components.pdf-preview')
            ->viewData(function ($record) {
                if (!$record instanceof AssignmentLetter) {
                    return ['previewData' => ['hasFile' => false]];
                }

                return [
                    'previewData' => $this->pdfPreviewService->getPreviewData($record),
                    'record' => $record,
                ];
            })
            ->visible(fn($record) => filled($record) && $record instanceof AssignmentLetter && $record->hasFile());
    }

    /**
     * Build hidden fields
     */
    private function buildHiddenFields(): array
    {
        return [
            Forms\Components\Hidden::make('created_by')
                ->default(function () {
                    return $this->authService->getCurrentUserId();
                }),
            Forms\Components\Hidden::make('created_at')
                ->default(now()),
        ];
    }

    /**
     * Get storage helper text
     */
    private function getStorageHelperText(): string
    {
        $storageStatus = $this->storageHealthService->checkMinioHealth();

        return match ($storageStatus['status']) {
            'healthy' => '✅ Storage is healthy. Files will be uploaded to MinIO.',
            'warning' => '⚠️ Storage warning: ' . $storageStatus['message'] . '. Files will be uploaded to local storage as backup.',
            default => '❌ Storage error: ' . $storageStatus['message'] . '. Files will be uploaded to local storage only.',
        };
    }

    /**
     * Get storage hint
     */
    private function getStorageHint(): ?string
    {
        $storageStatus = $this->storageHealthService->checkMinioHealth();

        if ($storageStatus['status'] !== 'healthy') {
            return 'Note: There are storage connectivity issues. Please verify file uploads after submission.';
        }

        return null;
    }
}
