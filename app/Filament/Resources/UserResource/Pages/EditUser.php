<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Auth;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load auth data if it exists
        $user = $this->getRecord();
        if ($user->auth) {
            $data['auth'] = [
                'role' => $user->auth->role,
            ];
        }
        
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Update user data
        $userData = $data;
        unset($userData['auth']);
        
        $record->update($userData);
        
        // Update auth data if provided
        if (isset($data['auth']) && $record->auth) {
            $authData = $data['auth'];
            
            // Update auth record
            $updateData = [];
            
            if (isset($authData['password']) && !empty($authData['password'])) {
                $updateData['password'] = Hash::make($authData['password']);
            }
            
            if (isset($authData['role'])) {
                $updateData['role'] = $authData['role'];
            }
            
            if (!empty($updateData)) {
                $record->auth->update($updateData);
                
                // Update Spatie role if role changed
                if (isset($authData['role'])) {
                    $record->auth->syncRoles([$authData['role']]);
                }
            }
        }
        
        return $record;
    }
}
