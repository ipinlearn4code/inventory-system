<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Auth;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function handleRecordCreation(array $data): Model
    {
        $createAuth = $data['create_auth'] ?? false;
        
        // Start a transaction to ensure both User and Auth are created successfully
        return DB::transaction(function () use ($data, $createAuth) {
            // Remove the create_auth flag and auth data from the User data
            $userData = $data;
            unset($userData['create_auth']);
            unset($userData['auth']);
            
            // Create the User model
            $user = User::create($userData);
            
            // If create_auth is true, create the Auth record
            if ($createAuth && isset($data['auth'])) {
                $authData = $data['auth'];
                $authData['pn'] = $user->pn; // Use the PN from the user
                
                // Create the Auth record
                $auth = Auth::create([
                    'pn' => $user->pn,
                    'password' => Hash::make($authData['password']),
                ]);
                
                // Assign the role
                if (isset($authData['role'])) {
                    $auth->assignRole($authData['role']);
                }
            }
            
            return $user;
        });
    }
}
