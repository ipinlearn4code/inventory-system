<?php

namespace App\Services;

use App\Models\Device;
use App\Models\User;
use Filament\Forms;

class QuickAssignmentFormBuilder
{
    public function __construct(
        private readonly AuthenticationService $authService
    ) {}

    /**
     * Build the device assignment form fields
     */
    public function buildDeviceAssignmentFields(): array
    {
        return [
            Forms\Components\Select::make('user_id')
                ->label('User')
                ->options(User::pluck('name', 'user_id'))
                ->searchable()
                ->required()
                ->helperText('User who will receive the device'),
                
            Forms\Components\Select::make('device_id')
                ->label('Device')
                ->options(function() {
                    return Device::available()
                        ->where('condition', 'Baik')
                        ->get()
                        ->pluck('asset_code_with_type', 'device_id');
                })
                ->searchable()
                ->required()
                ->helperText('Only available devices in good condition are shown'),
                
            Forms\Components\DatePicker::make('assigned_date')
                ->label('Assignment Date')
                ->required()
                ->default(now()),
                
            Forms\Components\Textarea::make('assignment_notes')
                ->label('Notes')
                ->rows(3)
                ->maxLength(500),
        ];
    }

    /**
     * Build the assignment letter form fields
     */
    public function buildAssignmentLetterFields(): array
    {
        return [
            Forms\Components\TextInput::make('letter_number')
                ->label('Letter Number')
                ->placeholder('Input Letter Number')
                ->required()
                ->maxLength(50),
                
            Forms\Components\DatePicker::make('letter_date')
                ->label('Letter Date')
                ->required()
                ->default(now()),
                
            $this->buildApproverToggle(),
            $this->buildApproverSelect(),
            $this->buildFileUpload(),
        ];
    }

    /**
     * Build the approver toggle field
     */
    private function buildApproverToggle(): Forms\Components\Toggle
    {
        return Forms\Components\Toggle::make('is_approver')
            ->label('Are you the approver?')
            ->default(true)
            ->live()
            ->dehydrated(true)
            ->afterStateUpdated(function ($set, $state) {
                if ($state) {
                    $currentUser = $this->authService->getCurrentUser();
                    if ($currentUser) {
                        $set('approver_id', $currentUser->user_id);
                    }
                } else {
                    $set('approver_id', null);
                }
            });
    }

    /**
     * Build the approver select field
     */
    private function buildApproverSelect(): Forms\Components\Select
    {
        return Forms\Components\Select::make('approver_id')
            ->label('Approver')
            ->options(function () {
                return User::pluck('name', 'user_id')->toArray();
            })
            ->disabled(fn ($get) => $get('is_approver'))
            ->dehydrated(true)
            ->native(false)
            ->placeholder('Select an approver')
            ->required()
            ->selectablePlaceholder(false)
            ->searchable()
            ->preload();
    }

    /**
     * Build the file upload field
     */
    private function buildFileUpload(): Forms\Components\FileUpload
    {
        return Forms\Components\FileUpload::make('file_path')
            ->label('Assignment Letter File')
            ->disk('public')
            ->directory('assignment-letters')
            ->acceptedFileTypes(['application/pdf'])
            ->maxSize(5120)
            ->required()
            ->helperText('Upload PDF files only. Max size: 5MB');
    }
}
