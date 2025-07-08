<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Filament\Notifications\Notification;
use Livewire\Attributes\On;

class PermissionMatrix extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Permission Management';
    protected static ?string $title = 'Permission Matrix';
    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.permission-matrix';

    public static function canAccess(): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) return false;
        
        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && $authModel->hasRole('superadmin'); // Only superadmin
    }

    // System-defined permissions that cannot be modified
    protected function getSystemPermissions(): array
    {
        return [
            'view own assignments' => 'View devices assigned to user',
            'make requests' => 'Make requests for devices',
            'manage devices' => 'Create, edit, delete devices',
            'manage assignments' => 'Create, edit, delete assignments',
            'manage regular users' => 'Manage users with user role',
            'manage regular auth' => 'Manage auth for regular users',
            'manage departments' => 'Manage department master data',
            'manage branches' => 'Manage branch master data',
            'manage briboxes' => 'Manage bribox categories',
            'setup master data' => 'Manage departments, branches, briboxes',
            'manage all users' => 'Manage any user',
            'manage all auth' => 'Manage any authentication record',
            'view audit logs' => 'View system audit logs',
            'export data' => 'Export system data',
        ];
    }

    // System-defined roles that cannot be modified
    protected function getSystemRoles(): array
    {
        return [
            'user' => 'Regular User',
            'admin' => 'Administrator', 
            'superadmin' => 'Super Administrator'
        ];
    }

    public function getPermissionMatrix(): array
    {
        $systemPermissions = $this->getSystemPermissions();
        $roles = Role::whereIn('name', array_keys($this->getSystemRoles()))->with('permissions')->get();
        
        $matrix = [];
        
        foreach ($systemPermissions as $permissionName => $description) {
            $permission = Permission::where('name', $permissionName)->first();
            if (!$permission) continue;
            
            $permissionData = [
                'id' => $permission->id,
                'name' => $permissionName,
                'description' => $description,
                'roles' => []
            ];
            
            foreach ($roles as $role) {
                $hasPermission = $role->permissions->contains('id', $permission->id);
                $permissionData['roles'][$role->name] = [
                    'role_id' => $role->id,
                    'has_permission' => $hasPermission,
                    'disabled' => $role->name === 'superadmin' // Superadmin always has all permissions
                ];
            }
            
            $matrix[] = $permissionData;
        }
        
        return $matrix;
    }

    public function getRoles(): array
    {
        return $this->getSystemRoles();
    }

    public function togglePermission($roleId, $permissionId, $currentState)
    {
        $role = Role::find($roleId);
        $permission = Permission::find($permissionId);

        if (!$role || !$permission) {
            Notification::make()
                ->title('Error')
                ->body('Role or Permission not found.')
                ->danger()
                ->send();
            return;
        }

        // Prevent modification of superadmin role
        if ($role->name === 'superadmin') {
            Notification::make()
                ->title('Cannot Modify SuperAdmin')
                ->body('SuperAdmin role always has all permissions.')
                ->warning()
                ->send();
            return;
        }

        if ($currentState) {
            $role->revokePermissionTo($permission);
            Notification::make()
                ->title('Permission Revoked')
                ->body("Removed '{$permission->name}' from '{$role->name}' role.")
                ->warning()
                ->send();
        } else {
            $role->givePermissionTo($permission);
            Notification::make()
                ->title('Permission Granted')
                ->body("Added '{$permission->name}' to '{$role->name}' role.")
                ->success()
                ->send();
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    #[On('refresh-matrix')]
    public function refreshMatrix()
    {
        // This method will be called when we need to refresh the matrix
    }
}
