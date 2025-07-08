<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Filament\Notifications\Notification;

class PermissionToggle extends Component
{
    public $roleId;
    public $permissionId;
    public $hasPermission;

    public function mount($roleId, $permissionId, $hasPermission = false)
    {
        $this->roleId = $roleId;
        $this->permissionId = $permissionId;
        $this->hasPermission = $hasPermission;
    }

    public function togglePermission()
    {
        // Check if user is superadmin
        $auth = session('authenticated_user');
        if (!$auth) {
            return;
        }
        
        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        if (!$authModel || !$authModel->hasRole('superadmin')) {
            Notification::make()
                ->title('Access Denied')
                ->body('Only SuperAdmins can modify permissions.')
                ->danger()
                ->send();
            return;
        }

        $role = Role::find($this->roleId);
        $permission = Permission::find($this->permissionId);

        if (!$role || !$permission) {
            Notification::make()
                ->title('Error')
                ->body('Role or Permission not found.')
                ->danger()
                ->send();
            return;
        }

        if ($this->hasPermission) {
            $role->revokePermissionTo($permission);
            $this->hasPermission = false;
            
            Notification::make()
                ->title('Permission Revoked')
                ->body("Removed '{$permission->name}' from '{$role->name}' role.")
                ->warning()
                ->send();
        } else {
            $role->givePermissionTo($permission);
            $this->hasPermission = true;
            
            Notification::make()
                ->title('Permission Granted')
                ->body("Added '{$permission->name}' to '{$role->name}' role.")
                ->success()
                ->send();
        }

        // Emit event to refresh the parent component
        $this->dispatch('permission-updated');
    }

    public function render()
    {
        return view('livewire.permission-toggle');
    }
}
