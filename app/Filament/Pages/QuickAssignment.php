<?php

namespace App\Filament\Pages;

use App\Services\AuthenticationService;
use App\Services\NotificationService;
use App\Services\QuickAssignmentFormBuilder;
use App\Services\QuickAssignmentService;
use App\Services\QuickAssignmentValidator;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;

class QuickAssignment extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?string $navigationLabel = 'Quick Assignment';
    protected static string $view = 'filament.pages.quick-assignment';
    protected static ?int $navigationSort = 3;
    
    public ?array $data = [];
    
    public function mount(): void
    {
        $this->form->fill();
    }
    
    public function form(Form $form): Form
    {
        $formBuilder = app(QuickAssignmentFormBuilder::class);
        
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Device Assignment')
                        ->schema($formBuilder->buildDeviceAssignmentFields()),
                    
                    Forms\Components\Wizard\Step::make('Assignment Letter')
                        ->schema($formBuilder->buildAssignmentLetterFields()),
                ])
                ->persistStepInQueryString()
                ->skippable()
            ])
            ->statePath('data');
    }
    
    public function submit(): void
    {
        try {
            $data = $this->form->getState();
            
            $validator = app(QuickAssignmentValidator::class);
            $data = $validator->sanitize($data);
            $validator->validate($data);
            
            $assignmentService = app(QuickAssignmentService::class);
            $result = $assignmentService->createAssignmentWithLetter($data);
            
            $notificationService = app(NotificationService::class);
            $notificationService->assignmentCompleted();
            
            $this->resetForm();
            $this->redirectToAssignmentList();
            
        } catch (\Exception $e) {
            Log::error('Quick Assignment failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $notificationService = app(NotificationService::class);
            $notificationService->assignmentFailed($e->getMessage());
        }
    }

    /**
     * Reset the form after successful submission
     */
    private function resetForm(): void
    {
        $this->form->fill();
        $this->data = [];
    }

    /**
     * Redirect to assignment list
     */
    private function redirectToAssignmentList(): void
    {
        $this->redirect(route('filament.admin.resources.device-assignments.index'));
    }
}

