<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions based on DFD requirements
        $permissions = [
            // User permissions (regular users)
            'view own assignments',
            'make requests',
            
            // Admin permissions
            'manage devices',
            'manage assignments',
            'manage regular users',
            'manage regular auth',
            
            // Super Admin permissions (can do everything)
            'setup master data',
            'manage all users',
            'manage all auth',
            'view audit logs',
            'export data',
            'manage departments',
            'manage branches',
            'manage briboxes',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles and assign permissions
        
        // User role - can only view their own assignments and make requests
        $userRole = Role::create(['name' => 'user', 'guard_name' => 'web']);
        $userRole->givePermissionTo([
            'view own assignments',
            'make requests',
        ]);

        // Admin role - can manage devices, assignments, and regular users
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo([
            'view own assignments',
            'make requests',
            'manage devices',
            'manage assignments',
            'manage regular users',
            'manage regular auth',
        ]);

        // Super Admin role - can do everything
        $superAdminRole = Role::create(['name' => 'superadmin', 'guard_name' => 'web']);
        $superAdminRole->givePermissionTo(Permission::all());

        echo "Roles and permissions created successfully!\n";
    }
}
