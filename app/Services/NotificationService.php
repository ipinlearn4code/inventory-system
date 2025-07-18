<?php

namespace App\Services;

use Filament\Notifications\Notification;

class NotificationService
{
    /**
     * Show success notification for completed assignment
     */
    public function assignmentCompleted(): void
    {
        Notification::make()
            ->title('Device Assignment Complete')
            ->body('Device successfully assigned and assignment letter uploaded.')
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->duration(8000)
            ->success()
            ->send();
    }

    /**
     * Show error notification for failed assignment
     */
    public function assignmentFailed(string $message): void
    {
        Notification::make()
            ->title('Assignment Failed')
            ->body($message)
            ->icon('heroicon-o-exclamation-triangle')
            ->iconColor('danger')
            ->duration(8000)
            ->danger()
            ->send();
    }

    /**
     * Show validation error notification
     */
    public function validationError(string $message): void
    {
        Notification::make()
            ->title('Validation Error')
            ->body($message)
            ->icon('heroicon-o-x-circle')
            ->iconColor('warning')
            ->duration(5000)
            ->warning()
            ->send();
    }
}
