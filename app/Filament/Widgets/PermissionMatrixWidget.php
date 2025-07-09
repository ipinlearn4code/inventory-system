<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionMatrixWidget extends Widget
{
    protected static string $view = 'filament.widgets.permission-matrix-widget';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    protected $listeners = ['permission-updated' => '$refresh'];

    // Disable this widget from appearing on dashboard
    public static function canView(): bool
    {
        return false; // Never show on dashboard
    }

    // Keep the original canView logic for other places if needed
    public static function canViewForSuperadmin(): bool
    {
        $auth = session('authenticated_user');
        if (!$auth) return false;
        
        $authModel = \App\Models\Auth::where('pn', $auth['pn'])->first();
        return $authModel && $authModel->hasRole('superadmin'); // Only superadmin
    }

    public function getPermissionMatrix(): array
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();
        
        $matrix = [];
        
        foreach ($permissions as $permission) {
            $permissionData = [
                'permission' => $permission->name,
                'roles' => []
            ];
            
            foreach ($roles as $role) {
                $permissionData['roles'][$role->name] = $role->permissions->contains('id', $permission->id);
            }
            
            $matrix[] = $permissionData;
        }
        
        return $matrix;
    }
    
    public function getRoles(): array
    {
        return Role::all()->pluck('name')->toArray();
    }
}
