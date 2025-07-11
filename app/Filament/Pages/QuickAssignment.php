<?php

namespace App\Filament\Pages;

use App\Models\AssignmentLetter;
use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\User;
use App\Services\MinioStorageService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class QuickAssignment extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationLabel = 'Quick Assignment';
    protected static string $view = 'filament.pages.quick-assignment';
    protected static ?string $navigationGroup = 'Device Management';
    protected static ?int $navigationSort = 3;
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill();
    }
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    // Step 1: Device Assignment
                    Forms\Components\Wizard\Step::make('Device Assignment')
                        ->schema([
                            Forms\Components\Select::make('user_id')
                                ->label('User')
                                ->options(User::pluck('name', 'user_id'))
                                ->searchable()
                                ->required()
                                ->helperText('User who will receive the device'),
                                
                            Forms\Components\Select::make('device_id')
                                ->label('Device')
                                ->options(function() {
                                    // Get devices that don't have an active assignment
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
                        ]),
                    
                    // Step 2: Assignment Letter
                    Forms\Components\Wizard\Step::make('Assignment Letter')
                        ->schema([
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
                                ->afterStateUpdated(function ($set, $state) {
                                    if ($state) {
                                        // When toggled on, set the current user as approver
                                        $auth = session('authenticated_user');
                                        if ($auth) {
                                            $user = User::where('pn', $auth['pn'])->first();
                                            if ($user) {
                                                $set('approver_id', $user->user_id);
                                            }
                                        }
                                    } else {
                                        // When toggled off, clear the selection
                                        $set('approver_id', null);
                                    }
                                }),
                                
                            Forms\Components\Select::make('approver_id')
                                ->label('Approver')
                                ->options(function () {
                                    return User::pluck('name', 'user_id')->toArray();
                                })
                                ->disabled(fn ($get) => $get('is_approver'))
                                ->native(false)
                                ->placeholder('Select an approver')
                                ->required()
                                ->selectablePlaceholder(false)
                                ->searchable()
                                ->preload(),
                                
                            Forms\Components\FileUpload::make('file_path')
                                ->label('Assignment Letter File')
                                ->acceptedFileTypes(['application/pdf', 'image/*', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                ->maxSize(5120) // 5MB max
                                ->required()
                                ->helperText('Upload PDF, Word document, or image file. Max size: 5MB'),
                        ]),
                ])
                ->persistStepInQueryString()
                ->skippable()
            ]);
    }
    
    public function submit(): void
    {
        $data = $this->form->getState();
        
        // Begin a transaction to ensure all operations succeed or fail together
        DB::beginTransaction();
        
        try {
            // Get user data for assignment
            $user = User::find($data['user_id']);
            
            // 1. Create device assignment
            $deviceAssignment = DeviceAssignment::create([
                'device_id' => $data['device_id'],
                'user_id' => $data['user_id'],
                'branch_id' => $user->branch_id,  // Get branch from user
                'assigned_date' => $data['assigned_date'],
                'status' => 'Digunakan',  // Set status to "In Use"
                'notes' => $data['assignment_notes'] ?? null,
                'created_by' => $this->getCurrentUserId(),
                'created_at' => now(),
            ]);
            
            // 2. Create assignment letter
            $assignmentLetter = AssignmentLetter::create([
                'assignment_id' => $deviceAssignment->assignment_id,
                'letter_type' => 'assignment',
                'letter_number' => $data['letter_number'],
                'letter_date' => $data['letter_date'],
                'approver_id' => $data['approver_id'],
                'created_by' => $this->getCurrentUserId(),
                'created_at' => now(),
            ]);
            
            // 3. Upload letter file to MinIO
            if (isset($data['file_path']) && $data['file_path']) {
                // Get the temporary uploaded file
                $uploadedFile = $data['file_path'];
                
                // Store file through our model method that uses MinIO
                $path = $assignmentLetter->storeFile($uploadedFile);
                
                if (!$path) {
                    // If file upload fails, roll back the transaction
                    throw new \Exception('Failed to upload assignment letter file');
                }
            }
            
            // If everything succeeded, commit the transaction
            DB::commit();
            
            // Show success notification
            Notification::make()
                ->title('Device Assignment Complete')
                ->body('Device successfully assigned and ready for pickup.')
                ->icon('heroicon-o-check-circle')
                ->iconColor('success')
                ->duration(8000)
                ->success()
                ->send();
                
            // Redirect to the list of assignments
            $this->redirect(route('filament.admin.resources.device-assignments.index'));
            
        } catch (\Exception $e) {
            // If any operation fails, roll back the entire transaction
            DB::rollBack();
            
            // Log the error
            \Log::error('Quick Assignment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Show error notification
            Notification::make()
                ->title('Assignment Failed')
                ->body($e->getMessage())
                ->icon('heroicon-o-exclamation-triangle')
                ->iconColor('danger')
                ->duration(8000)
                ->danger()
                ->send();
        }
    }
    
    /**
     * Get the current authenticated user's ID
     *
     * @return int|null
     */
    private function getCurrentUserId(): ?int
    {
        $auth = session('authenticated_user');
        if ($auth) {
            $user = User::where('pn', $auth['pn'])->first();
            if ($user) {
                return $user->user_id;
            }
        }
        return null;
    }
}
