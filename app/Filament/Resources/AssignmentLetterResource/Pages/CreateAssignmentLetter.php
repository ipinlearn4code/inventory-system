<?php

namespace App\Filament\Resources\AssignmentLetterResource\Pages;

use App\Filament\Resources\AssignmentLetterResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAssignmentLetter extends CreateRecord
{
    protected static string $resource = AssignmentLetterResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove the is_approver field as it's not part of the model
        $isApprover = $data['is_approver'] ?? false;
        unset($data['is_approver']);
        
        // Set created_by from authenticated user
        $auth = session('authenticated_user');
        if ($auth) {
            $user = User::where('pn', $auth['pn'])->first();
            if ($user) {
                $data['created_by'] = $user->user_id;
                
                // If the user is the approver but no approver_id was set, use current user
                if ($isApprover && empty($data['approver_id'])) {
                    $data['approver_id'] = $user->user_id;
                }
            }
        }
        
        // Make sure created_at is set
        if (empty($data['created_at'])) {
            $data['created_at'] = now();
        }
        
        return $data;
    }
}
