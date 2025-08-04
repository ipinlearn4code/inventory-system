<?php

namespace App\Filament\Helpers;

use App\Models\Device;
use App\Models\User;
use App\Models\Branch;
use App\Models\Bribox;
use App\Models\BriboxesCategory;
use App\Models\Department;
use App\Models\MainBranch;
use App\Models\DeviceAssignment;
use App\Models\AssignmentLetter;
use App\Models\Auth;
use App\Services\DropdownOptionsService;
use App\Services\QRCodeService;
use App\Filament\Forms\Components\QrCodeScanner;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Forms;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;

class FormSchemaHelper
{
    /**
     * Get QR Scanner component for device selection
     */
    public static function getQrScannerField(): QrCodeScanner
    {
        return QrCodeScanner::make('qr_scanner')
            ->label('Scan Device QR Code')
            ->asButton('📱 Scan QR Code', 'primary', 'md')
            ->withIcon('heroicon-o-qr-code')
            ->live()
            ->lazy()
            ->visibleOn(['create', 'edit'])
            ->afterStateUpdated(function (string $state = null, Forms\Set $set) {
                if ($state) {
                    // Extract asset code from QR data
                    if (strpos($state, 'briven-') === 0) {
                        $assetCode = substr($state, 7); // Remove 'briven-' prefix
                    } else {
                        $assetCode = $state; // Use as-is if no prefix
                    }

                    // Find the device
                    $device = Device::available()
                        ->where('asset_code', $assetCode)
                        ->first();

                    if ($device) {
                        $set('device_id', $device['device_id']);

                        // Show success notification
                        \Filament\Notifications\Notification::make()
                            ->title('Device Found!')
                            ->body("Successfully scanned device: {$device['asset_code']}")
                            ->success()
                            ->send();
                    } else {
                        // Show error notification
                        \Filament\Notifications\Notification::make()
                            ->title('Device Not Found')
                            ->body("No available device found with asset code: {$assetCode}")
                            ->danger()
                            ->send();
                    }
                }
            })
            ->columnSpanFull();
    }

    /**
     * Get device selection field for assignments
     */
    public static function getDeviceSelectField(): Select
    {
        return Select::make('device_id')
            ->label('Device')
            ->relationship(
                name: 'device',
                titleAttribute: 'asset_code_with_type'
            )
            ->getOptionLabelFromRecordUsing(fn($record) => $record->asset_code_with_type)
            ->options(function () {
                return Device::available()->get()->pluck('asset_code_with_type', 'device_id');
            })
            ->required()
            ->searchable()
            ->helperText('Only available devices (not currently assigned) are shown. Use QR scanner above for quick selection.');
    }

    /**
     * Get user selection field with department info
     */
    public static function getUserSelectField(): Select
    {
        return Select::make('user_id')
            ->label('Assign to User')
            ->options(function () {
                return User::with('department')
                    ->get()
                    ->mapWithKeys(function ($user) {
                        $deptName = isset($user->department) ? $user->department->name : 'No Dept';
                        return [$user->user_id => $user->pn . ' - ' . $user->name . ' (' . $deptName . ')'];
                    });
            })
            ->required()
            ->searchable()
            ->live()
            ->afterStateUpdated(function ($state, Forms\Set $set) {
                if ($state) {
                    $user = User::find($state);
                    if ($user && $user->branch_id) {
                        $set('branch_id', $user->branch_id);

                        // Show notification that branch was auto-filled
                        \Filament\Notifications\Notification::make()
                            ->title('Branch Auto-filled')
                            ->body("Branch automatically set based on user's branch")
                            ->success()
                            ->send();
                    }
                }
            });
    }

    /**
     * Get branch selection field
     */
    public static function getBranchSelectField(): Select
    {
        return Select::make('branch_id')
            ->label('Branch')
            ->options(Branch::with('mainBranch')->get()->mapWithKeys(function ($branch) {
                return [$branch->branch_id => $branch->unit_name . ' (' . $branch->mainBranch->main_branch_name . ')'];
            }))
            ->required()
            ->searchable()
            ->helperText('This will be auto-filled when you select a user');
    }

    /**
     * Get assignment date field
     */
    public static function getAssignmentDateField(): DatePicker
    {
        return DatePicker::make('assigned_date')
            ->label('Assignment Date')
            ->required()
            ->default(now())
            ->maxDate(now());
    }

    /**
     * Get return date field
     */
    public static function getReturnDateField(): DatePicker
    {
        return DatePicker::make('returned_date')
            ->label('Return Date')
            ->helperText('Leave empty for active assignments');
    }

    /**
     * Get notes field
     */
    public static function getNotesField(): Textarea
    {
        return Textarea::make('notes')
            ->label('Notes')
            ->maxLength(500)
            ->rows(3);
    }

    /**
     * Get device brand field
     */
    public static function getDeviceBrandField(): Select
    {
        return Select::make('brand')
            ->label('Brand')
            ->required()
            ->options(DropdownOptionsService::getDeviceBrands())
            ->searchable()
            ->searchPrompt('Search or click + to add')
            ->createOptionForm([
                TextInput::make('new_brand')
                    ->label('New Brand Name')
                    ->maxLength(50)
                    ->required(),
            ])
            ->createOptionUsing(function (array $data) {
                DropdownOptionsService::clearCache();
                return $data['new_brand'];
            });
    }

    /**
     * Get device brand name field
     */
    public static function getDeviceBrandNameField(): Select
    {
        return Select::make('brand_name')
            ->label('Brand Name (Model/Series)')
            ->required()
            ->options(DropdownOptionsService::getDeviceBrandNames())
            ->searchable()
            ->searchPrompt('Search or click + to add')
            ->createOptionForm([
                TextInput::make('new_brand_name')
                    ->label('New Brand Name (Model/Series)')
                    ->maxLength(50)
                    ->required(),
            ])
            ->createOptionUsing(function (array $data) {
                DropdownOptionsService::clearCache();
                return $data['new_brand_name'];
            });
    }

    /**
     * Get bribox category field
     */
    public static function getBriboxCategoryField(): Select
    {
        return Select::make('category_id')
            ->label('Category')
            ->required()
            ->options(DropdownOptionsService::getBriboxCategories())
            ->searchable()
            ->live()
            ->afterStateUpdated(function ($state, Forms\Set $set) {
                if ($state) {
                    $set('bribox_id', null);
                }
            });
    }

    /**
     * Get bribox field based on category
     */
    public static function getBriboxField(): Select
    {
        return Select::make('bribox_id')
            ->label('Bribox Type')
            ->required()
            ->options(function (Forms\Get $get) {
                $categoryId = $get('category_id');
                if (!$categoryId) {
                    return [];
                }
                return Bribox::where('category_id', $categoryId)
                    ->pluck('name', 'bribox_id')
                    ->toArray();
            })
            ->searchable()
            ->disabled(fn(Forms\Get $get) => !$get('category_id'))
            ->helperText('Select a category first');
    }

    /**
     * Get complete device assignment form schema
     */
    public static function getDeviceAssignmentSchema(): array
    {
        return [
            self::getQrScannerField(),
            self::getDeviceSelectField(),
            self::getUserSelectField(),
            self::getBranchSelectField(),
            self::getAssignmentDateField(),
            self::getReturnDateField(),
            self::getNotesField(),
        ];
    }

    /**
     * Get device information section schema
     */
    public static function getDeviceInformationSchema(): array
    {
        return [
            Section::make('Device Information')
                ->schema([
                    self::getDeviceBrandField(),
                    self::getDeviceBrandNameField(),
                    self::getDeviceSerialNumberField(),
                    self::getDeviceAssetCodeField(),
                    self::getDeviceBriboxField(),
                    self::getDeviceConditionField(),
                    self::getDeviceStatusField(),
                    self::getDeviceDateField(),
                ])
                ->columns(2)
        ];
    }

    /**
     * Get device serial number field
     */
    public static function getDeviceSerialNumberField(): TextInput
    {
        return TextInput::make('serial_number')
            ->label('Serial Number')
            ->required()
            ->maxLength(50)
            ->unique(ignoreRecord: true)
            ->autocomplete(null);
    }

    /**
     * Get device asset code field with generation logic
     */
    public static function getDeviceAssetCodeField(): TextInput
    {
        return TextInput::make('asset_code')
            ->disabled(function ($livewire) {
                if (!($livewire instanceof \Filament\Resources\Pages\EditRecord) || empty($livewire->record)) {
                    return false;
                }
                $isAssigned = !$livewire->record?->currentAssignment?->returned_date;
                if (session('authenticated_user.role') === 'superadmin') {
                    return false;
                }
                if ($isAssigned) {
                    return true;
                }
                return false;
            })
            ->hint(function ($livewire) {
                if (!($livewire instanceof \Filament\Resources\Pages\EditRecord) || empty($livewire->record)) {
                    return null;
                }
                $isAssigned = !$livewire->record?->currentAssignment?->returned_date;
                if (session('authenticated_user.role') === 'superadmin' && $isAssigned) {
                    return 'Be careful on this field, you are editing an active assigned device';
                }
                if (session('authenticated_user.role') !== 'superadmin' && $isAssigned) {
                    return 'You cannot edit asset code because of active assignment';
                }
                return null;
            })
            ->label('Asset Code')
            ->required()
            ->maxLength(20)
            ->unique(ignoreRecord: true)
            ->suffixAction(
                Action::make('generate')
                    ->icon('heroicon-m-arrow-path')
                    ->tooltip('Generate Asset Code')
                    ->action(function (Forms\Set $set) {
                        $assetCode = self::generateAssetCode();
                        $set('asset_code', $assetCode);
                    })
                    ->visible(function ($livewire) {
                        if ($livewire instanceof \Filament\Resources\Pages\CreateRecord) {
                            return true;
                        }
                        if ($livewire instanceof \Filament\Resources\Pages\EditRecord && ($livewire->record?->currentAssignment === null)) {
                            return true;
                        }
                        return false;
                    })
            );
    }

    /**
     * Get device bribox field
     */
    public static function getDeviceBriboxField(): Select
    {
        return Select::make('bribox_id')
            ->label('Bribox Category')
            ->options(Bribox::with('category')->get()->mapWithKeys(function ($bribox) {
                $categoryName = $bribox->category ? $bribox->category->category_name : 'No Category';
                return [$bribox->bribox_id => "{$bribox->bribox_id} - {$bribox->type} ({$categoryName})"];
            }))
            ->required()
            ->searchable();
    }

    /**
     * Get device condition field
     */
    public static function getDeviceConditionField(): Select
    {
        return Select::make('condition')
            ->options([
                'Baik' => 'Baik',
                'Rusak' => 'Rusak',
                'Perlu Pengecekan' => 'Perlu Pengecekan',
            ])
            ->required();
    }

    /**
     * Get device status field
     */
    public static function getDeviceStatusField(): Select
    {
        return Select::make('status')
            ->options([
                'Digunakan' => 'Digunakan',
                'Tidak Digunakan' => 'Tidak Digunakan',
                'Cadangan' => 'Cadangan',
            ])
            ->default('Tidak Digunakan')
            ->required();
    }

    /**
     * Get device development date field
     */
    public static function getDeviceDateField(): DatePicker
    {
        return DatePicker::make('dev_date')
            ->label('Development Date');
    }

    /**
     * Get device specifications section
     */
    public static function getDeviceSpecificationsSchema(): array
    {
        return [
            Section::make('Specifications')
                ->schema([
                    TextInput::make('spec1')
                        ->label('Specification 1')
                        ->maxLength(100),
                    TextInput::make('spec2')
                        ->label('Specification 2')
                        ->maxLength(100),
                    TextInput::make('spec3')
                        ->label('Specification 3')
                        ->maxLength(100),
                    TextInput::make('spec4')
                        ->label('Specification 4')
                        ->maxLength(100),
                    TextInput::make('spec5')
                        ->label('Specification 5')
                        ->maxLength(100),
                ])
                ->columns(2)
        ];
    }

    /**
     * Get device audit information section
     */
    public static function getDeviceAuditSchema(): array
    {
        return [
            Section::make('Audit Information')
                ->schema([
                    TextInput::make('created_by')
                        ->label('Created By')
                        ->default(auth()->user()?->pn ?? session('authenticated_user.pn'))
                        ->maxLength(8)
                        ->disabled()
                        ->dehydrated(),
                    TextInput::make('updated_by')
                        ->label('Updated By')
                        ->default(auth()->user()?->pn ?? session('authenticated_user.pn'))
                        ->maxLength(8)
                        ->disabled()
                        ->dehydrated(),
                ])
                ->columns(2)
                ->visibleOn('edit')
        ];
    }

    /**
     * Get complete device form schema
     */
    public static function getDeviceFormSchema(): array
    {
        return array_merge(
            self::getDeviceInformationSchema(),
            self::getDeviceSpecificationsSchema(),
            self::getDeviceAuditSchema()
        );
    }

    /**
     * Get device view form schema for modal display
     */
    public static function getDeviceViewSchema(Device $record): array
    {
        $qrCodeService = app(QRCodeService::class);
        $qrCodeDataUrl = null;
        try {
            $qrCodeDataUrl = $qrCodeService->getQRCodeDataUrl($record->asset_code);
        } catch (\Exception $e) {
            // Handle QR code generation error
        }

        return [
    Section::make('Device Information')
        ->collapsible()
        ->icon('heroicon-o-cpu-chip')
        ->schema([
            Grid::make(2) // 2-column grid for better spacing
                ->schema([
                    Placeholder::make('asset_code')
                        ->label('Asset Code')
                        ->content(fn ($record) => $record->asset_code ?? 'N/A')
                        // ->suffixAction(
                        //     Action::make('copy')
                        //         ->icon('heroicon-m-clipboard')
                        //         ->tooltip('Copy Asset Code')
                        //         ->action(fn ($record) => \Filament\Support\copy_to_clipboard($record->asset_code))
                        // ),
                        ->inlineLabel(),
                    Placeholder::make('brand')
                        ->label('Brand')
                        ->content(fn ($record) => $record->brand ?? 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('brand_name')
                        ->label('Model/Series')
                        ->content(fn ($record) => $record->brand_name ?? 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('serial_number')
                        ->label('Serial Number')
                        ->content(fn ($record) => $record->serial_number ?? 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('bribox_type')
                        ->label('Type')
                        ->content(fn ($record) => $record->bribox?->type ?? 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('category')
                        ->label('Category')
                        ->content(fn ($record) => $record->bribox?->category?->category_name ?? 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('condition')
                        ->label('Condition')
                        ->content(fn ($record) => $record->condition ?? 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('dev_date')
                        ->label('Development Date')
                        ->content(fn ($record) => $record->dev_date ? $record->dev_date->format('d M Y') : 'N/A')
                        ->inlineLabel(),
                ]),
        ]),

    Section::make('Specifications')
        ->collapsible()
        ->icon('heroicon-o-information-circle')
        ->schema([
            Grid::make(2)
                ->schema([
                    Placeholder::make('spec1')
                        ->label('Specification 1')
                        ->content(fn ($record) => $record->spec1 ?? 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('spec2')
                        ->label('Specification 2')
                        ->content(fn ($record) => $record->spec2 ?? 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('spec3')
                        ->label('Specification 3')
                        ->content(fn ($record) => $record->spec3 ?? 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('spec4')
                        ->label('Specification 4')
                        ->content(fn ($record) => $record->spec4 ?? 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('spec5')
                        ->label('Specification 5')
                        ->content(fn ($record) => $record->spec5 ?? 'N/A')
                        ->inlineLabel(),
                ]),
        ]),

    Section::make('QR Code')
        ->description(fn ($record) => 'QR code for this device: briven-' . ($record->asset_code ?? 'N/A'))
        ->collapsible()
        ->icon('heroicon-o-qr-code')
        ->schema([
            Grid::make(2)
                ->schema([
                    ViewField::make('qr_code_preview')
                        ->label('')
                        ->view('filament.components.qr-code-preview', [
                            'qrCodeDataUrl' => $qrCodeDataUrl,
                            'assetCode' => $record->asset_code,
                            'deviceId' => $record->device_id,
                        ])
                        ->extraAttributes(['class' => 'flex justify-center']),
                ]),
        ]),

    Section::make('Assignment Information')
        ->collapsible()
        ->icon('heroicon-o-user-plus')
        ->schema([
            Grid::make(1)
                ->schema([
                    Placeholder::make('assigned_to')
                        ->label('Assigned To')
                        ->content(fn ($record) => $record->currentAssignment?->user?->name ?? 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('branch')
                        ->label('Branch')
                        ->content(fn ($record) => $record->currentAssignment?->branch?->branch_name ?? 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('assigned_date')
                        ->label('Assignment Date')
                        ->content(fn ($record) => $record->currentAssignment?->assigned_date ? $record->currentAssignment->assigned_date->format('d M Y') : 'N/A')
                        ->inlineLabel(),
                    RichEditor::make('assignment_notes')
                        ->label('Assignment Notes')
                        ->default(fn ($record) => $record->currentAssignment?->notes ?? 'No notes available.')
                        ->disabled()
                        ->columnSpanFull(),
                ]),
        ])
        ->visible(fn ($record) => $record->currentAssignment !== null),

    Section::make('Audit Trail')
        ->collapsible()
        ->icon('heroicon-o-clock')
        ->schema([
            Grid::make(2)
                ->schema([
                    Placeholder::make('created_by')
                        ->label('Created By')
                        ->content(fn ($record) => $record->created_by ?? 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('created_at')
                        ->label('Created At')
                        ->content(fn ($record) => $record->created_at ? $record->created_at->format('d M Y H:i') : 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('updated_by')
                        ->label('Updated By')
                        ->content(fn ($record) => $record->updated_by ?? 'N/A')
                        ->inlineLabel(),
                    Placeholder::make('updated_at')
                        ->label('Updated At')
                        ->content(fn ($record) => $record->updated_at ? $record->updated_at->format('d M Y H:i') : 'N/A')
                        ->inlineLabel(),
                ]),
        ]),
];
    }

    /**
     * Generate a unique 15-character alphanumeric asset code
     */
    private static function generateAssetCode(): string
    {
        do {
            $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $assetCode = '';

            for ($i = 0; $i < 15; $i++) {
                $assetCode .= $characters[rand(0, strlen($characters) - 1)];
            }

            $exists = Device::where('asset_code', $assetCode)->exists();
        } while ($exists);

        return $assetCode;
    }

    /**
     * Get user personnel number field
     */
    public static function getUserPnField(): TextInput
    {
        return TextInput::make('pn')
            ->label('Personnel Number')
            ->required()
            ->maxLength(8)
            ->unique(ignoreRecord: true);
    }

    /**
     * Get user name field
     */
    public static function getUserNameField(): TextInput
    {
        return TextInput::make('name')
            ->required()
            ->maxLength(50);
    }

    /**
     * Get user department field
     */
    public static function getUserDepartmentField(): Select
    {
        return Select::make('department_id')
            ->label('Department')
            ->options(Department::all()->pluck('name', 'department_id'))
            ->required()
            ->searchable();
    }

    /**
     * Get user position field
     */
    public static function getUserPositionField(): TextInput
    {
        return TextInput::make('position')
            ->label('Position')
            ->datalist(
                User::query()
                    ->whereNotNull('position')
                    ->distinct()
                    ->orderBy('position')
                    ->pluck('position')
                    ->toArray()
            )
            ->autocomplete(false)
            ->required();
    }

    /**
     * Get user branch selection field (different from assignment branch)
     */
    public static function getUserBranchField(): Select
    {
        return Select::make('branch_id')
            ->label('Branch')
            ->options(Branch::all()->pluck('unit_name', 'branch_id'))
            ->required()
            ->searchable();
    }

    /**
     * Get create authentication toggle field
     */
    public static function getCreateAuthToggleField(): Toggle
    {
        return Toggle::make('create_auth')
            ->label('Add Authentication')
            ->helperText('Create authentication credentials for this user')
            ->default(false)
            ->live()
            ->visible(fn ($livewire) => $livewire instanceof \App\Filament\Resources\UserResource\Pages\CreateUser);
    }

    /**
     * Get authentication details section
     */
    public static function getAuthenticationDetailsSchema(): array
    {
        return [
            Section::make('Authentication Details')
                ->schema([
                    TextInput::make('auth.password')
                        ->label('Password')
                        ->password()
                        ->dehydrated(fn ($state) => filled($state))
                        ->required(fn ($livewire) => $livewire instanceof \App\Filament\Resources\UserResource\Pages\CreateUser && $livewire->data['create_auth'])
                        ->confirmed()
                        ->minLength(8)
                        ->helperText('Leave blank to keep the same password when editing'),
                    
                    TextInput::make('auth.password_confirmation')
                        ->label('Confirm Password')
                        ->password()
                        ->required(fn ($livewire) => $livewire instanceof \App\Filament\Resources\UserResource\Pages\CreateUser && $livewire->data['create_auth'])
                        ->dehydrated(false)
                        ->minLength(8),
                    
                    Select::make('auth.role')
                        ->label('Role')
                        ->options([
                            'user' => 'Regular User',
                            'admin' => 'Administrator',
                            'superadmin' => 'Super Administrator'
                        ])
                        ->required(fn (Forms\Get $get): bool => $get('create_auth'))
                        ->default('user')
                        ->helperText('Determines what actions the user can perform'),
                ])
                ->columns(2)
        ];
    }

    /**
     * Get complete user form schema
     */
    public static function getUserFormSchema(): array
    {
        return [
            self::getUserPnField(),
            self::getUserBranchField(),
            self::getUserNameField(),
            self::getUserDepartmentField(),
            self::getUserPositionField(),
            self::getCreateAuthToggleField(),
            ...self::getAuthenticationDetailsSchema()
        ];
    }

    /**
     * Get branch code field
     */
    public static function getBranchCodeField(): TextInput
    {
        return TextInput::make('branch_code')
            ->label('Branch Code')
            ->required()
            ->maxLength(8)
            ->unique(ignoreRecord: true);
    }

    /**
     * Get branch unit name field
     */
    public static function getBranchUnitNameField(): TextInput
    {
        return TextInput::make('unit_name')
            ->label('Unit Name')
            ->required()
            ->maxLength(50);
    }

    /**
     * Get branch main branch field
     */
    public static function getBranchMainBranchField(): Select
    {
        return Select::make('main_branch_id')
            ->label('Main Branch')
            ->options(MainBranch::all()->pluck('main_branch_name', 'main_branch_id'))
            ->required()
            ->searchable();
    }

    /**
     * Get complete branch form schema
     */
    public static function getBranchFormSchema(): array
    {
        return [
            self::getBranchCodeField(),
            self::getBranchUnitNameField(),
            self::getBranchMainBranchField(),
        ];
    }

    // =======================================================================
    // VIEW SCHEMA METHODS
    // These methods provide standardized view-only schemas for all resources
    // following the DeviceViewSchema pattern with collapsible sections,
    // using Placeholder components instead of disabled form fields
    // =======================================================================

    /**
     * Get user view form schema for modal display
     */
    public static function getUserViewSchema(User $record): array
    {
        return [
            Section::make('User Information')
                ->collapsible()
                ->icon('heroicon-o-user')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('pn')
                                ->label('Personnel Number')
                                ->content(fn ($record) => $record->pn ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('name')
                                ->label('Name')
                                ->content(fn ($record) => $record->name ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('position')
                                ->label('Position')
                                ->content(fn ($record) => $record->position ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('department')
                                ->label('Department')
                                ->content(fn ($record) => $record->department?->name ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('branch')
                                ->label('Branch')
                                ->content(fn ($record) => $record->branch?->unit_name ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('main_branch')
                                ->label('Main Branch')
                                ->content(fn ($record) => $record->branch?->mainBranch?->main_branch_name ?? 'N/A')
                                ->inlineLabel(),
                        ]),
                ]),

            Section::make('Assignment Summary')
                ->collapsible()
                ->icon('heroicon-o-computer-desktop')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('total_assignments')
                                ->label('Total Assignments')
                                ->content(fn ($record) => $record->deviceAssignments()->count())
                                ->inlineLabel(),
                            Placeholder::make('active_assignments')
                                ->label('Active Assignments')
                                ->content(fn ($record) => $record->deviceAssignments()->whereNull('returned_date')->count())
                                ->inlineLabel(),
                        ]),
                ]),

            Section::make('Authentication Status')
                ->collapsible()
                ->icon('heroicon-o-key')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('has_auth')
                                ->label('Has Authentication')
                                ->content(fn ($record) => $record->auth ? 'Yes' : 'No')
                                ->inlineLabel(),
                            Placeholder::make('role')
                                ->label('Role')
                                ->content(fn ($record) => $record->auth?->role ?? 'N/A')
                                ->inlineLabel(),
                        ]),
                ])
                ->visible(fn ($record) => $record->auth !== null),
        ];
    }

    /**
     * Get branch view form schema for modal display
     */
    public static function getBranchViewSchema(Branch $record): array
    {
        return [
            Section::make('Branch Information')
                ->collapsible()
                ->icon('heroicon-o-building-office-2')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('branch_code')
                                ->label('Branch Code')
                                ->content(fn ($record) => $record->branch_code ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('unit_name')
                                ->label('Unit Name')
                                ->content(fn ($record) => $record->unit_name ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('main_branch')
                                ->label('Main Branch')
                                ->content(fn ($record) => $record->mainBranch?->main_branch_name ?? 'N/A')
                                ->inlineLabel(),
                        ]),
                ]),

            Section::make('Statistics')
                ->collapsible()
                ->icon('heroicon-o-chart-bar')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('total_users')
                                ->label('Total Users')
                                ->content(fn ($record) => $record->users()->count())
                                ->inlineLabel(),
                            Placeholder::make('total_assignments')
                                ->label('Total Device Assignments')
                                ->content(fn ($record) => $record->deviceAssignments()->count())
                                ->inlineLabel(),
                        ]),
                ]),
        ];
    }

    /**
     * Get main branch view form schema for modal display
     */
    public static function getMainBranchViewSchema(MainBranch $record): array
    {
        return [
            Section::make('Main Branch Information')
                ->collapsible()
                ->icon('heroicon-o-building-office')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('main_branch_name')
                                ->label('Main Branch Name')
                                ->content(fn ($record) => $record->main_branch_name ?? 'N/A')
                                ->inlineLabel(),
                        ]),
                ]),

            Section::make('Branch Statistics')
                ->collapsible()
                ->icon('heroicon-o-chart-bar')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('total_branches')
                                ->label('Total Sub-Branches')
                                ->content(fn ($record) => $record->branches()->count())
                                ->inlineLabel(),
                        ]),
                ]),
        ];
    }

    /**
     * Get department view form schema for modal display
     */
    public static function getDepartmentViewSchema(Department $record): array
    {
        return [
            Section::make('Department Information')
                ->collapsible()
                ->icon('heroicon-o-building-library')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('name')
                                ->label('Department Name')
                                ->content(fn ($record) => $record->name ?? 'N/A')
                                ->inlineLabel(),
                        ]),
                ]),

            Section::make('Department Statistics')
                ->collapsible()
                ->icon('heroicon-o-chart-bar')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('total_users')
                                ->label('Total Users')
                                ->content(fn ($record) => $record->users()->count())
                                ->inlineLabel(),
                        ]),
                ]),
        ];
    }

    /**
     * Get bribox view form schema for modal display
     */
    public static function getBriboxViewSchema(Bribox $record): array
    {
        return [
            Section::make('Bribox Information')
                ->collapsible()
                ->icon('heroicon-o-cube')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('type')
                                ->label('Type')
                                ->content(fn ($record) => $record->type ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('category')
                                ->label('Category')
                                ->content(fn ($record) => $record->category?->category_name ?? 'N/A')
                                ->inlineLabel(),
                        ]),
                ]),

            Section::make('Device Statistics')
                ->collapsible()
                ->icon('heroicon-o-chart-bar')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('total_devices')
                                ->label('Total Devices')
                                ->content(fn ($record) => $record->devices()->count())
                                ->inlineLabel(),
                        ]),
                ]),
        ];
    }

    /**
     * Get bribox category view form schema for modal display
     */
    public static function getBriboxesCategoryViewSchema(BriboxesCategory $record): array
    {
        return [
            Section::make('Category Information')
                ->collapsible()
                ->icon('heroicon-o-tag')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('category_name')
                                ->label('Category Name')
                                ->content(fn ($record) => $record->category_name ?? 'N/A')
                                ->inlineLabel(),
                        ]),
                ]),

            Section::make('Category Statistics')
                ->collapsible()
                ->icon('heroicon-o-chart-bar')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('total_briboxes')
                                ->label('Total Bribox Types')
                                ->content(fn ($record) => $record->briboxes()->count())
                                ->inlineLabel(),
                        ]),
                ]),
        ];
    }

    /**
     * Get device assignment view form schema for modal display
     */
    public static function getDeviceAssignmentViewSchema(DeviceAssignment $record): array
    {
        return [
            Section::make('Assignment Information')
                ->collapsible()
                ->icon('heroicon-o-clipboard-document-list')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('device')
                                ->label('Device')
                                ->content(fn ($record) => $record->device?->brand . ' ' . $record->device?->brand_name . ' (' . $record->device?->asset_code . ')' ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('user')
                                ->label('Assigned To')
                                ->content(fn ($record) => $record->user?->name . ' (' . $record->user?->pn . ')' ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('branch')
                                ->label('Branch')
                                ->content(fn ($record) => $record->branch?->unit_name ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('assigned_date')
                                ->label('Assignment Date')
                                ->content(fn ($record) => $record->assigned_date ? $record->assigned_date->format('d M Y') : 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('returned_date')
                                ->label('Return Date')
                                ->content(fn ($record) => $record->returned_date ? $record->returned_date->format('d M Y') : 'Active Assignment')
                                ->inlineLabel(),
                            Placeholder::make('status')
                                ->label('Status')
                                ->content(fn ($record) => $record->returned_date ? 'Returned' : 'Active')
                                ->inlineLabel(),
                        ]),
                ]),

            Section::make('Assignment Notes')
                ->collapsible()
                ->icon('heroicon-o-document-text')
                ->schema([
                    RichEditor::make('notes')
                        ->label('')
                        ->default(fn ($record) => $record->notes ?? 'No notes available.')
                        ->disabled()
                        ->columnSpanFull(),
                ]),

            Section::make('Audit Trail')
                ->collapsible()
                ->icon('heroicon-o-clock')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('created_by')
                                ->label('Created By')
                                ->content(fn ($record) => $record->created_by ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('created_at')
                                ->label('Created At')
                                ->content(fn ($record) => $record->created_at ? $record->created_at->format('d M Y H:i') : 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('updated_by')
                                ->label('Updated By')
                                ->content(fn ($record) => $record->updated_by ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('updated_at')
                                ->label('Updated At')
                                ->content(fn ($record) => $record->updated_at ? $record->updated_at->format('d M Y H:i') : 'N/A')
                                ->inlineLabel(),
                        ]),
                ]),
        ];
    }

    /**
     * Get assignment letter view form schema for modal display
     */
    public static function getAssignmentLetterViewSchema(AssignmentLetter $record): array
    {
        return [
            Section::make('Letter Information')
                ->collapsible()
                ->icon('heroicon-o-document')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('letter_number')
                                ->label('Letter Number')
                                ->content(fn ($record) => $record->letter_number ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('assignment')
                                ->label('Assignment')
                                ->content(fn ($record) => $record->deviceAssignment?->device?->brand . ' ' . $record->deviceAssignment?->device?->brand_name ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('user')
                                ->label('Assigned To')
                                ->content(fn ($record) => $record->deviceAssignment?->user?->name ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('letter_date')
                                ->label('Letter Date')
                                ->content(fn ($record) => $record->letter_date ? $record->letter_date->format('d M Y') : 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('type')
                                ->label('Letter Type')
                                ->content(fn ($record) => $record->type ?? 'N/A')
                                ->inlineLabel(),
                        ]),
                ]),

            Section::make('File Attachments')
                ->collapsible()
                ->icon('heroicon-o-paper-clip')
                ->schema([
                    Placeholder::make('file_info')
                        ->label('File Status')
                        ->content(fn ($record) => $record->file_path ? 'File attached' : 'No file attached')
                        ->inlineLabel(),
                ])
                ->visible(fn ($record) => $record->file_path !== null),

            Section::make('Audit Trail')
                ->collapsible()
                ->icon('heroicon-o-clock')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('created_by')
                                ->label('Created By')
                                ->content(fn ($record) => $record->created_by ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('created_at')
                                ->label('Created At')
                                ->content(fn ($record) => $record->created_at ? $record->created_at->format('d M Y H:i') : 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('updated_by')
                                ->label('Updated By')
                                ->content(fn ($record) => $record->updated_by ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('updated_at')
                                ->label('Updated At')
                                ->content(fn ($record) => $record->updated_at ? $record->updated_at->format('d M Y H:i') : 'N/A')
                                ->inlineLabel(),
                        ]),
                ]),
        ];
    }

    /**
     * Get auth view form schema for modal display
     */
    public static function getAuthViewSchema(Auth $record): array
    {
        return [
            Section::make('Authentication Information')
                ->collapsible()
                ->icon('heroicon-o-key')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('pn')
                                ->label('Personnel Number')
                                ->content(fn ($record) => $record->pn ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('role')
                                ->label('Role')
                                ->content(fn ($record) => $record->role ?? 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('user')
                                ->label('User')
                                ->content(fn ($record) => $record->user?->name ?? 'N/A')
                                ->inlineLabel(),
                        ]),
                ]),

            Section::make('Audit Trail')
                ->collapsible()
                ->icon('heroicon-o-clock')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Placeholder::make('created_at')
                                ->label('Created At')
                                ->content(fn ($record) => $record->created_at ? $record->created_at->format('d M Y H:i') : 'N/A')
                                ->inlineLabel(),
                            Placeholder::make('updated_at')
                                ->label('Updated At')
                                ->content(fn ($record) => $record->updated_at ? $record->updated_at->format('d M Y H:i') : 'N/A')
                                ->inlineLabel(),
                        ]),
                ]),
        ];
    }
}
