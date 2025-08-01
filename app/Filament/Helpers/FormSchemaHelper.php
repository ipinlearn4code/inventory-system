<?php

namespace App\Filament\Helpers;

use App\Models\Device;
use App\Models\User;
use App\Models\Branch;
use App\Models\Bribox;
use App\Models\BriboxesCategory;
use App\Models\Department;
use App\Models\MainBranch;
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
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('asset_code')
                                ->label('Asset Code')
                                ->disabled()
                                ->suffixAction(
                                    Action::make('copy')
                                        ->icon('heroicon-m-clipboard')
                                        ->tooltip('Copy Asset Code')
                                ),
                            TextInput::make('brand')
                                ->label('Brand')
                                ->disabled(),
                            TextInput::make('brand_name')
                                ->label('Model/Series')
                                ->disabled(),
                            TextInput::make('serial_number')
                                ->label('Serial Number')
                                ->disabled(),
                            TextInput::make('bribox.type')
                                ->label('Type')
                                ->disabled(),
                            TextInput::make('bribox.category.category_name')
                                ->label('Category')
                                ->disabled(),
                            TextInput::make('condition')
                                ->label('Condition')
                                ->disabled(),
                            DatePicker::make('dev_date')
                                ->label('Development Date')
                                ->disabled(),
                        ]),
                ]),

            Section::make('QR Code')
                ->description('QR code for this device containing: briven-' . $record->asset_code)
                ->schema([
                    Grid::make(1)
                        ->schema([
                            ViewField::make('qr_code_preview')
                                ->label('')
                                ->view('filament.components.qr-code-preview', [
                                    'qrCodeDataUrl' => $qrCodeDataUrl,
                                    'assetCode' => $record->asset_code,
                                    'deviceId' => $record->device_id,
                                ])
                                ->extraAttributes(['style' => 'text-align: center;']),
                        ]),
                ]),

            Section::make('Specifications')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('spec1')
                                ->label('Specification 1')
                                ->disabled(),
                            TextInput::make('spec2')
                                ->label('Specification 2')
                                ->disabled(),
                            TextInput::make('spec3')
                                ->label('Specification 3')
                                ->disabled(),
                            TextInput::make('spec4')
                                ->label('Specification 4')
                                ->disabled(),
                            TextInput::make('spec5')
                                ->label('Specification 5')
                                ->disabled(),
                        ]),
                ])
                ->collapsible(),

            Section::make('Assignment Information')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('currentAssignment.user.name')
                                ->label('Assigned To')
                                ->disabled(),
                            TextInput::make('currentAssignment.branch.branch_name')
                                ->label('Branch')
                                ->disabled(),
                            DatePicker::make('currentAssignment.assigned_date')
                                ->label('Assignment Date')
                                ->disabled(),
                            Textarea::make('currentAssignment.notes')
                                ->label('Assignment Notes')
                                ->disabled(),
                        ]),
                ])
                ->visible(fn() => $record->currentAssignment !== null),

            Section::make('Audit Trail')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('created_by')
                                ->label('Created By')
                                ->disabled(),
                            DateTimePicker::make('created_at')
                                ->label('Created At')
                                ->disabled(),
                            TextInput::make('updated_by')
                                ->label('Updated By')
                                ->disabled(),
                            DateTimePicker::make('updated_at')
                                ->label('Updated At')
                                ->disabled(),
                        ]),
                ])
                ->collapsible(),
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
}
